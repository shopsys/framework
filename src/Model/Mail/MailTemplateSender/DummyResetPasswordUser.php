<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Mail\MailTemplateSender;

use Shopsys\FrameworkBundle\Model\Customer\User\ResetPasswordInterface;

class DummyResetPasswordUser implements ResetPasswordInterface
{
    protected const string DUMMY_RESET_PASSWORD_HASH = 'dummy-reset-password-hash';

    /**
     * @param string $email
     * @param int $domainId
     */
    public function __construct(protected readonly string $email, protected readonly int $domainId)
    {
    }

    public function getId()
    {
        return 1;
    }

    /**
     * @param string|null $hash
     * @return bool
     */
    public function isResetPasswordHashValid(?string $hash): bool
    {
        return true;
    }

    public function getResetPasswordHash()
    {
        return static::DUMMY_RESET_PASSWORD_HASH;
    }

    public function getDomainId()
    {
        return $this->domainId;
    }

    public function getEmail()
    {
        return $this->email;
    }
}
