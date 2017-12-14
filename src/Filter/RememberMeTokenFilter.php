<?php

/*
 * This file is part of the ForciLoginBundle package.
 *
 * (c) Martin Kirilov <wucdbm@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Forci\Bundle\RememberMeBundle\Filter;

use Wucdbm\Bundle\QuickUIBundle\Filter\AbstractFilter;

class RememberMeTokenFilter extends AbstractFilter {

    /** @var int|null */
    protected $userId;

    /** @var string|null */
    protected $username;

    /** @var string|null */
    protected $area;

    /** @var \DateTime|null */
    protected $dateMin;

    /** @var \DateTime|null */
    protected $dateMax;

    public function getUserId(): ?int {
        return $this->userId;
    }

    public function setUserId(?int $userId) {
        $this->userId = $userId;
    }

    public function getUsername(): ?string {
        return $this->username;
    }

    public function setUsername(?string $username) {
        $this->username = $username;
    }

    public function getArea(): ?string {
        return $this->area;
    }

    public function setArea(?string $area) {
        $this->area = $area;
    }

    public function getDateMin(): ?\DateTime {
        return $this->dateMin;
    }

    public function setDateMin(?\DateTime $dateMin) {
        $this->dateMin = $dateMin;
    }

    public function getDateMax(): ?\DateTime {
        return $this->dateMax;
    }

    public function setDateMax(?\DateTime $dateMax) {
        $this->dateMax = $dateMax;
    }
}
