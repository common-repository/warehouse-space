<?php

namespace WarehouseSpace\Tests;

use WarehouseSpace\Classes\Product;
use WarehouseSpace\Exceptions\ProductException;
use PHPUnit\Framework\TestCase;

class ProductTest extends TestCase
{
    /**
    * @expectedException WarehouseSpace\Exceptions\ProductException
    * @expectedExceptionMessage Product Field 'fieldThatDoesNotExistInSchema' does not exist
    */
    public function testProductUnknownFieldInitializationThrowsException()
    {
        $product = new Product(['fieldThatDoesNotExistInSchema' => 'value',]);
    }

    /**
    * @expectedException WarehouseSpace\Exceptions\ProductException
    * @expectedExceptionMessage Product Validation error - field 'BuyPrice' value is not floating point type
    */
    public function testProductProhibitedFieldValueThrowsException()
    {
        $product = new Product(['BuyPrice' => 'a',]);
    }

    /**
    * @expectedException WarehouseSpace\Exceptions\ProductException
    * @expectedExceptionMessage Product Field 'Warehouse' must be filled
    */
    public function testProductRequiredFieldsNotFilledThrowsException()
    {
        $product = new Product([
            'ProductID'     => 1,
            'Article'       => 1,
            'Description'   => 1,
            'UOM'           => 'each',
            'BuyPrice'      => 1,
            'SellPrice'     => 1,
            'Supplier'      => 1,
            'AccountKey'    => 1,
        ]);
    }

    /**
    * @expectedException WarehouseSpace\Exceptions\ProductException
    * @expectedExceptionMessage Product Field 'AccountKey' must be filled
    */
    public function testProductEmptyThrowsException()
    {
        $product = new Product([]);
    }

    public function testProductInitiliazation()
    {
        $date = new \DateTime();
        $product = new Product([
            'ProductID'     => 1,
            'Warehouse'     => 1,
            'Article'       => 1,
            'Description'   => 1,
            'UOM'           => 'each',
            'BuyPrice'      => 1,
            'SellPrice'     => 1,
            'Supplier'      => 1,
            'TimeStamp'     => $date->format(\DateTime::RFC3339),
            'AccountKey'    => 1,
            'ErpTimeStamp'  => $date->format(\DateTime::RFC3339),
            'Manufacturer'  => 1,
            'MinQuantity'   => 1,
            'ItemWeight'    => 1,
            'ItemHeight'    => 1,
            'ItemWidth'     => 1,
            'ItemDepth'     => 1,
            'WeightCat'     => 1,
            'Model'         => 1,
            'Category'      => 1,
        ]);
        $product = new Product([
            'ProductID'     => 1,
            'Warehouse'     => 1,
            'AccountKey'    => 1,
            'Article'       => 1,
            'SellPrice'     => 1,
        ]);
    }

    public function testDefaults()
    {
        $data = [
            'ProductID'     => 1,
            'Warehouse'     => 1,
            'AccountKey'    => 1,
            'Article'       => 1,
            'SellPrice'     => 1,
        ];
        $product = new Product($data);
        $data['Description'] = '';
        $data['Images'] = [];
        $this->assertEquals($data, $product->getData());
    }

    public function testProductDataCorrectness()
    {
        $date = new \DateTime();
        $data = [
            'ProductID'     => 1,
            'Warehouse'     => 1,
            'Article'       => 2,
            'Description'   => 3,
            'UOM'           => 'each',
            'BuyPrice'      => 4,
            'SellPrice'     => 5,
            'Supplier'      => 6,
            'BuyPrice'      => 7,
            'TimeStamp'     => $date->format(\DateTime::RFC3339),
            'AccountKey'    => 8,
            'ErpTimeStamp'  => $date->format(\DateTime::RFC3339),
            'Manufacturer'  => 9,
            'MinQuantity'   => 1,
            'ItemWeight'    => 1,
            'ItemHeight'    => 1,
            'ItemWidth'     => 1,
            'ItemDepth'     => 1,
            'WeightCat'     => 1,
            'Model'         => 1,
            'Category'      => 1,
            'Images'        => ['http://a.a.a', 'http://b.b.b'],
        ];
        $product = new Product($data);
        $this->assertEquals($data, $product->getData());
    }

    /**
    * @expectedException WarehouseSpace\Exceptions\ProductException
    */
    public function testProductDataUpdate()
    {
        $date = new \DateTime();
        $data = [
            'ProductID'     => 1,
            'Warehouse'     => 1,
            'Article'       => 2,
            'Description'   => 3,
            'UOM'           => 'each',
            'BuyPrice'      => 4,
            'SellPrice'     => 5,
            'Supplier'      => 6,
            'BuyPrice'      => 7,
            'TimeStamp'     => $date->format(\DateTime::RFC3339),
            'AccountKey'    => 8,
            'ErpTimeStamp'  => $date->format(\DateTime::RFC3339),
            'Manufacturer'  => 9,
            'MinQty'        => 1,
            'ItemWeight'    => 1,
            'ItemHeight'    => 1,
            'ItemWidth'     => 1,
            'ItemDepth'     => 1,
            'WeightCat'     => 1,
            'Model'         => 1,
            'Category'      => 1,
        ];
        $product = new Product($data);
        $product->assignData(['Supplier' => 10]);
        $data['Supplier'] = 10;
        $this->assertEquals($data, $product->getData());
        $product->assignData(['BuyPrice' => 'a']);
    }
}
