<?php

namespace Forci\Bundle\RememberMeBundle\Service;

use DeviceDetector\DeviceDetector;
use DeviceDetector\Parser\Device\DeviceParserAbstract;
use Doctrine\Common\Cache\CacheProvider;
use Forci\Bundle\RememberMeBundle\Entity\DeviceAwareInterface;
use Forci\Bundle\RememberMeBundle\Entity\RememberMeToken;
use Forci\Bundle\RememberMeBundle\Entity\Session;
use Forci\Bundle\RememberMeBundle\Provider\DoctrineEntityProvider;
use Forci\Bundle\RememberMeBundle\Provider\FindUserByIdInterface;
use Forci\Bundle\RememberMeBundle\Repository\SessionRepository;
use Symfony\Component\HttpFoundation\Cookie;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Core\Exception\CookieTheftException;
use Symfony\Component\Security\Core\Exception\TokenNotFoundException;
use Symfony\Component\Security\Core\User\UserProviderInterface;
use Symfony\Component\Security\Http\RememberMe\AbstractRememberMeServices;

/**
 * This class has been copied from
 * Symfony\Component\Security\Http\RememberMe\PersistentTokenBasedRememberMeServices
 * And the security.authentication.rememberme.services.persistent service is being overwritten
 */
class PersistentTokenBasedRememberMeServices extends AbstractRememberMeServices {

    /** @var array */
    protected $config;

    public function setConfig(array $config) {
        $this->config = $config;
    }

    /** @var CacheProvider|null */
    protected $doctrineCache;

    public function setDoctrineCache(?CacheProvider $doctrineCache) {
        $this->doctrineCache = $doctrineCache;
    }

    /** @var \SplObjectStorage|null */
    protected $ddCache;

    /** @var DoctrineEntityProvider */
    private $tokenProvider;

    public function setTokenProvider(DoctrineEntityProvider $tokenProvider) {
        $this->tokenProvider = $tokenProvider;
    }

    /** @var SessionRepository */
    protected $sessionRepository;

    public function setSessionRepository(SessionRepository $sessionRepository) {
        $this->sessionRepository = $sessionRepository;
    }

    /**
     * {@inheritdoc}
     */
    protected function cancelCookie(Request $request) {
        // Delete cookie on the client
        parent::cancelCookie($request);

        // Delete cookie from the tokenProvider
        if (null !== ($cookie = $request->cookies->get($this->options['name']))
            && count($parts = $this->decodeCookie($cookie)) === 2
        ) {
            list($series) = $parts;
            $this->tokenProvider->deleteTokenBySeries($series);
        }
    }

    /**
     * {@inheritdoc}
     */
    protected function processAutoLoginCookie(array $cookieParts, Request $request) {
        if (count($cookieParts) !== 2) {
            throw new AuthenticationException('The cookie is invalid.');
        }

        list($series, $tokenValue) = $cookieParts;
        $persistentToken = $this->tokenProvider->loadTokenBySeries($series);

        if (!hash_equals($persistentToken->getTokenValue(), $tokenValue)) {
            throw new CookieTheftException('This token was already used. The account is possibly compromised.');
        }

        if ($persistentToken->getLastUsed()->getTimestamp() + $this->options['lifetime'] < time()) {
            throw new AuthenticationException('The cookie has expired.');
        }

        $tokenValue = base64_encode(random_bytes(64));
        $this->tokenProvider->updateToken($series, $tokenValue, new \DateTime());
        $request->attributes->set(self::COOKIE_ATTR_NAME,
            new Cookie(
                $this->options['name'],
                $this->encodeCookie(array($series, $tokenValue)),
                time() + $this->options['lifetime'],
                $this->options['path'],
                $this->options['domain'],
                $this->options['secure'],
                $this->options['httponly']
            )
        );

        // this is also a security hole, since
        // 1. user logs in
        // 2. user deletes their session cookie
        // 3. user refreshes their browser
        // 4. user gets a new session ID / cookie
        // 5. the new session Id/cookie is not saved @ Token
        // 6. Upon deletion of the token, only the old session ID is invalidated
        // So, there must be a way to grab the current session ID somehow and set it
        // Perhaps attaching this to listen to Interactive Login would help

        // Custom - set this session ID on this token
        $session = $request->getSession();

        if (!$session->isStarted()) {
            // This may cause a LOT of trouble
            // But I have found that it fine and solves the above issue
            // Please report any issues caused by this
            $session->start();
        }

        // Save the new Session Entity
        /** @var Session|null $sessionEntity */
        $sessionEntity = $this->addSession($persistentToken, $request);
        $this->sessionRepository->save($sessionEntity);

        // Custom - try using ID first
        /** @var UserProviderInterface $userProvider */
        $userProvider = $this->getUserProvider($persistentToken->getClass());

        if ($userProvider instanceof FindUserByIdInterface && $userId = $persistentToken->getUserId()) {
            return $userProvider->findOneById($userId);
        }

        return $userProvider->loadUserByUsername($persistentToken->getUsername());
    }

