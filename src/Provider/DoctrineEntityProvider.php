<?php

/*
 * This file is part of the ForciRememberMeBundle package.
 *
 * (c) Martin Kirilov <wucdbm@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Forci\Bundle\RememberMe\Provider;

use Forci\Bundle\RememberMe\Entity\RememberMeToken;
use Forci\Bundle\RememberMe\Entity\Session;
use Forci\Bundle\RememberMe\Repository\RememberMeTokenRepository;
use Symfony\Component\Cache\Adapter\AdapterInterface;
use Symfony\Component\Security\Core\Authentication\RememberMe\PersistentTokenInterface;
use Symfony\Component\Security\Core\Authentication\RememberMe\TokenProviderInterface;
use Symfony\Component\Security\Core\Exception\TokenNotFoundException;

class DoctrineEntityProvider implements TokenProviderInterface {

    /** @var RememberMeTokenRepository */
    protected $tokenRepository;

    /** @var AdapterInterface */
    protected $cache;

    /** @var \SessionHandlerInterface|null */
    protected $sessionHandler;

    public function __construct(RememberMeTokenRepository $tokenRepository, AdapterInterface $cache,
                                ?\SessionHandlerInterface $sessionHandler) {
        $this->tokenRepository = $tokenRepository;
        $this->cache = $cache;
        $this->sessionHandler = $sessionHandler;
    }

    public function loadTokenBySeries($series): RememberMeToken {
        $token = $this->getTokenBySeries($series);

        if (!$token) {
            throw new TokenNotFoundException('No token found.');
        }

        return $token;
    }

    public function deleteTokenBySeries($series) {
        $token = $this->getTokenBySeries($series);

        if ($token) {
            $this->deleteToken($token);
        }
    }

    public function deleteToken(RememberMeToken $token) {
        $this->tokenRepository->remove($token);

        // First try to invalidate all sessions
        if ($this->sessionHandler) {
            /** @var Session $session */
            foreach ($token->getSessions() as $session) {
                $this->sessionHandler->destroy($session->getIdentifier());
            }
        }

        $this->uncacheTokenBySeries($token->getSeries());
    }

    public function updateToken($series, $tokenValue, \DateTime $lastUsed) {
        $rows = $this->tokenRepository->updateToken($series, $tokenValue, $lastUsed);

        if (!$rows) {
            throw new TokenNotFoundException('No token found.');
        }

        $this->uncacheTokenBySeries($series);
    }

    public function createNewToken(PersistentTokenInterface $token) {
        if (is_a($token, RememberMeToken::class)) {
            /* @var RememberMeToken $token */
            $this->tokenRepository->save($token);
        }
    }

    protected function getTokenBySeries(string $series) {
        return $this->tokenRepository->findOneBySeries($series);
        // todo proper implementation
        // 1. delete sessions
        // 2. delete token
        // Those are creating issues since the Token / Session are detached from the entity manager
        $key = $this->generateTokenKey($series);

        $item = $this->cache->getItem($key);
        if ($item->isHit()) {
            return $item->get();
        }

        $token = $this->tokenRepository->findOneBySeries($series);
        $item->set($token);

        $this->cache->save($item);

        return $token;
    }

    protected function uncacheTokenBySeries(string $series) {
        $key = $this->generateTokenKey($series);
        $this->cache->deleteItem($key);
    }

    protected function generateTokenKey(string $series) {
        return sprintf('forci_remember_me.token.%s', md5($series));
    }
}
