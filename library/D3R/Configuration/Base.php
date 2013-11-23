<?php

namespace D3R\Configuration;

abstract class Base 
{
    static public function Factory($metric)
    {
        $class = "\\D3R\\Configuration\\" . ucfirst(strtolower($metric));
        if (class_exists($class))
        {
            return new $class;
        }
        throw new \Exception("Invalid configuration {$metric}", 400);
    }

    abstract public function getData();
}