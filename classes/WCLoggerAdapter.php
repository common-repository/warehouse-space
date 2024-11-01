<?php

namespace WarehouseSpace\WP\Classes;

use WarehouseSpace\Interfaces\Logger;

class WCLoggerAdapter implements Logger
{
    private $logger;
    private $context;
    private $priorities;

    public function __construct()
    {
        $this->priorities = [
            0 => 'critical',
            1 => 'error',
            2 => 'warning',
            3 => 'info',
            4 => 'debug',
        ];
        $this->context = ['source' => 'warehousespace'];
        $this->logger = wc_get_logger();
    }

    public function log($msg, $priority)
    {
        $this->logger->log($this->priorities[$priority], $msg, $this->context);
    }
}
