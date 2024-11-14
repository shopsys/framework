<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Stock;

class StockSettingsData
{
    /**
     * @var int|null
     */
    public $transfer;

    /**
     * @var array<string, mixed>
     */
    public $pluginData = [];

    /**
     * @var int|null
     */
    public $feedDeliveryDaysForOutOfStockProducts;
}
