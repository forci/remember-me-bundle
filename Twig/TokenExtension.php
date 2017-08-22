<?php

namespace Forci\Bundle\RememberMeBundle\Twig;

use Forci\Bundle\RememberMeBundle\Entity\RememberMeToken;
use Forci\Bundle\RememberMeBundle\Provider\FindUserByIdInterface;
use Symfony\Component\Security\Core\Exception\UnsupportedUserException;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\User\UserProviderInterface;

class TokenExtension extends \Twig_Extension {

    /** @var UserProviderInterface[] */
    protected $userProviders;

    /** @var array */
    protected $config;

    public function __construct(array $userProviders = [], array $config) {
        $this->userProviders = $userProviders;
        $this->config = $config;
    }

    public function getFilters() {
        return [
            new \Twig_SimpleFilter('rememberMeAreaName', [$this, 'rememberMeAreaName'])
        ];
    }

    public function rememberMeAreaName($area): string {
        if ($area instanceof RememberMeToken) {
            $area = $area->getArea();
        }

        if (isset($this->config['area_map'][$area])) {
            return $this->config['area_map'][$area];
        }

        return $area;
    }

    public function getFunctions() {
        return [
            new \Twig_SimpleFunction('getUserForToken', [$this, 'getUserForToken'])
        ];
    }

    public function getUserForToken(RememberMeToken $token): ?UserInterface {
        try {
            $provider = $this->getUserProvider($token->getClass());
        } catch (UnsupportedUserException $e) {
            return null;
        }

        if ($provider instanceof FindUserByIdInterface && $userId = $token->getUserId()) {
            return $provider->findOneById($userId);
        }

        return $provider->loadUserByUsername($token->getUsername());
    }

    final protected function getUserProvider($class): UserProviderInterface {
        /** @var UserProviderInterface $provider */
        foreach ($this->userProviders as $provider) {
            if ($provider->supportsClass($class)) {
                return $provider;
            }
        }

        throw new UnsupportedUserException(sprintf('There is no user provider that supports class "%s".', $class));
    }

}