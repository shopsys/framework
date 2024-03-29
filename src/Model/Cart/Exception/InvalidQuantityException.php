<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Cart\Exception;

use Exception;

class InvalidQuantityException extends Exception implements CartException
{
    protected mixed $invalidValue;

    /**
     * @param mixed $invalidValue
     * @param string $message
     * @param \Exception|null $previous
     */
    public function __construct($invalidValue, $message = '', ?Exception $previous = null)
    {
        $this->invalidValue = $invalidValue;

        parent::__construct($message, 0, $previous);
    }

    /**
     * @return mixed
     */
    public function getInvalidValue()
    {
        return $this->invalidValue;
    }
}
