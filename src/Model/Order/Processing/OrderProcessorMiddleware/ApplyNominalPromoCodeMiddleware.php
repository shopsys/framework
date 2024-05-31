<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Order\Processing\OrderProcessorMiddleware;

use Override;
use Shopsys\FrameworkBundle\Component\Domain\Config\DomainConfig;
use Shopsys\FrameworkBundle\Component\Money\Money;
use Shopsys\FrameworkBundle\Component\Translation\Translator;
use Shopsys\FrameworkBundle\Model\Order\Item\OrderItemData;
use Shopsys\FrameworkBundle\Model\Order\Item\OrderItemDataFactory;
use Shopsys\FrameworkBundle\Model\Order\Item\OrderItemTypeEnum;
use Shopsys\FrameworkBundle\Model\Order\OrderData;
use Shopsys\FrameworkBundle\Model\Order\Processing\OrderProcessingData;
use Shopsys\FrameworkBundle\Model\Order\PromoCode\CurrentPromoCodeFacade;
use Shopsys\FrameworkBundle\Model\Order\PromoCode\DiscountCalculation;
use Shopsys\FrameworkBundle\Model\Order\PromoCode\PromoCode;
use Shopsys\FrameworkBundle\Model\Order\PromoCode\PromoCodeFacade;
use Shopsys\FrameworkBundle\Model\Order\PromoCode\PromoCodeLimit\PromoCodeLimit;
use Shopsys\FrameworkBundle\Model\Pricing\Vat\VatFacade;
use Shopsys\FrameworkBundle\Twig\PriceExtension;

class ApplyNominalPromoCodeMiddleware extends AbstractPromoCodeMiddleware
{
    /**
     * @param \Shopsys\FrameworkBundle\Model\Order\PromoCode\CurrentPromoCodeFacade $currentPromoCodeFacade
     * @param \Shopsys\FrameworkBundle\Model\Order\PromoCode\PromoCodeFacade $promoCodeFacade
     * @param \Shopsys\FrameworkBundle\Model\Order\PromoCode\DiscountCalculation $discountCalculation
     * @param \Shopsys\FrameworkBundle\Model\Order\Item\OrderItemDataFactory $orderItemDataFactory
     * @param \Shopsys\FrameworkBundle\Twig\PriceExtension $priceExtension
     * @param \Shopsys\FrameworkBundle\Model\Pricing\Vat\VatFacade $vatFacade
     */
    public function __construct(
        CurrentPromoCodeFacade $currentPromoCodeFacade,
        PromoCodeFacade $promoCodeFacade,
        protected readonly DiscountCalculation $discountCalculation,
        protected readonly OrderItemDataFactory $orderItemDataFactory,
        protected readonly PriceExtension $priceExtension,
        protected readonly VatFacade $vatFacade,
    ) {
        parent::__construct($currentPromoCodeFacade, $promoCodeFacade);
    }

    /**
     * {@inheritdoc}
     */
    #[Override]
    protected function getSupportedTypes(): array
    {
        return [PromoCode::DISCOUNT_TYPE_NOMINAL];
    }

    /**
     * {@inheritdoc}
     */
    #[Override]
    public function createAndAddOrderItemData(
        OrderData $orderData,
        array $validProductIds,
        PromoCode $appliedPromoCode,
        PromoCodeLimit $promoCodeLimit,
        OrderProcessingData $orderProcessingData,
    ): void {
        $discountOrderItemData = $this->createDiscountOrderItemData(
            $appliedPromoCode,
            $promoCodeLimit,
            $orderProcessingData->getDomainConfig(),
        );

        if ($discountOrderItemData === null) {
            return;
        }

        $orderData->addItem($discountOrderItemData);
        $orderData->addTotalPrice($discountOrderItemData->getTotalPrice(), OrderItemTypeEnum::TYPE_DISCOUNT);
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Order\PromoCode\PromoCode $promoCode
     * @param \Shopsys\FrameworkBundle\Model\Order\PromoCode\PromoCodeLimit\PromoCodeLimit $promoCodeLimit
     * @param \Shopsys\FrameworkBundle\Component\Domain\Config\DomainConfig $domainConfig
     * @return \Shopsys\FrameworkBundle\Model\Order\Item\OrderItemData|null
     */
    protected function createDiscountOrderItemData(
        PromoCode $promoCode,
        PromoCodeLimit $promoCodeLimit,
        DomainConfig $domainConfig,
    ): ?OrderItemData {
        $locale = $domainConfig->getLocale();
        $domainId = $domainConfig->getId();

        $defaultVat = $this->vatFacade->getDefaultVatForDomain($domainId);

        $discountPrice = $this->discountCalculation->calculateNominalDiscount(
            Money::create($promoCodeLimit->getDiscount()),
            (float)$defaultVat->getPercent(),
        );

        $discountOrderItemData = $this->orderItemDataFactory->create(OrderItemTypeEnum::TYPE_DISCOUNT);

        $discountPrice = $discountPrice->inverse();

        $name = sprintf(
            '%s %s',
            t('Promo code', [], Translator::DEFAULT_TRANSLATION_DOMAIN, $locale),
            $this->priceExtension->priceFilter($discountPrice->getPriceWithVat()),
        );

        $discountOrderItemData->name = $name;
        $discountOrderItemData->quantity = 1;
        $discountOrderItemData->setUnitPrice($discountPrice);
        $discountOrderItemData->setTotalPrice($discountPrice);
        $discountOrderItemData->vatPercent = $defaultVat->getPercent();
        $discountOrderItemData->promoCode = $promoCode;

        return $discountOrderItemData;
    }
}
