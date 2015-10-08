<?php

namespace D3R\Monitor\Metric;

use D3R\Monitor\Component;

class Ram extends Component
{
    const UNIT_BYTES     = 'b';
    const UNIT_KILOBYTES = 'k';
    const UNIT_MEGABYTES = 'm';
    const UNIT_GIGABYTES = 'g';

    public function getData()
    {
        $units = $this->_request->get('units');
        if (is_null($units)) {
            $units = static::UNIT_BYTES;
        }
        if (false == ($data = file_get_contents('/proc/meminfo')))
        {
            throw new Exception("Unable to get memory info");
        }

        $data = static::_parseMeminfo($data);

        $output = array(
            "total"          => $data['MemTotal'],
            "free"           => $data['MemFree'] + $data['Buffers'] + $data['Cached'],
            "used"           => $data['MemTotal'] - ($data['MemFree'] + $data['Buffers'] + $data['Cached']),
            "perc_free"      => ($data['MemFree'] + $data['Buffers'] + $data['Cached']) / $data['MemTotal'] * 100,
            "swap_total"     => $data['SwapTotal'],
            "swap_free"      => $data['SwapFree'],
            "swap_perc_free" => $data['SwapFree'] / $data['SwapTotal'] * 100,
        );

        foreach ($output as $key => &$value) {
            if ($key == "swap_perc_free" || $key == "perc_free") { continue; }
            $value = $this->_formatNumber($value, $units);
        }

        return $output;
    }

    protected function _parseMeminfo($raw)
    {
        $raw    = explode("\n", trim($raw));
        $data   = array();
        foreach ($raw as $line)
        {
            $line   = preg_split("#\s+#", $line);
            $label  = $line[0];
            $value  = $line[1];
            $unit   = (isset($line[2]) ? $line[2] : 'b');
            $label  = preg_replace('#[^A-z]+#', '', $label);
            $data[$label] = static::_toBytes($unit, $value);
        }
        return $data;
    }

    protected function _formatNumber($number, $units)
    {
        switch ($units) {
            case static::UNIT_GIGABYTES:
                return $number / 1024 / 1024 / 1024;
                break;

            case static::UNIT_MEGABYTES:
                return $number / 1024 / 1024;
                break;

            case static::UNIT_KILOBYTES:
                return $number / 1024;
                break;

            case static::UNIT_BYTES:
            default:
                return $number;
                break;
        }
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
