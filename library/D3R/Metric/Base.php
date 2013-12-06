<?php

namespace D3R\Metric;

abstract class Base
{
    static public function Factory($metric, $request)
    {
        $class = "\\D3R\\Metric\\" . ucfirst(strtolower($metric));
        if (class_exists($class))
        {
            $obj = new $class;
            $obj->_setRequest($request);
            return $obj;
        }
        throw new \Exception("Invalid metric {$metric}", 400);
    }

    protected $_request;

    protected function _setRequest($request)
    {
        $this->_request = $request;
    }

    abstract public function getData();
}