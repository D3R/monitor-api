<?php

namespace D3R\Metric;

class Load extends Base
{
    public function getData()
    {
        $load = file_get_contents('/proc/loadavg');
        list($load1, $load5, $load15, $dummy) = explode(" ", trim($load), 4);
        
        return array(
                '1_min' => $load1,
                '5_min' => $load5,
                '15_min' => $load15,
        );
    }
}