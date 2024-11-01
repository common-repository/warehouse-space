<?php

namespace WarehouseSpace\Classes;

class LoggerManager
{
    public $loggers;

    public function add($logger)
    {
        if (is_object($logger) && $logger instanceof \WarehouseSpace\Interfaces\Logger) {
            $this->loggers[] = $logger;
        } elseif (is_array($logger)) {
            foreach ($logger as $l) {
                $this->add($l);
            }
        } else {
            $obj = new $logger();
            if ($obj instanceof \WarehouseSpace\Interfaces\Logger) {
                $this->loggers[] = $obj;
            } else {
                return false;
            }
        }
        return true;
    }

    public function log($msg, $priority)
    {
        foreach ($this->loggers as $logger) {
            $logger->log($msg, $priority);
        }
    }
}
