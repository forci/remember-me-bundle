<?php

/*
 * This file is part of the ForciLoginBundle package.
 *
 * (c) Martin Kirilov <wucdbm@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Forci\Bundle\RememberMeBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Security\Core\Authentication\RememberMe\PersistentTokenInterface;

/**
 * @ORM\Entity(repositoryClass="Forci\Bundle\RememberMeBundle\Repository\RememberMeTokenRepository")
 */
class RememberMeToken implements PersistentTokenInterface, DeviceAwareInterface {

    use DeviceAwareTrait;

    /** @var int */
    protected $id;

    /** @var string */
    protected $series;

    /** @var string */
    protected $value;

    /** @var \DateTime|null */
    protected $lastUsed;

    /** @var \DateTime */
    protected $dateCreated;

    /** @var string|null */
    protected $class;

    /** @var string|null */
    protected $username;

    /** @var string|null */
    protected $area;

    /** @var int|null */
    protected $userId;

    /** @var ArrayCollection|Session[] */
    protected $sessions;

    public function getUserId() {
        return $this->userId;
    }

    public function setUserId($userId) {
        $this->userId = $userId;
    }

    public function getTokenValue(): ?string {
        return $this->value;
    }

    public function getUsername(): ?string {
        return $this->username;
    }

    public function setUsername(?string $username) {
        $this->username = $username;

        return $this;
    }

    public function getClass(): ?string {
        return $this->class;
    }

    public function getLastUsed(): ?\DateTime {
        return $this->lastUsed;
    }

    public function getSeries(): ?string {
        return $this->series;
    }

    public function addSession(Session $session) {
        $this->sessions->add($session);
    }

    public function removeSession(Session $session) {
        $this->sessions->removeElement($session);
    }

    public function getSessions(): Collection {
        return $this->sessions;
    }

    public function getId(): ?int {
        return $this->id;
    }

    public function setSessions(array $sessions) {
        $this->sessions = new ArrayCollection($sessions);
    }

    public function __construct(string $class, ?string $username, string $series, string $tokenValue, int $userId = null) {
        if (empty($class)) {
            throw new \InvalidArgumentException('$class must not be empty.');
        }
        // Allow null username
        if ('' === $username) { //  || null === $username
            throw new \InvalidArgumentException('$username can be null, but must not be an empty string.');
        }
        if (empty($series)) {
            throw new \InvalidArgumentException('$series must not be empty.');
        }
        if (empty($tokenValue)) {
            throw new \InvalidArgumentException('$tokenValue must not be empty.');
        }
        if (!$userId) {
            throw new \InvalidArgumentException('$user must not be empty.');
        }

        $this->class = $class;
        $this->username = $username;
        $this->series = $series;
        $this->value = $tokenValue;
        $this->userId = $userId;
        $this->dateCreated = $this->lastUsed = new \DateTime();
        $this->sessions = new ArrayCollection();
    }

    public function setSeries(string $series) {
        $this->series = $series;

        return $this;
    }

    public function setValue(string $value) {
        $this->value = $value;

        return $this;
    }

    public function getValue(): ?string {
        return $this->value;
    }

    public function setLastUsed(\DateTime $lastUsed) {
        $this->lastUsed = $lastUsed;

        return $this;
    }

    public function setClass(string $class) {
        $this->class = $class;

        return $this;
    }

    public function setDateCreated(\DateTime $dateCreated) {
        $this->dateCreated = $dateCreated;

        return $this;
    }

    public function getDateCreated(): \DateTime {
        return $this->dateCreated;
    }

    public function getArea(): ?string {
        return $this->area;
    }

    public function setArea(?string $area) {
        $this->area = $area;
    }
}
