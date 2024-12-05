<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Customer\Mail;

use Shopsys\FrameworkBundle\Component\Setting\Setting;
use Shopsys\FrameworkBundle\Model\Customer\User\ResetPasswordInterface;
use Shopsys\FrameworkBundle\Model\Mail\MailTemplate;
use Shopsys\FrameworkBundle\Model\Mail\MessageData;
use Shopsys\FrameworkBundle\Model\Mail\MessageFactoryInterface;
use Shopsys\FrameworkBundle\Model\Mail\Setting\MailSetting;

class ResetPasswordMail implements MessageFactoryInterface
{
    public const VARIABLE_EMAIL = '{email}';
    public const VARIABLE_NEW_PASSWORD_URL = '{new_password_url}';

    /**
     * @param \Shopsys\FrameworkBundle\Component\Setting\Setting $setting
     * @param \Shopsys\FrameworkBundle\Model\Customer\Mail\NewPasswordUrlProvider $newPasswordUrlProvider
     */
    public function __construct(
        protected readonly Setting $setting,
        protected readonly NewPasswordUrlProvider $newPasswordUrlProvider,
    ) {
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Mail\MailTemplate $template
     * @param \Shopsys\FrameworkBundle\Model\Customer\User\ResetPasswordInterface $customerUser
     * @return \Shopsys\FrameworkBundle\Model\Mail\MessageData
     */
    public function createMessage(MailTemplate $template, $customerUser)
    {
        return new MessageData(
            $customerUser->getEmail(),
            $template->getBccEmail(),
            $template->getBody(),
            $template->getSubject(),
            $this->setting->getForDomain(MailSetting::MAIN_ADMIN_MAIL, $customerUser->getDomainId()),
            $this->setting->getForDomain(MailSetting::MAIN_ADMIN_MAIL_NAME, $customerUser->getDomainId()),
            $this->getBodyValuesIndexedByVariableName($customerUser),
            $this->getSubjectValuesIndexedByVariableName($customerUser),
        );
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Customer\User\ResetPasswordInterface $customerUser
     * @return string[]
     */
    protected function getBodyValuesIndexedByVariableName(ResetPasswordInterface $customerUser)
    {
        return [
            self::VARIABLE_EMAIL => htmlspecialchars($customerUser->getEmail(), ENT_QUOTES),
            self::VARIABLE_NEW_PASSWORD_URL => $this->newPasswordUrlProvider->getNewPasswordUrl($customerUser),
        ];
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Customer\User\ResetPasswordInterface $customerUser
     * @return string[]
     */
    protected function getSubjectValuesIndexedByVariableName(ResetPasswordInterface $customerUser)
    {
        return $this->getBodyValuesIndexedByVariableName($customerUser);
    }
}
