<?php

namespace D3R\Monitor\Metric;

class Uptime extends Base
{
    public function getData()
    {
        $uptime = file_get_contents('/proc/uptime');
        if (false == $uptime)
        {
            throw new Exception("Unable to get uptime");
        }

        list($all, $idle) = explode(" ", trim($uptime), 2);
        return array(
            'since_boot' => $all,
            'idle' => $idle
        );
    }
}
