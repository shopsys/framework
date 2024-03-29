<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Component\Elasticsearch\Exception;

use RuntimeException;
use Throwable;

class ElasticsearchMoreThanOneCurrentIndexException extends RuntimeException implements ElasticsearchException
{
    /**
     * @param string $aliasName
     * @param \Throwable|null $previous
     */
    public function __construct(string $aliasName, ?Throwable $previous = null)
    {
        $message = sprintf(
            'There is more than one index aliased "%s". Please delete all non current ones.',
            $aliasName,
        );

        parent::__construct($message, 0, $previous);
    }
}
