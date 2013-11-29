<?php

namespace D3R\Metric;

class Fpm extends Base
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