    /**
     * {@inheritdoc}
     */
    protected function onLoginSuccess(Request $request, Response $response, TokenInterface $token) {
        do {
            // Custom, do not allow duplicates even if the chance is low
            $series = base64_encode(random_bytes(64));
            try {
                $persistentToken = $this->tokenProvider->loadTokenBySeries($series);
            } catch (TokenNotFoundException $e) {
                $persistentToken = null;
            }
        } while ($persistentToken instanceof RememberMeToken);

        $tokenValue = base64_encode(random_bytes(64));

        $persistentToken = new RememberMeToken(
            get_class($user = $token->getUser()),
            $user->getUsername(),
            $series,
            $tokenValue,
            new \DateTime(),
            // Custom, set the User ID if available
            is_callable([$user, 'getId']) ? $user->getId() : null
        );

        // Custom - get the firewall name
        $providerKeyCallable = [$token, 'getProviderKey'];
        if (is_callable($providerKeyCallable)) {
            $persistentToken->setArea($providerKeyCallable());
        }

        // Custom - Add session record
        $this->addSession($persistentToken, $request);

        // Custom - fetch Device Info
        $this->fetchPlatformInformation($persistentToken, $request);

        $this->tokenProvider->createNewToken($persistentToken);

        $response->headers->setCookie(
            new Cookie(
                $this->options['name'],
                $this->encodeCookie(array($series, $tokenValue)),
                time() + $this->options['lifetime'],
                $this->options['path'],
                $this->options['domain'],
                $this->options['secure'],
                $this->options['httponly']
            )
        );
    }

    protected function addSession(RememberMeToken $token, Request $request) {
        $session = $request->getSession();

        if (!$session) {
            return;
        }

        $sessionId = $session->getId();

        if (!$sessionId) {
            return;
        }

        if ($token->getId()) {
            // If token exists, check if Session is already saved
            $sessionEntity = $this->sessionRepository->findOneByTokenIdAndIdentifier($token->getId(), $sessionId);

            // If so, we don't save again. It has a unique index on token id / session id
            if ($sessionEntity) {
                return $sessionEntity;
            }
        }

        $sessionEntity = $this->createSessionEntity($token, $sessionId);
        $token->addSession($sessionEntity);

        return $sessionEntity;
    }

    /**
     * @param RememberMeToken $token
     * @param string $sessionId
     * @return Session
     */
    protected function createSessionEntity(RememberMeToken $token, string $sessionId) {
        $class = $this->config['session_class'];

        return new $class($token, $sessionId);
    }

    protected function fetchPlatformInformation(DeviceAwareInterface $deviceInfo, Request $request) {
        $dd = $this->getDeviceDetector($request);

        $deviceInfo->setOs($dd->getOs('name'));
        $deviceInfo->setOsVersion($dd->getOs('version'));
        $deviceInfo->setDevice($dd->getDeviceName());
        $deviceInfo->setBrand($dd->getBrandName());
        $deviceInfo->setBrowser($dd->getClient('name'));
        $deviceInfo->setBrowserVersion($dd->getClient('version'));
    }

    protected function getDeviceDetector(Request $request): DeviceDetector {
        if (null === $this->ddCache) {
            $this->ddCache = new \SplObjectStorage();
        }

        if ($this->ddCache->offsetExists($request)) {
            return $this->ddCache->offsetGet($request);
        }

        $userAgent = $request->headers->get('User-Agent');

        // OPTIONAL: Set version truncation to none, so full versions will be returned
        // By default only minor versions will be returned (e.g. X.Y)
        // for other options see VERSION_TRUNCATION_* constants in DeviceParserAbstract class
        DeviceParserAbstract::setVersionTruncation(DeviceParserAbstract::VERSION_TRUNCATION_PATCH);

        $dd = new DeviceDetector($userAgent);

        // OPTIONAL: Set caching method
        // By default static cache is used, which works best within one php process (memory array caching)
        // To cache across requests use caching in files or memcache
        $dd->setCache($this->doctrineCache);

        // OPTIONAL: Set custom yaml parser
        // By default Spyc will be used for parsing yaml files. You can also use another yaml parser.
        // You may need to implement the Yaml Parser facade if you want to use another parser than Spyc or [Symfony](https://github.com/symfony/yaml)
        $dd->setYamlParser(new \DeviceDetector\Yaml\Symfony());

        // OPTIONAL: If called, getBot() will only return true if a bot was detected  (speeds up detection a bit)
        $dd->discardBotInformation();

        // OPTIONAL: If called, bot detection will completely be skipped (bots will be detected as regular devices then)
        $dd->skipBotDetection();

        $dd->parse();

        $this->ddCache->offsetSet($request, $dd);

        return $dd;
    }
}
