<?php

namespace D3R\Monitor\Metric;

use D3R\Monitor\Component;

class Load extends Component
{
    public function getData()
    {
        $load = @file_get_contents('/proc/loadavg');
        if (false === $load) {
            throw new \Exception('Unable to get load stats', 500);
        }
        list($load1, $load5, $load15, $dummy) = explode(" ", trim($load), 4);

        return array(
                '1_min' => $load1,
                '5_min' => $load5,
                '15_min' => $load15,
        );
    }
}
