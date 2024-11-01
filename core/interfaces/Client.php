<?php

namespace WarehouseSpace\Interfaces;

interface Client
{
    public function __construct($wsdlUrl, $accountKey, $warehouseNumber);
    public function call($function, $params);
}
