<?php

namespace WarehouseSpace\Classes;

class Validator
{
    public static function validateField($value, $type, $params)
    {
        $function = 'validate'.ucfirst($type);
        return self::$function($value, $params);
    }

    public static function validateInt($value, $params)
    {
        if (is_int($value)) {
            return ['result' => true];
        } else {
            return [
                'result'    => false,
                'message'   => 'not integer type',
            ];
        }
    }

    public static function validateBool($value, $params)
    {
        if (is_bool($value)) {
            return ['result' => true];
        } else {
            return [
                'result'    => false,
                'message'   => 'not boolean type',
            ];
        }
    }

    public static function validateString($value, $params)
    {
        if (isset($params['max'])) {
            if (iconv_strlen($value) > $params['max']) {
                return [
                    'result'    => false,
                    'message'   => 'too long',
                ];
            }
        }

        if (isset($params['notEmpty']) && $params['notEmpty'] === true && strlen($value) === 0) {
            return [
                'result'    => false,
                'message'   => 'empty',
            ];
        }
        
        return [
            'result' => true
        ];
    }

    public static function validateDatetime($value, $params)
    {
        $format = \DateTime::RFC3339;
        if (is_string($value)) {
            $d = \DateTime::createFromFormat($format, $value);
            if ($d && $d->format($format) == $value) {
                return ['result' => true];
            }
        }
        return [
            'result'    => false,
            'message'   => 'in wrong datetime format',
        ];
    }

    public static function validateDecimal($value, $params)
    {
        if (is_float($value) || is_int($value)) {
            return ['result' => true];
        } else {
            return [
                'result'    => false,
                'message'   => 'not floating point type',
            ];
        }
    }

    public static function validateObject($value, $params)
    {
        if (is_object($value)) {
            if (isset($params['class']) && !$value instanceof $params['class']) {
                return [
                    'result'    => false,
                    'message'   => "not instance of {$params['class']} class",
                ];
            }
            return ['result' => true];
        } else {
            return [
                'result'    => false,
                'message'   => 'not object type',
            ];
        }
    }

    public static function validateArray($value, $params)
    {
        if (is_array($value)) {
            if (isset($params['type'])) {
                foreach ($value as $v) {
                    $success = self::validateField($v, $params['type'], $params);
                    if ($success['result'] === false) {
                        return $success;
                    }
                }
            }
            return ['result' => true];
        } else {
            return [
                'result'    => false,
                'message'   => 'not array type',
            ];
        }
    }
}
