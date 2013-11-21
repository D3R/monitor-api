<?php

namespace D3R\Metric;

class Ram extends Base
{
    public function getData()
    {
        if (false == ($data = file_get_contents('/proc/meminfo')))
        {
            throw new Exception("Unable to get memory info");
        }

        $data = static::_parseMeminfo($data);

        return array(
            "total" => $data['MemTotal'],
            "free" => $data['MemFree'] + $data['Buffers'] + $data['Cached'],
            "used" => $data['MemTotal'] - ($data['MemFree'] + $data['Buffers'] + $data['Cached']),
            "perc_free" => ($data['MemFree'] + $data['Buffers'] + $data['Cached']) / $data['MemTotal'] * 100,
            "swap_total" => $data['SwapTotal'],
            "swap_free" => $data['SwapFree'],
            "swap_perc_free" => $data['SwapFree'] / $data['SwapTotal'] * 100,
        );
    }

    protected function _parseMeminfo($raw)
    {
        $raw    = explode("\n", trim($raw));
        $data   = array();
        foreach ($raw as $line)
        {
            list($label, $value, $unit) = preg_split("#\s+#", $line);
            $label = preg_replace('#[^A-z]+#', '', $label);
            $data[$label] = static::_toBytes($unit, $value);
        }
        return $data;
    }

    protected function _toBytes($unit, $value)
    {
        switch ($unit)
        {
            case 'kB':
                return $value * 1024;
            case 'B':
                return $value;
        }
    }
}