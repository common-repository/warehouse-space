<?php

namespace WarehouseSpace\Tests;

use WarehouseSpace\Classes\Validator;
use PHPUnit\Framework\TestCase;

class ValidatorTest extends TestCase
{
    /**
     * @dataProvider stringProvider
     */
    public function testStringValidation($string, $params, $result)
    {
        $success = Validator::validateString($string, $params);
        $this->assertEquals($result, $success['result']);
    }
    public function stringProvider()
    {
        return [
            ['Abc', ['max' => 5], true],
            ['Abc', ['max' => 3, 'notEmpty' => true], true],
            ['Абв', ['max' => 3], true],
            ['Абв', ['max' => 2], false],
            ['Abc', ['max' => 2], false],
            ['Абв ', ['max' => 4], true],
            ['Abc ', ['max' => 4], true],
            [' Ab c ', ['max' => 6], true],
            [' Ab c ', ['max' => 5], false],
            ['?!@"#äöüõ-.', ['max' => 11], true],
            ['', ['notEmpty' => true], false],
        ];
    }

    /**
     * @dataProvider intProvider
     */
    public function testIntegerValidation($int, $result)
    {
        $success = Validator::validateInt($int, []);
        $this->assertEquals($result, $success['result']);
    }
    public function intProvider()
    {
        return [
            [1, true],
            ['1', false],
            [1.1, false],
            [true, false],
            ['a', false],
            [[], false],
            ['', false],
        ];
    }

    /**
     * @dataProvider decimalProvider
     */
    public function testDecimalValidation($decimal, $result)
    {
        $success = Validator::validateDecimal($decimal, []);
        $this->assertEquals($result, $success['result']);
    }
    public function decimalProvider()
    {
        return [
            [2, true],
            [true, false],
            ['1', false],
            [1.1, true],
            [1.0001, true],
            ['a', false],
            [[], false],
            ['', false],
        ];
    }

    /**
     * @dataProvider objectProvider
     */
    public function testObjectValidation($object, $params, $result)
    {
        $success = Validator::validateObject($object, $params);
        $this->assertEquals($result, $success['result']);
    }
    public function objectProvider()
    {
        $validator = new Validator();
        return [
            [$validator, ['class' => Validator::class], true],
            [$validator, ['class' => 'Validator'], false],
        ];
    }

    /**
     * @dataProvider datetimeProvider
     */
    public function testDatetimeValidation($datetime, $result)
    {
        $success = Validator::validateDatetime($datetime, []);
        $this->assertEquals($result, $success['result']);
    }
    public function datetimeProvider()
    {
        $now = new \DateTime();
        return [
            [$now->format(\DateTime::RFC3339), true],
            [true, false],
            ['1', false],
            [1.1, false],
            ['1999-01-01T02:25:25+03:00', true],
            ['1999-01-01 02:25:25+03:00', false],
            ['a', false],
            [[], false],
            ['', false],
        ];
    }

    /**
     * @dataProvider arrayProvider
     */
    public function arrayValidation($array, $params, $result)
    {
        $success = Validator::validateArray($array, []);
        $this->assertEquals($result, $success['result']);
    }
    public function arrayProvider()
    {
        $now = new \DateTime();
        return [
            [[], [], true],
            [[1, 2, '3'], [], true],
            [['1'], ['type' => 'decimal'], false],
            [['1', '123'], ['type' => 'string', 'max' => 2], false],
            [['12'], ['type' => 'string', 'max' => 1], false],
            [['12'], ['type' => 'string'], false],
            ['1', ['type' => 'string', 'max' => 1], false],
        ];
    }

    /**
     * @dataProvider fieldProvider
     */
    public function testFieldValidation($value, $type, $params, $result)
    {
        $success = Validator::validateField($value, $type, $params);
        $this->assertEquals($result, $success['result']);
    }
    public function fieldProvider()
    {
        $validator = new Validator();
        $now = new \DateTime();
        return [
            [$now->format(\DateTime::RFC3339), 'datetime', [], true],
            [1.01, 'decimal', [], true],
            ['a', 'int', [], false],
            [12, 'string', [], true],
            ['12', 'string', [], true],
            [12, 'string', ['max' => 1], false],
            [['a' => 'a'], 'array', ['type' => 'string'], true],
            [[$validator, 'a' => $validator], 'array', ['type' => 'object', 'class' => Validator::class], true],
        ];
    }
}
