<?php

namespace Forci\Bundle\RememberMeBundle\DependencyInjection\Compiler;

use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;

class SetSessionHandlerPass implements CompilerPassInterface {

    /**
     * {@inheritdoc}
     */
    public function process(ContainerBuilder $container) {
        $serviceId = $container->getParameter('remember_me.session_handler_service_id');

        if (!$container->hasDefinition($serviceId)) {
            throw new \InvalidArgumentException(sprintf('The provided Session Handler Service ID %s is missing', $serviceId));
        }

        $definition = $container->getDefinition($serviceId);
        if (!is_a($definition->getClass(), \SessionHandlerInterface::class, true)) {
            throw new \InvalidArgumentException(sprintf('The provided Session Handler Service ID %s is not an instance of %s', $serviceId, \SessionHandlerInterface::class));
        }

        $container->setAlias('remember_me.session_handler', $serviceId);
    }

}


