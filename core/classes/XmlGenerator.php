<?php

namespace WarehouseSpace\Classes;

use WarehouseSpace\Classes\Requests\Request;

class XmlGenerator
{
    public function arrayToXml(array $data, \SimpleXMLElement $xml)
    {
        foreach ($data as $key => $value) {
            if (is_numeric($key)) {
                if (is_array($value)) {
                    $this->arrayToXml($value, $xml);
                } else {
                    $xml->addChild('string', htmlspecialchars($value));
                }
            } else {
                if (is_array($value)) {
                    if (empty($value)) {
                        $child = $xml->addChild($key);
                        $child[0] = '';
                    } else {
                        $subnode = $xml->addChild($key);
                        $this->arrayToXml($value, $subnode);
                    }
                } else {
                    $xml->addChild($key, htmlspecialchars($value));
                }
            }
        }
    }

    public function generateFromRequest(Request $request)
    {
        $requestArray = $request->getRequestParams();
        $keys = array_keys($requestArray);
        $rootElemName = reset($keys);
        $xml = new \SimpleXMLElement('<'.$rootElemName.'/>');
        $this->arrayToXml($requestArray[$rootElemName], $xml);
        return $xml->asXML();
    }
}
