<?php

// autoload_static.php @generated by Composer

namespace Composer\Autoload;

class ComposerStaticInitdd93745b3297148a97886af9533f0913
{
    public static $prefixLengthsPsr4 = array (
        'W' => 
        array (
            'WarehouseSpace\\Tests\\' => 21,
            'WarehouseSpace\\Interfaces\\' => 26,
            'WarehouseSpace\\Exceptions\\' => 26,
            'WarehouseSpace\\Controllers\\' => 27,
            'WarehouseSpace\\Classes\\' => 23,
        ),
    );

    public static $prefixDirsPsr4 = array (
        'WarehouseSpace\\Tests\\' => 
        array (
            0 => __DIR__ . '/../..' . '/tests',
        ),
        'WarehouseSpace\\Interfaces\\' => 
        array (
            0 => __DIR__ . '/../..' . '/interfaces',
        ),
        'WarehouseSpace\\Exceptions\\' => 
        array (
            0 => __DIR__ . '/../..' . '/exceptions',
        ),
        'WarehouseSpace\\Controllers\\' => 
        array (
            0 => __DIR__ . '/../..' . '/controllers',
        ),
        'WarehouseSpace\\Classes\\' => 
        array (
            0 => __DIR__ . '/../..' . '/classes',
        ),
    );

    public static $classMap = array (
        'WarehouseSpace\\Classes\\Exporter' => __DIR__ . '/../..' . '/classes/Exporter.php',
        'WarehouseSpace\\Classes\\Logger' => __DIR__ . '/../..' . '/classes/Logger.php',
        'WarehouseSpace\\Classes\\LoggerManager' => __DIR__ . '/../..' . '/classes/LoggerManager.php',
        'WarehouseSpace\\Classes\\Order' => __DIR__ . '/../..' . '/classes/Order.php',
        'WarehouseSpace\\Classes\\OrderExporter' => __DIR__ . '/../..' . '/classes/OrderExporter.php',
        'WarehouseSpace\\Classes\\OrderProduct' => __DIR__ . '/../..' . '/classes/OrderProduct.php',
        'WarehouseSpace\\Classes\\OrderShipmentInfoImporter' => __DIR__ . '/../..' . '/classes/OrderShipmentInfoImporter.php',
        'WarehouseSpace\\Classes\\Product' => __DIR__ . '/../..' . '/classes/Product.php',
        'WarehouseSpace\\Classes\\ProductBulkExporter' => __DIR__ . '/../..' . '/classes/ProductBulkExporter.php',
        'WarehouseSpace\\Classes\\Requests\\GetOrderShipmentInfoRequest' => __DIR__ . '/../..' . '/classes/Requests/GetOrderShipmentInfoRequest.php',
        'WarehouseSpace\\Classes\\Requests\\MaterialBulkFileUploadRequest' => __DIR__ . '/../..' . '/classes/Requests/MaterialBulkFileUploadRequest.php',
        'WarehouseSpace\\Classes\\Requests\\MaterialBulkRequest' => __DIR__ . '/../..' . '/classes/Requests/MaterialBulkRequest.php',
        'WarehouseSpace\\Classes\\Requests\\OrderDetailRequest' => __DIR__ . '/../..' . '/classes/Requests/OrderDetailRequest.php',
        'WarehouseSpace\\Classes\\Requests\\RegisterStoreRequest' => __DIR__ . '/../..' . '/classes/Requests/RegisterStoreRequest.php',
        'WarehouseSpace\\Classes\\Requests\\Request' => __DIR__ . '/../..' . '/classes/Requests/Request.php',
        'WarehouseSpace\\Classes\\SoapClient' => __DIR__ . '/../..' . '/classes/SoapClient.php',
        'WarehouseSpace\\Classes\\Validator' => __DIR__ . '/../..' . '/classes/Validator.php',
        'WarehouseSpace\\Classes\\WarehouseObject' => __DIR__ . '/../..' . '/classes/WarehouseObject.php',
        'WarehouseSpace\\Classes\\XmlGenerator' => __DIR__ . '/../..' . '/classes/XmlGenerator.php',
        'WarehouseSpace\\Controllers\\MainController' => __DIR__ . '/../..' . '/controllers/MainController.php',
        'WarehouseSpace\\Exceptions\\ConfigurationException' => __DIR__ . '/../..' . '/exceptions/ConfigurationException.php',
        'WarehouseSpace\\Exceptions\\OrderException' => __DIR__ . '/../..' . '/exceptions/OrderException.php',
        'WarehouseSpace\\Exceptions\\ProductException' => __DIR__ . '/../..' . '/exceptions/ProductException.php',
        'WarehouseSpace\\Exceptions\\RequestException' => __DIR__ . '/../..' . '/exceptions/RequestException.php',
        'WarehouseSpace\\Interfaces\\Client' => __DIR__ . '/../..' . '/interfaces/Client.php',
        'WarehouseSpace\\Interfaces\\FileGenerator' => __DIR__ . '/../..' . '/interfaces/FileGenerator.php',
        'WarehouseSpace\\Interfaces\\IException' => __DIR__ . '/../..' . '/interfaces/IException.php',
        'WarehouseSpace\\Interfaces\\Logger' => __DIR__ . '/../..' . '/interfaces/Logger.php',
        'WarehouseSpace\\Tests\\MaterialBulkRequestTest' => __DIR__ . '/../..' . '/tests/MaterialBulkRequestTest.php',
        'WarehouseSpace\\Tests\\OrderExporterTest' => __DIR__ . '/../..' . '/tests/OrderExporterTest.php',
        'WarehouseSpace\\Tests\\OrderShipmentInfoImporterTest' => __DIR__ . '/../..' . '/tests/OrderShipmentInfoImporterTest.php',
        'WarehouseSpace\\Tests\\ProductBulkExporterTest' => __DIR__ . '/../..' . '/tests/ProductBulkExporterTest.php',
        'WarehouseSpace\\Tests\\ProductTest' => __DIR__ . '/../..' . '/tests/ProductTest.php',
        'WarehouseSpace\\Tests\\ValidatorTest' => __DIR__ . '/../..' . '/tests/ValidatorTest.php',
    );

    public static function getInitializer(ClassLoader $loader)
    {
        return \Closure::bind(function () use ($loader) {
            $loader->prefixLengthsPsr4 = ComposerStaticInitdd93745b3297148a97886af9533f0913::$prefixLengthsPsr4;
            $loader->prefixDirsPsr4 = ComposerStaticInitdd93745b3297148a97886af9533f0913::$prefixDirsPsr4;
            $loader->classMap = ComposerStaticInitdd93745b3297148a97886af9533f0913::$classMap;

        }, null, ClassLoader::class);
    }
}