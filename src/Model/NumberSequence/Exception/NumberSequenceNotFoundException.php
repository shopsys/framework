<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\NumberSequence\Exception;

use Exception;

class NumberSequenceNotFoundException extends Exception
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
