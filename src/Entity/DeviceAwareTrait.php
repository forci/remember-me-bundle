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

trait DeviceAwareTrait {

    /** @var string|null */
    protected $os;

    /** @var string|null */
    protected $osVersion;

    /** @var string|null */
    protected $device;

    /** @var string|null */
    protected $brand;

    /** @var string|null */
    protected $browser;

    /** @var string|null */
    protected $browserVersion;

    public function getOs(): ?string {
        return $this->os;
    }

    public function setOs(?string $os) {
        $this->os = $os;
    }

    public function getOsVersion(): ?string {
        return $this->osVersion;
    }

    public function setOsVersion(?string $osVersion) {
        $this->osVersion = $osVersion;
    }

    public function getDevice(): ?string {
        return $this->device;
    }

    public function setDevice(?string $device) {
        $this->device = $device;
    }

    public function getBrand(): ?string {
        return $this->brand;
    }

    public function setBrand(?string $brand) {
        $this->brand = $brand;
    }

    public function getBrowser(): ?string {
        return $this->browser;
    }

    public function setBrowser(?string $browser) {
        $this->browser = $browser;
    }

    public function getBrowserVersion(): ?string {
        return $this->browserVersion;
    }

    public function setBrowserVersion(?string $browserVersion) {
        $this->browserVersion = $browserVersion;
    }
}
