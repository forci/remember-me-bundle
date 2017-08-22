<?php

namespace Forci\Bundle\RememberMeBundle\DependencyInjection;

use Forci\Bundle\RememberMeBundle\Entity\RememberMeToken;
use Forci\Bundle\RememberMeBundle\Entity\Session;
use Forci\Bundle\RememberMeBundle\Repository\RememberMeTokenRepository;
use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

class Configuration implements ConfigurationInterface {

    public function getConfigTreeBuilder() {
        $treeBuilder = new TreeBuilder();
        $rootNode = $treeBuilder->root('remember_me');

        $rootNode
            ->children()
//                ->scalarNode('user_class')
//                    ->defaultNull()
//                    ->validate()
//                    ->ifTrue(function ($class) {
//                        return !class_exists($class);
//                    })->thenInvalid('Please provide an existing class for user_class or leave empty')
//                    ->ifTrue(function ($class) use ($requiredUserProperties) {
//                        foreach ($requiredUserProperties as $property) {
//                            if (!property_exists($class, $property)) {
//                                return true;
//                            }
//                        }
//                        return false;
//                        })->thenInvalid(sprintf('The class you provide for user_class should have the following properties: %s', implode(", ", $requiredUserProperties)))
//                    ->end()
//                ->end()
                ->arrayNode('area_map')
                    ->prototype('scalar')->end()
                ->end()
                ->scalarNode('token_class')
                    ->defaultValue(RememberMeToken::class)
                    ->validate()
                    ->ifTrue(function ($class) {
                        return !class_exists($class);
                    })->thenInvalid('Please provide an existing class for token_class or leave empty to use default implementation')
                    ->ifTrue(function ($class) {
                        return !is_a($class, RememberMeToken::class, true);
                    })->thenInvalid(sprintf('The class you provide for token_class should extend %s', RememberMeToken::class))
                    ->end()
                ->end()
                ->scalarNode('session_class')
                    ->defaultValue(Session::class)
                    ->validate()
                    ->ifTrue(function ($class) {
                        return !class_exists($class);
                    })->thenInvalid('Please provide an existing class for token_class or leave empty to use default implementation')
                    ->ifTrue(function ($class) {
                        return !is_a($class, Session::class, true);
                    })->thenInvalid(sprintf('The class you provide for token_class should extend %s', Session::class))
                    ->end()
                ->end()
                ->enumNode('storage')
                    ->values(['ORM', 'ODM'])
                    ->defaultValue('ORM')
                ->end()
                ->scalarNode('cache') // Symfony Cache
                    ->defaultValue('remember_me.cache')
                ->end()
                ->scalarNode('doctrine_cache') // Doctrine Cache
                    ->defaultValue('remember_me.doctrine_cache')
                ->end()
                ->scalarNode('session_handler') // Session Handler Service ID
                    ->defaultNull()
                ->end()
            ->end();

        return $treeBuilder;
    }

}