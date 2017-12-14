<?php

/*
 * This file is part of the ForciRememberMeBundle package.
 *
 * (c) Martin Kirilov <wucdbm@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Forci\Bundle\RememberMeBundle\DependencyInjection\Compiler;

use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;

class SetSessionHandlerPass implements CompilerPassInterface {

    /**
     * {@inheritdoc}
     */
    public function process(ContainerBuilder $container) {
        $serviceId = $container->getParameter('forci_remember_me.session_handler_service_id');

        if (!$container->hasDefinition($serviceId)) {
            throw new \InvalidArgumentException(sprintf('The provided Session Handler Service ID %s is missing', $serviceId));
        }

        $definition = $container->getDefinition($serviceId);
        if (!is_a($definition->getClass(), \SessionHandlerInterface::class, true)) {
            throw new \InvalidArgumentException(sprintf('The provided Session Handler Service ID %s is not an instance of %s', $serviceId, \SessionHandlerInterface::class));
        }

        $container->setAlias('forci_remember_me.session_handler', $serviceId);
    }
}
