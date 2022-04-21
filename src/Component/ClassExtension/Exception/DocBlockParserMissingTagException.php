<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Component\ClassExtension\Exception;

class DocBlockParserMissingTagException extends DocBlockParserException
{
    /**
     * @param string $tagName
     * @param string $parameterName
     */
    public function __construct(string $tagName, string $parameterName)
    {
        parent::__construct("Doc block does not have ${tagName} tag for ${parameterName}.");
    }
}
