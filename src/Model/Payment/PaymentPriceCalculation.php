<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Payment;

use Shopsys\FrameworkBundle\Model\Pricing\BasePriceCalculation;
use Shopsys\FrameworkBundle\Model\Pricing\Currency\Currency;
use Shopsys\FrameworkBundle\Model\Pricing\Price;
use Shopsys\FrameworkBundle\Model\Pricing\PricingSetting;
use Shopsys\FrameworkBundle\Model\TransportAndPayment\FreeTransportAndPaymentFacade;

class PaymentPriceCalculation
{
    /**
     * @param \Shopsys\FrameworkBundle\Model\Pricing\BasePriceCalculation $basePriceCalculation
     * @param \Shopsys\FrameworkBundle\Model\Pricing\PricingSetting $pricingSetting
     * @param \Shopsys\FrameworkBundle\Model\TransportAndPayment\FreeTransportAndPaymentFacade $freeTransportAndPaymentFacade
     */
    public function __construct(
        protected readonly BasePriceCalculation $basePriceCalculation,
        protected readonly PricingSetting $pricingSetting,
        protected readonly FreeTransportAndPaymentFacade $freeTransportAndPaymentFacade,
    ) {
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Payment\Payment $payment
     * @param \Shopsys\FrameworkBundle\Model\Pricing\Currency\Currency $currency
     * @param \Shopsys\FrameworkBundle\Model\Pricing\Price $productsPrice
     * @param int $domainId
     * @param bool $forceFreePayment
     * @return \Shopsys\FrameworkBundle\Model\Pricing\Price
     */
    public function calculatePrice(
        Payment $payment,
        Currency $currency,
        Price $productsPrice,
        int $domainId,
        bool $forceFreePayment,
    ): Price {
        if ($this->freeTransportAndPaymentFacade->isFree($productsPrice->getPriceWithVat(), $domainId, $forceFreePayment)) {
            return Price::zero();
        }

        return $this->calculateIndependentPrice($payment, $currency, $domainId);
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Payment\Payment $payment
     * @param \Shopsys\FrameworkBundle\Model\Pricing\Currency\Currency $currency
     * @param int $domainId
     * @return \Shopsys\FrameworkBundle\Model\Pricing\Price
     */
    public function calculateIndependentPrice(Payment $payment, Currency $currency, int $domainId): Price
    {
        return $this->basePriceCalculation->calculateBasePriceRoundedByCurrency(
            $payment->getPrice($domainId)->getPrice(),
            $this->pricingSetting->getInputPriceType(),
            $payment->getPaymentDomain($domainId)->getVat(),
            $currency,
        );
    }
}
