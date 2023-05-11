<?php

namespace Tests\FrameworkBundle\Test;

use PHPUnit\Framework\Constraint\Constraint;
use SebastianBergmann\Exporter\Exporter;
use Shopsys\FrameworkBundle\Component\Money\Money;

final class IsMoneyEqual extends Constraint
{
    private Money $value;

    private Exporter $exporter;

    /**
     * @param \Shopsys\FrameworkBundle\Component\Money\Money $value
     */
    public function __construct(Money $value)
    {
        $this->value = $value;
        $this->exporter = new MoneyExporter();
    }

    /**
     * @return string
     */
    public function toString(): string
    {
        return 'is equal money amount to expected ' . $this->exporter->export($this->value);
    }

    /**
     * @param mixed $other
     * @return bool
     */
    protected function matches($other): bool
    {
        return $other instanceof Money && $other->equals($this->value);
    }
}
