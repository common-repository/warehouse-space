<?php

namespace WarehouseSpace\Controllers;

use WarehouseSpace\Classes\LoggerManager;
use WarehouseSpace\Classes\OrderExporter;
use WarehouseSpace\Classes\OrderShipmentInfoImporter;
use WarehouseSpace\Classes\ProductBulkExporter;
use WarehouseSpace\Classes\Requests\RegisterStoreRequest;
use WarehouseSpace\Classes\SoapClient;
use WarehouseSpace\Exceptions\ConfigurationException;

class MainController
{
    protected static $logger;
    const WAREHOUSE     = 'warehouse';
    const ACCOUNTKEY    = 'accountkey';

    protected $config;
    protected $debug;
    protected $rootDir;

    public function __construct($config)
    {
        if (!isset($config[self::ACCOUNTKEY]) || !isset($config[self::WAREHOUSE]) || strlen($config[self::ACCOUNTKEY]) === 0 || strlen($config[self::WAREHOUSE]) === 0) {
            throw new ConfigurationException('Account key or warehouse number are not set', 0);
        }
        $this->rootDir = dirname(__FILE__).'/../';
        $defaultConfig = include($this->rootDir.'config.php');
        $this->config = array_merge($config, $defaultConfig);

        static::$logger = new LoggerManager();
        if (isset($this->config['logger'])) {
            static::$logger->add($this->config['logger']);
        } else {
            static::$logger->add('\WarehouseSpace\Classes\Logger');
        }

        if (isset($this->config['debug']) && ($this->config['debug'] === '1' || $this->config['debug'] === true)) {
            $this->debug = true;
        } else {
            $this->debug = false;
        }
    }

    public static function getLogger()
    {
        return static::$logger;
    }

    public function getAccountkey()
    {
        return $this->config['accountkey'];
    }

    public function getWarehouse()
    {
        return $this->config['warehouse'];
    }

    public function exportProducts($products)
    {
        $soapClient = new SoapClient($this->config['wsdl'], $this->config[self::ACCOUNTKEY], $this->config[self::WAREHOUSE], $this->debug);
        $exporter = new ProductBulkExporter($soapClient, $this->config);
        $exporter->setProducts($products);
        try {
            $success = $exporter->export();
        } catch (\Exception $e) {
            $success = false;
            $error = 'Export error: '.$e;
            static::$logger->log($error, 0);
        }
        $hitIds = $exporter->getHitProductIds();
        $missedIds = $exporter->getMissedProductIds();
        $errors = $exporter->getErrors();
        $statistics = [
            'hit'       => $hitIds,
            'missed'    => $missedIds,
            'errors'    => $errors,
        ];
        if ($success) {
            return [
                'result'    => true,
                'data'      => $statistics,
            ];
        } else {
            return [
                'result'    => false,
                'data'      => $statistics,
            ];
        }
    }

    public function exportProductsBulk($products)
    {
        $soapClient = new SoapClient($this->config['wsdl2'], $this->config[self::ACCOUNTKEY], $this->config[self::WAREHOUSE], $this->debug);
        $exporter = new ProductBulkExporter($soapClient, $this->config);
        $exporter->setProducts($products);
        try {
            $success = $exporter->exportByFileUpload($this->rootDir.'tmp');
        } catch (\Exception $e) {
            $success = false;
            $error = 'Export error: '.$e;
            static::$logger->log($error, 0);
        }
        $hitIds = $exporter->getHitProductIds();
        $missedIds = $exporter->getMissedProductIds();
        $errors = $exporter->getErrors();
        $statistics = [
            'hit'       => $hitIds,
            'missed'    => $missedIds,
            'errors'    => $errors,
        ];
        if ($success) {
            return [
                'result'    => true,
                'data'      => $statistics,
            ];
        } else {
            return [
                'result'    => false,
                'data'      => $statistics,
            ];
        }
    }

    public function exportOrder($products, $order)
    {
        $soapClient = new SoapClient($this->config['wsdl'], $this->config[self::ACCOUNTKEY], $this->config[self::WAREHOUSE], $this->debug);
        $exporter = new OrderExporter($soapClient, $this->config);
        $exporter->setProducts($products);
        $exporter->setOrder($order);
        try {
            $success = $exporter->export();
        } catch (\Exception $e) {
            $success = false;
            $error = 'Export error: '.$e;
            static::$logger->log($error, 0);
        }
        $errors = $exporter->getErrors();
        $statistics = [
            'errors'    => $errors,
        ];

        return [
            'result'    => $success,
            'data'      => $statistics,
        ];
    }

    public function importOrderStatuses($orderIds)
    {
        $soapClient = new SoapClient($this->config['wsdl'], $this->config[self::ACCOUNTKEY], $this->config[self::WAREHOUSE], $this->debug);
        $exporter = new OrderShipmentInfoImporter($soapClient, $this->config);
        $exporter->setOrderIds($orderIds);
        
        try {
            $success = $exporter->export();
        } catch (\Exception $e) {
            $success = false;
            $error = 'Export error: '.$e;
            static::$logger->log($error, 0);
        }
        $errors = $exporter->getErrors();
        $statistics = [
            'errors'    => $errors,
        ];

        return [
            'result'    => $success,
            'data'      => $statistics,
        ];
    }

    public function registerStore($storeInfo)
    {
        $soapClient = new SoapClient($this->config['wsdl'], $this->config[self::ACCOUNTKEY], $this->config[self::WAREHOUSE], $this->debug);
        if (!isset($storeInfo['AccountKey'])) {
            $storeInfo['AccountKey'] = $this->config[static::ACCOUNTKEY];
        }
        if (!isset($storeInfo['Warehouse'])) {
            $storeInfo['Warehouse'] = $this->config[static::WAREHOUSE];
        }
        $request = new RegisterStoreRequest($storeInfo);
        try {
            $response = $soapClient->call('RegisterStore', $request->getRequestParams());
            if (is_object($response)) {
                $resultVariable = 'RegisterStoreResult';
                $response = $response->$resultVariable;
                return $response;
            } else {
                return false;
            }
        } catch (\Exception $e) {
            $this->errors[] = "Error during request build: {$e}";
            return false;
        }
    }
}
