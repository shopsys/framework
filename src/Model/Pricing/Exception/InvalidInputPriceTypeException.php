<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Pricing\Exception;

use Exception;

class InvalidInputPriceTypeException extends Exception implements PricingException
{
    /**
     * @param string $message
     * @param \Exception|null $previous
     */
    public function __construct($message = '', ?Exception $previous = null)
    {
        parent::__construct($message, 0, $previous);
    }
}
