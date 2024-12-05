<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Shopsys\FrameworkBundle\Component\Translation\Translator;
use Shopsys\MigrationBundle\Component\Doctrine\Migrations\AbstractMigration;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;

class Version20241205094315 extends AbstractMigration implements ContainerAwareInterface
{
    use MultidomainMigrationTrait;

    /**
     * @param \Doctrine\DBAL\Schema\Schema $schema
     */
    public function up(Schema $schema): void
    {
        $this->createMailTemplateIfNotExist('watchdog_mail');

        foreach ($this->getAllDomainIds() as $domainId) {
            $domainLocale = $this->getDomainLocale($domainId);

            $this->updateMailTemplate(
                'watchdog_mail',
                t('Your watched product is back in stock!', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $domainLocale),
                t('<div class="gjs-text-ckeditor">
                            <p>Dear Customer,</p>
                            <p>We are excited to let you know that the product you added to your watchlist is now back in stock:</p>
                            <h2>{product_name}</h2>
                            <div style="text-align: center; margin: 20px 0;">
                                <img src="{product_image}" alt="{product_name}" style="max-width: 100%; height: auto; border-radius: 5px;">
                            </div>
                            <p style="text-align: center; font-size: 18px; font-weight: bold;">
                                Currently available: {product_quantity}
                            </p>
                            <p>Don’t wait too long—supplies might be limited! Click the button below to secure your item:</p> 
                            <div style="text-align: center; margin: 20px 0;">
                                <a data-cke-saved-href="{product_url}" 
                                    href="{product_url}"
                                    style="display: inline-block; padding: 15px 30px; font-size: 16px; color: #fff; background-color: #00c8b7; text-decoration: none; border-radius: 5px;">
                                    Buy Now
                                </a>
                            </div>
                            <p>Thank you for using our services, and we wish you a pleasant shopping experience!</p>
                            <p>If you need immediate assistance or have additional questions, feel free to reply to this email or contact us.</p>
                            <p>Best regards</p>
                            <hr>
                            <p>If you have any questions or need assistance, don’t hesitate to contact us.</p>
                        </div>', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $domainLocale),
                $domainId,
            );
        }
    }

    /**
     * @param string $mailTemplateName
     */
    private function createMailTemplateIfNotExist(
        string $mailTemplateName,
    ): void {
        foreach ($this->getAllDomainIds() as $domainId) {
            $mailTemplateCount = $this->sql(
                'SELECT count(*) FROM mail_templates WHERE name = :mailTemplateName and domain_id = :domainId',
                [
                    'mailTemplateName' => $mailTemplateName,
                    'domainId' => $domainId,
                ],
            )->fetchOne();

            if ($mailTemplateCount !== 0) {
                continue;
            }

            $this->sql(
                'INSERT INTO mail_templates (name, domain_id, send_mail) VALUES (:mailTemplateName, :domainId, :sendMail)',
                [
                    'mailTemplateName' => $mailTemplateName,
                    'domainId' => $domainId,
                    'sendMail' => true,
                ],
            );
        }
    }

    /**
     * @param string $mailTemplateName
     * @param string $subject
     * @param string $body
     * @param int $domainId
     */
    private function updateMailTemplate(string $mailTemplateName, string $subject, string $body, int $domainId): void
    {
        $this->sql(
            'UPDATE mail_templates SET subject = :subject, body = :body WHERE name = :mailTemplateName AND domain_id = :domainId',
            [
                'subject' => $subject,
                'body' => $body,
                'mailTemplateName' => $mailTemplateName,
                'domainId' => $domainId,
            ],
        );
    }

    /**
     * @param \Doctrine\DBAL\Schema\Schema $schema
     */
    public function down(Schema $schema): void
    {
    }
}
