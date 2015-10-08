<?php

namespace D3R\Monitor\Configuration;

use D3R\Monitor\Component;

class Php extends Component
{

    protected $_params = array(
            'memory_limit',
            // 'include_path',
            // 'magic_quotes_gpc',
            'error_reporting'
        );

    public function getData()
    {
        $data = array();

        foreach ($this->_params as $param)
        {
            $data[$param] = ini_get($param);
        }
        $data['sapi_name']      = php_sapi_name();
        $data['extensions']     = get_loaded_extensions();

        return $data;
    }
}
