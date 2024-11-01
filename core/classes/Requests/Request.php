<?php

namespace WarehouseSpace\Classes\Requests;

use WarehouseSpace\Classes\Validator;
use WarehouseSpace\Exceptions\RequestException;

abstract class Request
{
    protected $schema;
    protected $data = [];
    
    public function __construct($data)
    {
        $this->data = [];
        $success = $this->assignData($data);
    }

    protected function assignData($data)
    {
        foreach ($data as $fieldName => $fieldValue) {
            if (isset($this->schema[$fieldName])) {
                $params = isset($this->schema[$fieldName]['params']) ? $this->schema[$fieldName]['params'] : [];
                $verdict = Validator::validateField($fieldValue, $this->schema[$fieldName]['type'], $params);
                if ($verdict['result'] === true) {
                    $this->data[$fieldName] = $fieldValue;
                } else {
                    throw new RequestException(get_class($this)." Validation error - field '{$fieldName}' value is {$verdict['message']}");
                }
            } else {
                throw new RequestException(get_class($this)." Field '{$fieldName}' does not exist", 0);
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
                throw new RequestException(get_class($this)." Field '{$fieldName}' must be filled", 0);
            }
        }
        return true;
    }

    public function getData()
    {
        return $this->data;
    }

    public function getRequestParams()
    {
        $requestParams = [];
        foreach ($this->data as $fieldName => $fieldValue) {
            $method = 'transform'.ucfirst($fieldName);
            if (method_exists($this, $method)) {
                $requestParams[$fieldName] = $this->$method();
            } else {
                $requestParams[$fieldName] = $fieldValue;
            }
        }
        return $requestParams;
    }
}
