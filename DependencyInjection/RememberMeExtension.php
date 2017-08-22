<?php

namespace Forci\Bundle\RememberMeBundle\DependencyInjection;

use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Extension\Extension;
use Symfony\Component\DependencyInjection\Loader\XmlFileLoader;

class RememberMeExtension extends Extension {

    public function load(array $configs, ContainerBuilder $container) {
        $configuration = $this->getConfiguration($configs, $container);
        $config = $this->processConfiguration($configuration, $configs);

        $loader = new XmlFileLoader(
            $container,
            new FileLocator(__DIR__ . '/../Resources/config')
        );

        $container->setParameter('remember_me.config', $config);
        $container->setParameter('remember_me.token_class', $config['token_class']);
        $container->setParameter('remember_me.session_class', $config['session_class']);
        $container->setParameter('remember_me.cache', $config['cache']);
        $container->setParameter('remember_me.doctrine_cache', $config['doctrine_cache']);
        $container->setParameter('remember_me.session_handler_service_id', $config['session_handler']);

        if ($config['storage'] == 'ORM') {
            $loader->load('services/orm_subscriber.xml');
        } elseif ($config['storage'] == 'ODM') {
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
