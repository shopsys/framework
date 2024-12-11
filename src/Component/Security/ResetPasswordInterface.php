<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Component\Security;

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
     * @return string
     */
    public function getEmail();
}
