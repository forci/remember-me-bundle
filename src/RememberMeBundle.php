<?php

/*
 * This file is part of the ForciLoginBundle package.
 *
 * (c) Martin Kirilov <wucdbm@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

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
