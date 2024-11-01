<?php

namespace WarehouseSpace\Tests;

use WarehouseSpace\Classes\Requests\MaterialBulkRequest;
use WarehouseSpace\Classes\Product;
use WarehouseSpace\Exceptions\RequestException;
use PHPUnit\Framework\TestCase;

class MaterialBulkRequestTest extends TestCase
{
    
    /**
    * @expectedException WarehouseSpace\Exceptions\RequestException
    * @expectedExceptionMessage WarehouseSpace\Classes\Requests\MaterialBulkRequest Field 'fieldThatDoesNotExistInSchema' does not exist
    */
    public function testRequestUnknownFieldInitializationThrowsException()
    {
        $request = new MaterialBulkRequest(['fieldThatDoesNotExistInSchema' => 'value',]);
    }

    /**
    * @expectedException WarehouseSpace\Exceptions\RequestException
    * @expectedExceptionMessage WarehouseSpace\Classes\Requests\MaterialBulkRequest Validation error - field 'ArticlesList' value is not array type
    */
    public function testRequestProhibitedFieldValueThrowsException()
    {
        $request = new MaterialBulkRequest(['ArticlesList' => 'a',]);
    }

    /**
    * @expectedException WarehouseSpace\Exceptions\RequestException
    * @expectedExceptionMessage MaterialBulkRequest Validation error - field 'ArticlesList' value is not object type
    */
    public function testRequestProhibitedFieldArrayValueThrowsException()
    {
        $request = new MaterialBulkRequest(['ArticlesList' => ['a'],]);
    }

    /**
    * @expectedException WarehouseSpace\Exceptions\RequestException
    * @expectedExceptionMessage WarehouseSpace\Classes\Requests\MaterialBulkRequest Validation error - field 'ArticlesList' value is not instance of WarehouseSpace\Classes\Product class
    */
    public function testRequestProhibitedFieldArrayClassValueThrowsException()
    {
        $obj = new \stdClass();
        $request = new MaterialBulkRequest(['ArticlesList' => [$obj],]);
    }

    /**
    * @expectedException WarehouseSpace\Exceptions\RequestException
    * @expectedExceptionMessage WarehouseSpace\Classes\Requests\MaterialBulkRequest Field 'ArticlesList' must be filled
    */
    public function testRequestEmptyThrowsException()
    {
        $request = new MaterialBulkRequest([]);
    }

    public function testRequestInitiliazation()
    {
        $product = new Product([
            'ProductID'     => 1,
            'Warehouse'     => 1,
            'AccountKey'    => 1,
            'Article'       => 1,
            'SellPrice'     => 1,
        ]);
        $array = ['ArticlesList' => []];
        $request = new MaterialBulkRequest($array);
        $array = ['ArticlesList' => [$product]];
        $request = new MaterialBulkRequest($array);
    }

    public function testRequestDataCorrectness()
    {
        $product = new Product([
            'ProductID'     => 1,
            'Warehouse'     => 1,
            'AccountKey'    => 1,
            'Article'       => 1,
            'SellPrice'     => 1,
        ]);
        $array = ['ArticlesList' => [$product]];
        $request = new MaterialBulkRequest($array);
        $this->assertEquals($array, $request->getData());
    }

    public function testGetRequestParams()
    {
        $product = new Product([
            'ProductID'     => 1,
            'Warehouse'     => 1,
            'AccountKey'    => 1,
            'Article'       => 1,
            'SellPrice'     => 1,
            'Description'   => 1,
        ]);
        $product2 = new Product([
            'ProductID'     => 2,
            'Warehouse'     => 1,
            'AccountKey'    => 1,
            'Article'       => 2,
            'SellPrice'     => 1,
            'Description'   => 1,
        ]);
        $array = ['ArticlesList' => [$product, $product2]];
        $request = new MaterialBulkRequest($array);
        $expected = [
            'ArticlesList' => [
                [
                    'ProductID'     => 1,
                    'Warehouse'     => 1,
                    'AccountKey'    => 1,
                    'Article'       => 1,
                    'SellPrice'     => 1,
                    'Description'   => 1,
                    'Images'        => [],
                ],
                [
                    'ProductID'     => 2,
                    'Warehouse'     => 1,
                    'AccountKey'    => 1,
                    'Article'       => 2,
                    'SellPrice'     => 1,
                    'Description'   => 1,
                    'Images'        => [],
                ],
            ],
        ];
        $this->assertEquals($expected, $request->getRequestParams());
    }
}
