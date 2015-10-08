<?php

namespace D3R\Monitor;

abstract class Component
{
    static public function Factory($module, $component, $request)
    {
        $module    = ucfirst(strtolower($module));
        $component = ucfirst(strtolower($component));
        $class     = "\\D3R\\Monitor\\{$module}\\{$component}";
        if (class_exists($class))
        {
            $obj = new $class;
            $obj->_setRequest($request);
            return $obj;
        }
        throw new \Exception("Invalid component {$module}\\{$component}", 400);
    }

    protected $_request;

    protected function _setRequest($request)
    {
        $this->_request = $request;
    }

    abstract public function getData();
}
