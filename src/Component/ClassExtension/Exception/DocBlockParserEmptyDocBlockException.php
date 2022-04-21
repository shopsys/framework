<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Component\ClassExtension\Exception;

class DocBlockParserEmptyDocBlockException extends DocBlockParserException
{
    public function __construct()
    {
        parent::__construct('Doc block is empty.');
    }
}
