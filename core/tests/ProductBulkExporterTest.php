<?php

namespace WarehouseSpace\Tests;

use WarehouseSpace\Classes\ProductBulkExporter;
use WarehouseSpace\Classes\Product;
use WarehouseSpace\Exceptions\ProductException;
use PHPUnit\Framework\TestCase;

class ProductBulkExporterTest extends TestCase
{
    /**
    * @expectedException WarehouseSpace\Exceptions\ConfigurationException
    */
    public function testForConfigurationException()
    {
        $this->client = $this->createMock('\WarehouseSpace\Classes\SoapClient');
        $this->exporter = new ProductBulkExporter($this->client, []);
    }

    public function testSetProductsBlockingConfig()
    {
        $this->client = $this->createMock('WarehouseSpace\Classes\SoapClient');
        $config = [];
        foreach (Product::$identifiers as $name => $identifier) {
            $config[$identifier] = 1;
        }
        $this->exporter = new ProductBulkExporter($this->client, $config);
        $this->exporter->setProducts($this->exampleProductsData());
        $hit = $this->exporter->getHitProductIds();
        sort($hit);
        $this->assertEquals([2, 4], $hit);

        $config['productImportNotBlocking'] = true;
        $this->exporter = new ProductBulkExporter($this->client, $config);
        $this->exporter->setProducts($this->exampleProductsData());
        $hit = $this->exporter->getHitProductIds();
        sort($hit);
        $this->assertEquals([2, 4], $hit);

        $config['productImportNotBlocking'] = false;
        $this->exporter = new ProductBulkExporter($this->client, $config);
        $this->exporter->setProducts($this->exampleProductsData());
        $hit = $this->exporter->getHitProductIds();
        sort($hit);
        $this->assertEquals([], $hit);
        $missed = $this->exporter->getMissedProductIds();
        sort($missed);
        $this->assertEquals([1], $missed);
    }
    
    public function testMissedAndHitProductsCorrectness()
    {
        $this->client = $this->createMock('WarehouseSpace\Classes\SoapClient');
        $config = [];
        foreach (Product::$identifiers as $name => $identifier) {
            $config[$identifier] = 1;
        }
        $this->exporter = new ProductBulkExporter($this->client, $config);
        $this->exporter->setProducts($this->exampleProductsData());

        $ids = $this->exporter->getMissedProductIds();
        sort($ids);
        $this->assertEquals([1, 3], $ids);
        $ids = $this->exporter->getHitProductIds();
        $this->assertEquals([2, 4], $ids);
    }
    
    public function testExport()
    {
        $this->client = $this->createMock('WarehouseSpace\Classes\SoapClient');
        $config = [];
        foreach (Product::$identifiers as $name => $identifier) {
            $config[$identifier] = 1;
        }
        $this->exporter = new ProductBulkExporter($this->client, $config);
        $this->exporter->setProducts($this->exampleProductsData());
        $this->exporter->export();
    }

    protected function exampleProductsData()
    {
        $date = new \DateTime();
        return [
            [
                'ProductID'     => 1,
                'id'            => 1,
                'Article'       => 2,
                'Description'   => 3,
                'UOM'           => 'each',
                'BuyPrice'      => 4,
                'Supplier'      => 6,
                'BuyPrice'      => 7,
                'TimeStamp'     => $date->format(\DateTime::RFC3339),
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
            ],
            [
                'ProductID'     => 1,
                'id'            => 2,
                'Article'       => 3,
                'AccountKey'    => 8,
                'Warehouse'     => 8,
                'SellPrice'     => 5,
            ],
            [
                'id'            => 3,
            ],
            [
                'ProductID'     => 1,
                'id'            => 4,
                'Article'       => 3,
                'SellPrice'     => 5,
            ],
        ];
    }
}
