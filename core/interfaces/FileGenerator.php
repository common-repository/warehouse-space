<?php

namespace WarehouseSpace\Interfaces;

interface FileGenerator
{
    public function createFile($name, $path);
    public function add($element);
}
