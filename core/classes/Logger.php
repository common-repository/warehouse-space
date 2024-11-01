<?php

namespace WarehouseSpace\Classes;

class Logger implements \WarehouseSpace\Interfaces\Logger
{
    public function log($str, $priority)
    {
        //echo "Priority: {$priority} Message: {$str}";
        error_log("Priority: {$priority} Message: {$str}");
    }
}
