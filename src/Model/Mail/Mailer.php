<?php

namespace Shopsys\FrameworkBundle\Model\Mail;

use Swift_Attachment;
use Swift_Mailer;
use Swift_Message;
use Swift_Transport;

class Mailer
{
    /**
     * @var \Swift_Transport
     */
    protected $realSwiftTransport;

    /**
     * @var \Swift_Mailer
     */
    protected $swiftMailer;

    /**
     * @var \Shopsys\FrameworkBundle\Model\Mail\MailTemplateFacade
     */
    protected $mailTemplateFacade;

    /**
     * @param \Swift_Mailer $swiftMailer
     * @param \Swift_Transport $realSwiftTransport
     * @param \Shopsys\FrameworkBundle\Model\Mail\MailTemplateFacade $mailTemplateFacade
     */
    public function __construct(Swift_Mailer $swiftMailer, Swift_Transport $realSwiftTransport, MailTemplateFacade $mailTemplateFacade)
    {
        $this->swiftMailer = $swiftMailer;
        $this->realSwiftTransport = $realSwiftTransport;
        $this->mailTemplateFacade = $mailTemplateFacade;
    }

    public function flushSpoolQueue()
    {
        $transport = $this->swiftMailer->getTransport();
        if (!($transport instanceof \Swift_Transport_SpoolTransport)) {
            return;
        }

        $spool = $transport->getSpool();
        if ($spool instanceof \Swift_Spool) {
            $spool->flushQueue($this->realSwiftTransport);
        }
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Mail\MessageData $messageData
     */
    public function send(MessageData $messageData)
    {
        $message = $this->getMessageWithReplacedVariables($messageData);
        $failedRecipients = [];

        if ($messageData->body === null || $messageData->subject === null) {
            throw new \Shopsys\FrameworkBundle\Model\Mail\Exception\EmptyMailException();
        }

        $successSend = $this->swiftMailer->send($message, $failedRecipients);
        if (!$successSend && count($failedRecipients) > 0) {
            throw new \Shopsys\FrameworkBundle\Model\Mail\Exception\SendMailFailedException($failedRecipients);
        }
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Mail\MessageData $messageData
     * @return \Swift_Message
     */
    protected function getMessageWithReplacedVariables(MessageData $messageData)
    {
        $toEmail = $messageData->toEmail;
        $body = $this->replaceVariables(
            $messageData->body,
            $messageData->variablesReplacementsForBody
        );
        $subject = $this->replaceVariables(
            $messageData->subject,
            $messageData->variablesReplacementsForSubject
        );
        $fromEmail = $messageData->fromEmail;
        $fromName = $messageData->fromName;

        $message = new Swift_Message();
        $message->setSubject($subject);
        $message->setFrom($fromEmail, $fromName);
        $message->setTo($toEmail);
        if ($messageData->bccEmail !== null) {
            $message->addBcc($messageData->bccEmail);
        }
        if ($messageData->replyTo !== null) {
            $message->addReplyTo($messageData->replyTo);
        }
        $message->setContentType('text/plain; charset=UTF-8');
        $message->setBody(htmlspecialchars_decode(strip_tags($body), ENT_QUOTES), 'text/plain');
        $message->addPart($body, 'text/html');

        foreach ($messageData->attachments as $attachment) {
            $swiftAttachment = Swift_Attachment::fromPath($this->mailTemplateFacade->getMailTemplateAttachmentFilepath($attachment));
            $swiftAttachment->setFilename($attachment->getNameWithExtension());
            $message->attach($swiftAttachment);
        }

        return $message;
    }

    /**
     * @param string $string
     * @param array $variablesKeysAndValues
     * @return string
     */
    protected function replaceVariables($string, $variablesKeysAndValues)
    {
        return strtr($string, $variablesKeysAndValues);
    }
}
