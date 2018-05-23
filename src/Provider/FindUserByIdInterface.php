<?php

/*
 * This file is part of the ForciRememberMeBundle package.
 *
 * (c) Martin Kirilov <wucdbm@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Forci\Bundle\RememberMe\Provider;

use Symfony\Component\Security\Core\User\UserInterface;

interface FindUserByIdInterface {

    /**
     * @param int $id
     *
     * @return UserInterface
     */
    public function findOneById(int $id);
}
