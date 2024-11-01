<?php

namespace WarehouseSpace\Classes;

use WarehouseSpace\Exceptions\ProductException;
use WarehouseSpace\Controllers\MainController;

abstract class WarehouseObject
{
    protected $schema;

    protected $data;

    public static $identifiers = [];

    protected static $defaults = [];

    public function __construct($data)
    {
        $this->data = [];
        $success = $this->assignData($data);
    }

    public function getData()
    {
        return $this->data;
    }

    public function assignData($data)
    {
        foreach ($data as $fieldName => $fieldValue) {
            if (isset($this->schema[$fieldName])) {
                $params = isset($this->schema[$fieldName]['params']) ? $this->schema[$fieldName]['params'] : [];
                $verdict = Validator::validateField($fieldValue, $this->schema[$fieldName]['type'], $params);
                if ($verdict['result'] === true) {
                    $this->data[$fieldName] = $fieldValue;
                } else {
                    throw new ProductException(get_class($this)." Validation error - field '{$fieldName}' value is {$verdict['message']}");
                }
            } else {
                throw new ProductException(get_class($this)." Field '{$fieldName}' does not exist", 0);
            }
        }

        foreach (static::$defaults as $fieldName => $fieldValue) {
            if (isset($this->schema[$fieldName]) && !isset($this->data[$fieldName])) {
                $params = isset($this->schema[$fieldName]['params']) ? $this->schema[$fieldName]['params'] : [];
                $verdict = Validator::validateField($fieldValue, $this->schema[$fieldName]['type'], $params);
                if ($verdict['result'] === true) {
                    $this->data[$fieldName] = $fieldValue;
                } else {
                    throw new ProductException(get_class($this)." Validation error - field '{$fieldName}' value is {$verdict['message']}");
                }
            }
        }
        $success = $this->validate();
        return $success;
    }

    public function validate()
    {
        // Validate all filled out fields
        foreach ($this->data as $fieldName => $value) {
            $params = isset($this->schema[$fieldName]['params']) ? $this->schema[$fieldName]['params'] : [];
            $verdict = Validator::validateField($value, $this->schema[$fieldName]['type'], $params);
            if ($verdict === false) {
                return false;
            }
        }

        // Check if all required fields are filled in
        foreach ($this->schema as $fieldName => $rules) {
            if ($rules['required'] === true && !isset($this->data[$fieldName])) {
                throw new ProductException(get_class($this)." Field '{$fieldName}' must be filled", 0);
            }
        }
        return true;
    }
}
