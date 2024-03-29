<?php

declare(strict_types=1);

namespace Tests\FrameworkBundle\Unit\Component\Setting;

use DateTime;
use PHPUnit\Framework\TestCase;
use Shopsys\FrameworkBundle\Component\Setting\Exception\InvalidArgumentException;
use Shopsys\FrameworkBundle\Component\Setting\SettingValue;
use stdClass;

class SettingValueTest extends TestCase
{
    public function editProvider()
    {
        return [
            ['string'],
            [0],
            [0.0],
            [false],
            [true],
            [null],
        ];
    }

    public function editExceptionProvider()
    {
        return [
            [[]],
            [new stdClass()],
        ];
    }

    /**
     * @dataProvider editProvider
     * @param mixed $value
     */
    public function testEdit($value)
    {
        $settingValue = new SettingValue('name', $value, 1);
        $this->assertSame($value, $settingValue->getValue());
    }

    /**
     * @dataProvider editExceptionProvider
     * @param mixed $value
     */
    public function testEditException($value)
    {
        $this->expectException(InvalidArgumentException::class);
        new SettingValue('name', $value, 1);
    }

    public function testStoreDatetime()
    {
        $value = new DateTime('2017-01-01 12:34:56');
        $settingValue = new SettingValue('name', $value, 1);
        $this->assertEquals($value, $settingValue->getValue());
    }
}
