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
        throw new \Exception("Metric '{$metric}' not supported", 400);
    }

    // static public function Available()
    // {
    //     return array(
    //             'load',
    //             'uptime',
    //             'disk'
    //         );
    // }

    abstract public function getData();
}