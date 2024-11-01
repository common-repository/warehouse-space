<?php

namespace WarehouseSpace\Classes;

use WarehouseSpace\Controllers\MainController;
use WarehouseSpace\Exceptions\ConfigurationException;
use WarehouseSpace\Interfaces\Client;

abstract class Exporter
{
    protected static $syncMethod;
    protected $config;
    protected $client;
    protected $errors = [];
    protected static $identifiers = [
        MainController::WAREHOUSE,
        MainController::ACCOUNTKEY,
    ];
    public function __construct(Client $client, $config)
    {
        if ($this->checkConfig($config) === false) {
            throw new ConfigurationException('Account key or warehouse number are not set', 0);
        }

        $this->client = $client;
        $this->config = $config;
        return false;
    }

    protected function checkConfig($config)
    {
        if (!is_array($config)) {
            return false;
        }

        foreach (static::$identifiers as $identifier) {
            if (!isset($config[$identifier])) {
                return false;
            }
        }
        return true;
    }

    abstract public function export();

    public function getErrors()
    {
        return $this->errors;
    }
}
