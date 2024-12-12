<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Mail\MailTemplateSender;

use Shopsys\FrameworkBundle\Model\Mail\MailTemplate;

interface MailTemplateSenderInterface
{
    /**
     * @return string|null
     */
    public function getFormLabelForEntityIdentifier(): ?string;

    /**
     * @param \Shopsys\FrameworkBundle\Model\Mail\MailTemplate $mailTemplate
     * @return bool
     */
    public function supports(MailTemplate $mailTemplate): bool;

    /**
     * @param \Shopsys\FrameworkBundle\Model\Mail\MailTemplate $mailTemplate
     * @param string $mailTo
     * @param int|null $entityId
     */
    public function sendTemplate(MailTemplate $mailTemplate, string $mailTo, ?int $entityId): void;
}
