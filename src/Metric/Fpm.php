<?php

namespace D3R\Monitor\Metric;

use D3R\Monitor\Component;

class Fpm extends Component
{
    public function getData()
    {
        $status = file_get_contents('http://localhost:81/fpm/status?json');
        $status = (array) json_decode($status);

        $data = array();
        foreach ($status as $key => $value)
        {
            $key = preg_replace('#\s+#', '_', $key);
            $data[$key] = $value;
        }
        return $data;
    }
}
