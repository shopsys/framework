<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Payment;

use Shopsys\FrameworkBundle\Model\Customer\User\Role\CustomerUserRoleResolver;
use Shopsys\FrameworkBundle\Model\Pricing\BasePriceCalculation;
use Shopsys\FrameworkBundle\Model\Pricing\Currency\Currency;
use Shopsys\FrameworkBundle\Model\Pricing\Price;
use Shopsys\FrameworkBundle\Model\Pricing\PricingSetting;

class PaymentPriceCalculation
{
    /**
     * @param \Shopsys\FrameworkBundle\Model\Pricing\BasePriceCalculation $basePriceCalculation
     * @param \Shopsys\FrameworkBundle\Model\Pricing\PricingSetting $pricingSetting
     * @param \Shopsys\FrameworkBundle\Model\Customer\User\Role\CustomerUserRoleResolver $customerUserRoleResolver
     */
    public function __construct(
        protected readonly BasePriceCalculation $basePriceCalculation,
        protected readonly PricingSetting $pricingSetting,
        protected readonly CustomerUserRoleResolver $customerUserRoleResolver,
    ) {
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Payment\Payment $payment
     * @param \Shopsys\FrameworkBundle\Model\Pricing\Currency\Currency $currency
     * @param \Shopsys\FrameworkBundle\Model\Pricing\Price $productsPrice
     * @param int $domainId
     * @return \Shopsys\FrameworkBundle\Model\Pricing\Price
     */
    public function calculatePrice(
        Payment $payment,
        Currency $currency,
        Price $productsPrice,
        int $domainId,
    ): Price {
        if ($this->isFree($productsPrice, $domainId)) {
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

    /**
     * @param \Shopsys\FrameworkBundle\Model\Pricing\Price $productsPrice
     * @param int $domainId
     * @return bool
     */
    protected function isFree(Price $productsPrice, int $domainId): bool
    {
        if (!$this->customerUserRoleResolver->canCurrentCustomerUserSeePrices()) {
            return false;
        }

        $freeTransportAndPaymentPriceLimit = $this->pricingSetting->getFreeTransportAndPaymentPriceLimit($domainId);

        if ($freeTransportAndPaymentPriceLimit === null) {
            return false;
        }

        return $productsPrice->getPriceWithVat()->isGreaterThanOrEqualTo($freeTransportAndPaymentPriceLimit);
    }
}
