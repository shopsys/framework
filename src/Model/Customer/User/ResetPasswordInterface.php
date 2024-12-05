<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Customer\User;

interface ResetPasswordInterface
{
    /**
     * @return int
     */
    public function getId();

    /**
     * @param string|null $hash
     * @return bool
     */
    public function isResetPasswordHashValid(?string $hash): bool;

    /**
     * @return string
     */
    public function getResetPasswordHash();

    /**
     * @return int
     */
    public function getDomainId();

    /**
     * @return string
     */
    public function getEmail();
}
