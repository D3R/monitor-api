<?php

namespace D3R\Monitor\Metric;

use D3R\Monitor\Component;

class Fpm extends Component
{
    public function getData()
    {
        $status = @file_get_contents('http://localhost:81/fpm/status?json');
        if (false === $status) {
            throw new \Exception('Unable to get status data', 500);
        }
        $status = (array) json_decode($status);
        if (false === $status) {
            throw new \Exception('Unable to parse status data', 500);
        }

        $data = array();
        foreach ($status as $key => $value)
        {
            $key = preg_replace('#\s+#', '_', $key);
            $data[$key] = $value;
        }
        return $data;
    }
}
