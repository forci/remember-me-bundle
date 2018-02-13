<?php

/*
 * This file is part of the ForciRememberMeBundle package.
 *
 * (c) Martin Kirilov <wucdbm@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Forci\Bundle\RememberMeBundle\DependencyInjection;

use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Extension\Extension;
use Symfony\Component\DependencyInjection\Loader\XmlFileLoader;

class ForciRememberMeExtension extends Extension {

    public function load(array $configs, ContainerBuilder $container) {
        $configuration = $this->getConfiguration($configs, $container);
        $config = $this->processConfiguration($configuration, $configs);

        $loader = new XmlFileLoader(
            $container,
            new FileLocator(__DIR__.'/../Resources/config')
        );

        $container->setParameter('forci_remember_me.config', $config);
        $container->setParameter('forci_remember_me.token_class', $config['token_class']);
        $container->setParameter('forci_remember_me.session_class', $config['session_class']);
        $container->setParameter('forci_remember_me.cache', $config['cache']);
        $container->setParameter('forci_remember_me.doctrine_cache', $config['doctrine_cache']);

        if ('ORM' == $config['storage']) {
            $loader->load('services/orm_subscriber.xml');
        } elseif ('ODM' == $config['storage']) {
            $loader->load('services/odm_subscriber.xml');
        }

        $loader->load('services.xml');
    }

    /**
     * {@inheritdoc}
     */
    public function getConfiguration(array $config, ContainerBuilder $container) {
        return new Configuration();
    }

    public function getNamespace() {
        return 'http://www.example.com/symfony/schema/';
    }

    public function getXsdValidationBasePath() {
        return false;
    }
}
