<?php

namespace WarehouseSpace\Classes;

use WarehouseSpace\Classes\Logger;
use WarehouseSpace\Controllers\MainController;
use WarehouseSpace\Interfaces\Client;

class SoapClient implements Client
{
    private $accountKey;
    private $warehouseNumber;
    private $soapClient;

    public function __construct($wsdlUrl, $accountKey, $warehouseNumber, $debug = false)
    {
        $this->accountKey = $accountKey;
        $this->warehouseNumber = $warehouseNumber;
        $this->debug = $debug;
        try {
            $this->soapClient = new \SoapClient(
                $wsdlUrl,
                array(
                    'trace' => 1,
                    'exceptions' => true,
                    'cache_wsdl' => $debug ? WSDL_CACHE_NONE : WSDL_CACHE_MEMORY,
                    'soap_version' => SOAP_1_1,
                    'keep_alive' => true,
                    'compression' => SOAP_COMPRESSION_GZIP,
                )
            );
        } catch (SoapFault $fault) {
            MainController::getLogger()->log('Soap client error: ' . $fault->getMessage(), 0);
        }
    }

    public function call($function, $params)
    {
        $function = ucfirst($function);
        try {
            $result = $this->soapClient->$function($params);
            if ($this->debug) {
                MainController::getLogger()->log("REQUEST:\n" . $this->soapClient->__getLastRequest() . "\n", 4);
                MainController::getLogger()->log(print_r($result, 1), 4);
            }
            return $result;
        } catch (\Exception $exception) {
            MainController::getLogger()->log('Error while making soap call: '.$function."\nREQUEST:". $this->soapClient->__getLastRequest() ."\nERROR:{$exception}\nINNER EXCEPTION ".$exception->detail->ExceptionDetail->InnerException->Message."\n", 0);
            return false;
        }
    }

    public function getFunctions()
    {
        return $this->soapClient->__getFunctions();
    }

    public function getTypes()
    {
        return $this->soapClient->__getTypes();
    }
}
