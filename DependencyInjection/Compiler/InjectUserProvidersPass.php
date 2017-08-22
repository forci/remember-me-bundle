<?php

namespace Forci\Bundle\RememberMeBundle\DependencyInjection\Compiler;

use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;

class InjectUserProvidersPass implements CompilerPassInterface {

    /**
     * {@inheritdoc}
     */
    public function process(ContainerBuilder $container) {
        $extension = $container->getDefinition('Forci\Bundle\RememberMeBundle\Twig\TokenExtension');

        $userProviderIds = [];

        foreach ($container->findTaggedServiceIds('security.remember_me_aware') as $serviceId => $attributes) {
            foreach ($attributes as $attribute) {
                if (isset($attribute['provider'])) {
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


