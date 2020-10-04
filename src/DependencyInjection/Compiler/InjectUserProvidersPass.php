<?php

/*
 * This file is part of the ForciRememberMeBundle package.
 *
 * (c) Martin Kirilov <wucdbm@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Forci\Bundle\RememberMe\DependencyInjection\Compiler;

use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;

class InjectUserProvidersPass implements CompilerPassInterface {

    /**
     * {@inheritdoc}
     */
    public function process(ContainerBuilder $container) {
        $extension = $container->getDefinition('Forci\Bundle\RememberMe\Twig\TokenExtension');

        $userProviderIds = [];

        foreach ($container->findTaggedServiceIds('security.remember_me_aware') as $serviceId => $attributes) {
            foreach ($attributes as $attribute) {
                if (isset($attribute['provider']) && 'none' !== $attribute['provider']) {
                    $userProviderIds[] = $attribute['provider'];
                }
            }
        }

        $userProviderIds = array_unique($userProviderIds);

        $userProviders = [];

        foreach ($userProviderIds as $serviceId) {
            $userProviders[] = new Reference($serviceId);
        }

        $extension->replaceArgument(0, $userProviders);
    }
}
