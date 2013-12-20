<?php

namespace D3R\Metric;

class Nginx extends Base
{
    public function getData()
    {
        $status = $this->_getStatus();

        return $status;
    }

    protected function _getStatus()
    {
        /**
         * Nginx status looks like this:
         * 
         * Active connections: 1 
         * server accepts handled requests
         *  34073 34073 49556 
         * Reading: 0 Writing: 1 Waiting: 0 
         */

        if (false == ($status = file_get_contents('http://localhost:81/nginx/status')))
        {
            throw new \Exception("Error reading nginx stats", 500);
        }
        list ($active, $discard, $connections, $stats) = explode("\n", $status);

        $data               = array();
        $data['active']     = (int) preg_replace('#[^0-9]+#', '', $active);

        $connections                    = preg_split('#\s#', trim($connections));
        $data['connections_accepted']   = (int) $connections[0];
        $data['connections_handled']    = (int) $connections[1];
        $data['requests_handled']       = (int) $connections[2];

        $stats              = preg_split('#\s#', trim($stats));
        $data['reading']    = (int) $stats[1];
        $data['writing']    = (int) $stats[3];
        $data['waiting']    = (int) $stats[5];

        // $data['requests_per_second']    = (float) round($data['requests_handled'] / $data['connections_handled'], 2);

        return $data;
    }
}