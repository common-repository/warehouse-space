<?php

namespace WarehouseSpace\Tests;

use PHPUnit\Framework\TestCase;
use WarehouseSpace\Classes\Order;
use WarehouseSpace\Classes\OrderExporter;
use WarehouseSpace\Classes\OrderProduct;
use WarehouseSpace\Classes\Requests\OrderDetailRequest;
use WarehouseSpace\Exceptions\RequestException;

class OrderExporterTest extends TestCase
{
    /**
    * @expectedException WarehouseSpace\Exceptions\ConfigurationException
    */
    public function testForConfigurationException()
    {
        $this->client = $this->createMock('\WarehouseSpace\Classes\SoapClient');
        $this->exporter = new OrderExporter($this->client, []);
    }
    
    public function testExport()
    {
        $this->client = $this->createMock('WarehouseSpace\Classes\SoapClient');
        $config = [];
        foreach (Order::$identifiers as $name => $identifier) {
            $config[$identifier] = 1;
        }
        $this->exporter = new OrderExporter($this->client, $config);
        $this->exporter->setOrder($this->exampleOrderData());
        $this->exporter->setProducts($this->exampleProductData());
        $this->exporter->export();
    }
    
    /**
    * @expectedException WarehouseSpace\Exceptions\OrderException
    */
    public function testThatExportWillNotRunWithErrors()
    {
        $this->client = $this->createMock('WarehouseSpace\Classes\SoapClient');
        $config = [];
        foreach (Order::$identifiers as $name => $identifier) {
            $config[$identifier] = 1;
        }
        $this->exporter = new OrderExporter($this->client, $config);
        $order = $this->exampleOrderData();
        unset($order['id']);
        $this->exporter->setOrder($order);
        $this->exporter->setProducts($this->exampleProductData());
        $this->exporter->export();
    }

    /**
    * @expectedException WarehouseSpace\Exceptions\OrderException
    */
    public function testThatExportDoesNotRunWithoutProducts()
    {
        $this->client = $this->createMock('WarehouseSpace\Classes\SoapClient');
        $config = [];
        foreach (Order::$identifiers as $name => $identifier) {
            $config[$identifier] = 1;
        }
        $this->exporter = new OrderExporter($this->client, $config);
        $order = $this->exampleOrderData();
        unset($order['id']);
        $this->exporter->setOrder($this->exampleOrderData());
        $this->exporter->export();
    }

    /**
    * @expectedException WarehouseSpace\Exceptions\OrderException
    */
    public function testThatExportDoesNotRunWithoutOrder()
    {
        $this->client = $this->createMock('WarehouseSpace\Classes\SoapClient');
        $config = [];
        foreach (Order::$identifiers as $name => $identifier) {
            $config[$identifier] = 1;
        }
        $this->exporter = new OrderExporter($this->client, $config);
        $order = $this->exampleOrderData();
        unset($order['id']);
        $this->exporter->setProducts($this->exampleProductData());
        $this->exporter->export();
    }

    protected function exampleProductData()
    {
        return [[
            'id'        => 1,
            'ArticleDescr'     => 1,
            'Quantity'            => 1,
            'ProductID'       => 2,
            'Article'   => 3,
        ]];
    }

    protected function exampleOrderData()
    {
        $date = new \DateTime();
        return [
            'id'        => 1,
            'InvNumber'     => 1,
            'Customer'            => 1,
            'AccountKey'       => 2,
            'InvReference'   => 3,
            'InvStatus'           => 1,
            'InvDate'      => $date->format(\DateTime::RFC3339),
            'InvDueDate'      => $date->format(\DateTime::RFC3339),
            'InvTotal'      => 7,
            'InvAmountDue'     => 1,
            'ErpTimeStamp'  => $date->format(\DateTime::RFC3339),
            'DeliverAddress'  => 9,
            'DeliveryPostCodeZIP'   => 1,
            'Country'    => 1,
            'CountryCode'    => 1,
            'StateOrProvinceCode'     => 1,
            'City'     => 1,
            'EmailAddress'     => 1,
            'PartnerKey'         => '',
            'ContactPersonName'      => 1,
            'ContactPersonPhone'        => 2,
            'Shipper'        => 2,
            'Comments'        => 2,
            'PaymentMethod'        => 2,
            'PaymentDescription'        => 2,
            'OrderTotalWeight'        => 2,
            'OrderType'        => 2,
            'InvoiceID'        => 2,
            'ShortCode'        => 2,
        ];
    }
}
