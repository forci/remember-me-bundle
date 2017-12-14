<?php

/*
 * This file is part of the ForciRememberMeBundle package.
 *
 * (c) Martin Kirilov <wucdbm@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Forci\Bundle\RememberMeBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="Forci\Bundle\RememberMeBundle\Repository\SessionRepository")
 */
class Session {

    /** @var int */
    protected $id;

    /** @var string */
    protected $identifier;

    /** @var \DateTime */
    protected $dateCreated;

    /** @var RememberMeToken */
    protected $token;

    public function __construct(RememberMeToken $token, string $sessionId) {
        $this->token = $token;
        $this->identifier = $sessionId;
        $this->dateCreated = new \DateTime();
    }

    public function getId(): ?int {
        return $this->id;
    }

    public function setId(?int $id) {
        $this->id = $id;
    }

    public function getIdentifier(): ?string {
        return $this->identifier;
    }

    public function setIdentifier(?string $identifier) {
        $this->identifier = $identifier;
    }

    public function setDateCreated(\DateTime $dateCreated) {
        $this->dateCreated = $dateCreated;

        return $this;
    }

    public function getDateCreated(): \DateTime {
        return $this->dateCreated;
    }

    public function getToken(): ?RememberMeToken {
        return $this->token;
    }

    public function setToken(?RememberMeToken $token) {
        $this->token = $token;
    }
}
