<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Component\Grid\Exception;

use Exception;

class PaginationNotSupportedException extends Exception implements GridException
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
