<?php

namespace D3R\Monitor\Configuration;

use D3R\Monitor\Component;

class Packages extends Component
{
    protected $packages = array(
            'nginx',
            'apache2',
            'mysql-server-5.6',
            'elasticsearch',
            'php5-fpm',
            'redis-server',
        );

    public function getData()
    {
        $data     = [];
        $packages = [];
        if (null == ($package = $this->_request->get('package'))) {
            $packages = $this->packages;
        } else {
            $packages[] = $package;
        }

        sort($packages);
        foreach ($packages as $package) {
            $data[$package] = $this->getInstalledVersion($package);
        }

        return $data;
    }

    protected function getInstalledVersion($package)
    {
        $cmd    = "/usr/bin/apt-cache policy $package";
        $output = [];
        exec($cmd, $output, $return);

        if (0 < $return) {
            return 'error';
        }

        foreach ($output as $line) {
            preg_match('#\s*Installed:([^\n]+)#', $line, $matches);
            if (isset($matches[1])) {
                $version = trim($matches[1]);
                if ('(none)' == $version) {
                    return 'none';
                }
                return $version;
            }
        }

        return 'none';
    }
}
