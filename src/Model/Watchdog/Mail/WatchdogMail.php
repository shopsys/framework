<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Watchdog\Mail;

use Shopsys\FrameworkBundle\Component\Domain\Domain;
use Shopsys\FrameworkBundle\Component\Mailer\MailerHelper;
use Shopsys\FrameworkBundle\Component\Router\DomainRouterFactory;
use Shopsys\FrameworkBundle\Component\Setting\Setting;
use Shopsys\FrameworkBundle\Model\Mail\MailTemplate;
use Shopsys\FrameworkBundle\Model\Mail\MessageData;
use Shopsys\FrameworkBundle\Model\Mail\Setting\MailSetting;
use Shopsys\FrameworkBundle\Model\Product\Availability\ProductAvailabilityFacade;
use Shopsys\FrameworkBundle\Model\Product\Image\ProductImageFacade;
use Shopsys\FrameworkBundle\Model\Watchdog\Watchdog;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class WatchdogMail
{
    public const string WATCHDOG_MAIL_TEMPLATE_NAME = 'watchdog_mail';
    public const string VARIABLE_PRODUCT_NAME = '{product_name}';
    public const string VARIABLE_PRODUCT_QUANTITY = '{product_quantity}';
    public const string VARIABLE_PRODUCT_URL = '{product_url}';
    public const string VARIABLE_PRODUCT_IMAGE = '{product_image}';

    /**
     * @param \Shopsys\FrameworkBundle\Component\Setting\Setting $setting
     * @param \Shopsys\FrameworkBundle\Component\Domain\Domain $domain
     * @param \Shopsys\FrameworkBundle\Component\Router\DomainRouterFactory $domainRouterFactory
     * @param \Shopsys\FrameworkBundle\Model\Product\Availability\ProductAvailabilityFacade $productAvailabilityFacade
     * @param \Shopsys\FrameworkBundle\Model\Product\Image\ProductImageFacade $productImageFacade
     */
    public function __construct(
        protected readonly Setting $setting,
        protected readonly Domain $domain,
        protected readonly DomainRouterFactory $domainRouterFactory,
        protected readonly ProductAvailabilityFacade $productAvailabilityFacade,
        protected readonly ProductImageFacade $productImageFacade,
    ) {
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Mail\MailTemplate $template
     * @param \Shopsys\FrameworkBundle\Model\Watchdog\Watchdog $watchdog
     * @return \Shopsys\FrameworkBundle\Model\Mail\MessageData
     */
    public function createMessage(MailTemplate $template, Watchdog $watchdog): MessageData
    {
        $locale = $this->domain->getDomainConfigById($watchdog->getDomainId())->getLocale();

        return new MessageData(
            $watchdog->getEmail(),
            $template->getBccEmail(),
            $template->getBody(),
            $template->getSubject(),
            $this->setting->getForDomain(MailSetting::MAIN_ADMIN_MAIL, $watchdog->getDomainId()),
            $this->setting->getForDomain(MailSetting::MAIN_ADMIN_MAIL_NAME, $watchdog->getDomainId()),
            $this->getBodyVariablesReplacements($watchdog, $locale),
            $this->getSubjectVariablesReplacements($watchdog, $locale),
        );
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Watchdog\Watchdog $watchdog
     * @param string $locale
     * @return array<string, string>
     */
    protected function getSubjectVariablesReplacements(Watchdog $watchdog, string $locale): array
    {
        return [
            self::VARIABLE_PRODUCT_NAME => MailerHelper::escapeOptionalString($watchdog->getProduct()->getName($locale)),
        ];
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Watchdog\Watchdog $watchdog
     * @param string $locale
     * @return array<string, string>
     */
    protected function getBodyVariablesReplacements(Watchdog $watchdog, string $locale): array
    {
        return [
            ...$this->getSubjectVariablesReplacements($watchdog, $locale),
            self::VARIABLE_PRODUCT_URL => $this->getProductUrl($watchdog),
            self::VARIABLE_PRODUCT_IMAGE => $this->productImageFacade->getProductImageUrl($watchdog->getProduct(), $watchdog->getDomainId()),
            self::VARIABLE_PRODUCT_QUANTITY => $this->getProductQuantity($watchdog, $locale),
        ];
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Watchdog\Watchdog $watchdog
     * @param string $locale
     * @return string
     */
    protected function getProductQuantity(Watchdog $watchdog, string $locale): string
    {
        $productQuantity = $this->productAvailabilityFacade->getGroupedStockQuantityByProductAndDomainId(
            $watchdog->getProduct(),
            $watchdog->getDomainId(),
        );

        $unit = $watchdog->getProduct()->getUnit()->getName($locale);

        if ($productQuantity === null) {
            return '';
        }

        return $productQuantity . ' ' . $unit;
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Watchdog\Watchdog $watchdog
     * @return string
     */
    protected function getProductUrl(Watchdog $watchdog): string
    {
        return $this->domainRouterFactory->getRouter($watchdog->getDomainId())->generate(
            'front_product_detail',
            ['id' => $watchdog->getProduct()->getId()],
            UrlGeneratorInterface::ABSOLUTE_URL,
        );
    }
}
