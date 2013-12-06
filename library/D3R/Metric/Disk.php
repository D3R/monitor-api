<?php

namespace D3R\Metric;

class Disk extends Base
{
    public function getData()
    {
        if (null == ($partition = $this->_request->get('partition')))
        {
            throw new \Exception('No partition specified', 400);
        }

        if (false == ($data = $this->_df($partition)))
        {
            throw new \Exception('Invalid request', 400);
        }

        if (array_key_exists('used_perc', $data))
        {
            $data['used_perc'] = preg_replace('#[^0-9]#', '', $data['used_perc']);
        }
        
        return $data;
    }

    protected function _df($partition)
    {
        $partition = escapeshellarg($partition);
        $command = '/bin/df ' . $partition;

        $raw = array();
        $return = null;
        exec($command, $raw, $return);

        if (0 < $return)
        {
            return false;
        }

        array_shift($raw);
        $raw = array_shift($raw);
        $raw = preg_split('#\s+#', $raw);

        $data = array();
        foreach (array('filesystem', 'total', 'used', 'available', 'used_perc', 'mount') as $label)
        {
            $data[$label] = array_shift($raw);
        }
        return $data;
    }
}