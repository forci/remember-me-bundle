<?php

namespace Forci\Bundle\RememberMeBundle;

use Forci\Bundle\RememberMeBundle\DependencyInjection\Compiler\InjectUserProvidersPass;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\Bundle\Bundle;

class RememberMeBundle extends Bundle {

    public function build(ContainerBuilder $container) {
        parent::build($container);

        $container->addCompilerPass(new InjectUserProvidersPass());
    }

}