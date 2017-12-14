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

interface DeviceAwareInterface {

    public function getOs(): ?string;

    public function setOs(?string $os);

    public function getOsVersion(): ?string;

    public function setOsVersion(?string $osVersion);

    public function getDevice(): ?string;

    public function setDevice(?string $device);

    public function getBrand(): ?string;

    public function setBrand(?string $brand);

    public function getBrowser(): ?string;

    public function setBrowser(?string $browser);

    public function getBrowserVersion(): ?string;

    public function setBrowserVersion(?string $browserVersion);
}
