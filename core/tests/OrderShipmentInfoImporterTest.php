<?php

namespace WarehouseSpace\Tests;

use PHPUnit\Framework\TestCase;
use WarehouseSpace\Classes\OrderShipmentInfoImporter;
use WarehouseSpace\Controllers\MainController;

class OrderShipmentInfoImporterTest extends TestCase
{
    public function setUp()
    {
        $this->client = $this->createMock('\WarehouseSpace\Classes\SoapClient');
        $this->importer = new OrderShipmentInfoImporter($this->client, [MainController::ACCOUNTKEY => 1]);
    }

    /**
    * @expectedException WarehouseSpace\Exceptions\OrderException
    */
    public function testNonIntOrderIds()
    {
        $this->importer->setOrderIds([1, 'a',]);
    }

    public function testIntOrderIds()
    {
        $this->importer->setOrderIds([1, '1', 21,]);
    }

    public function testShipmentInfoSoapCallArray()
    {
        $this->client->expects($this->once())
             ->method('call')
             ->with($this->anything(), $this->equalTo(['AccountKey' => 1, 'ListInvNumbers' => [1, '1', 21,]]));
        $this->importer->setOrderIds([1, '1', 21,]);
        $this->importer->export();
    }
}
