<?php

declare(strict_types=1);

namespace Tests\FrameworkBundle\Unit\Component\String;

use PHPUnit\Framework\TestCase;
use Shopsys\FrameworkBundle\Component\String\HashGenerator;

class HashGeneratorTest extends TestCase
{
    public function hashLengthProvider()
    {
        return [
            [1],
            [13],
            [100],
        ];
    }

    /**
     * @dataProvider hashLengthProvider
     * @param mixed $length
     */
    public function testGenerateHash($length)
    {
        $hashGererator = new HashGenerator();

        $hash = $hashGererator->generateHash($length);

        $this->assertSame($length, strlen($hash));
    }
}
