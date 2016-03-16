<?php

namespace D3R\Monitor\Metric;

use D3R\Monitor\Component;

class Uptime extends Component
{
    public function getData()
    {
        $uptime = @file_get_contents('/proc/uptime');
        if (false == $uptime)
        {
            throw new \Exception("Unable to get uptime", 500);
        }

        list($all, $idle) = explode(" ", trim($uptime), 2);
        return array(
            'since_boot' => $all,
            'idle' => $idle
        );
    }
}
