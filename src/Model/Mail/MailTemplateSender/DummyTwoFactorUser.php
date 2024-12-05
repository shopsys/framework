<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Mail\MailTemplateSender;

use Scheb\TwoFactorBundle\Model\Email\TwoFactorInterface;

class DummyTwoFactorUser implements TwoFactorInterface
{
    protected const string DUMMY_EMAIL_AUTH_CODE = 'dummy-email-auth-code';

    /**
     * @param string $email
     */
    public function __construct(protected readonly string $email)
    {
    }

    /**
     * @return bool
     */
    public function isEmailAuthEnabled(): bool
    {
        return true;
    }

    /**
     * @return string
     */
    public function getEmailAuthRecipient(): string
    {
        return $this->email;
    }

    /**
     * @return string|null
     */
    public function getEmailAuthCode(): ?string
    {
        return static::DUMMY_EMAIL_AUTH_CODE;
    }

    /**
     * @param string $authCode
     */
    public function setEmailAuthCode(string $authCode): void
    {
    }
}
