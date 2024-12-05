<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Customer\Mail;

use Shopsys\FrameworkBundle\Component\Setting\Setting;
use Shopsys\FrameworkBundle\Model\Customer\User\ResetPasswordInterface;
use Shopsys\FrameworkBundle\Model\Mail\MailTemplate;
use Shopsys\FrameworkBundle\Model\Mail\MessageData;
use Shopsys\FrameworkBundle\Model\Mail\MessageFactoryInterface;
use Shopsys\FrameworkBundle\Model\Mail\Setting\MailSetting;

class CustomerActivationMail implements MessageFactoryInterface
{
    public const string CUSTOMER_ACTIVATION_NAME = 'customer_activation';
    public const string VARIABLE_EMAIL = '{email}';
    public const string VARIABLE_ACTIVATION_URL = '{activation_url}';

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
    protected function getBodyValuesIndexedByVariableName(ResetPasswordInterface $customerUser): array
    {
        return [
            self::VARIABLE_EMAIL => htmlspecialchars($customerUser->getEmail(), ENT_QUOTES),
            self::VARIABLE_ACTIVATION_URL => $this->newPasswordUrlProvider->getNewPasswordUrl($customerUser),
        ];
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Customer\User\ResetPasswordInterface $customerUser
     * @return string[]
     */
    protected function getSubjectValuesIndexedByVariableName(ResetPasswordInterface $customerUser): array
    {
        return $this->getBodyValuesIndexedByVariableName($customerUser);
    }
}
