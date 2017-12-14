<?php

/*
 * This file is part of the ForciLoginBundle package.
 *
 * (c) Martin Kirilov <wucdbm@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Forci\Bundle\RememberMeBundle\DependencyInjection;

use Forci\Bundle\RememberMeBundle\Entity\RememberMeToken;
use Forci\Bundle\RememberMeBundle\Entity\Session;
use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

class Configuration implements ConfigurationInterface {

    public function getConfigTreeBuilder() {
        $treeBuilder = new TreeBuilder();
        $rootNode = $treeBuilder->root('remember_me');

        $rootNode
            ->children()
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
                ->arrayNode('area_map')
                    ->prototype('scalar')->end()
                ->end()
                ->enumNode('storage')
                    ->values(['ORM']) // , 'ODM' // add ODM once it's implemented
                    ->defaultValue('ORM')
                ->end()
                ->scalarNode('cache') // Symfony Cache
                    ->defaultValue('remember_me.cache')
                ->end()
                ->scalarNode('doctrine_cache') // Doctrine Cache
                    ->defaultValue('remember_me.doctrine_cache')
                ->end()
            ->end();

        return $treeBuilder;
    }
}
