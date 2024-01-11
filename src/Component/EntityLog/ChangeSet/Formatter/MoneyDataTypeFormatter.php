<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Component\EntityLog\ChangeSet\Formatter;

use Shopsys\FrameworkBundle\Component\Money\Money;

class MoneyDataTypeFormatter
{
    /**
     * @param array $changes
     * @return string
     */
    public function formatChanges(array $changes): string
    {
        $changes['oldReadableValue'] = Money::create($changes['oldReadableValue'])->round(2)->getAmount();
        $changes['newReadableValue'] = Money::create($changes['newReadableValue'])->round(2)->getAmount();

        return t('from "oldReadableValue" to "newReadableValue"', $changes);
    }
}
