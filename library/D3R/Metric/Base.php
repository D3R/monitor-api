<?php

namespace D3R\Metric;

abstract class Base
{
    static public function Factory($metric)
    {
        $class = "\\D3R\\Metric\\" . ucfirst(strtolower($metric));
        if (class_exists($class))
        {
            return new $class;
        }
        throw new \Exception("Invalid metric {$metric}", 400);
    }

    abstract public function getData();
}