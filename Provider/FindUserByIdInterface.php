<?php

namespace Forci\Bundle\RememberMeBundle\Provider;

use Symfony\Component\Security\Core\User\UserInterface;

interface FindUserByIdInterface {

    /**
     * @param int $id
     * @return UserInterface
     */
    public function findOneById(int $id);

}