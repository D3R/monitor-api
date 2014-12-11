<?php

namespace D3R\Monitor\Configuration;

class Packages extends Base
{
    protected $packages = array(
            'nginx',
            'apache2',
            'mysql-server',
            'elasticsearch',
            'php5',
            'redis-server',
            'memcached',
        );

    public function getData()
    {
        $data = [];
        foreach ($this->packages as $package) {
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
            return 'unknown';
        }

        foreach ($output as $line) {
            preg_match('#\s*Installed:([^\n]+)#', $line, $matches);
            if (isset($matches[1])) {
                $version = trim($matches[1]);
                if ('(none)' == $version) {
                    return 'not installed';
                }
                return $version;
            }
        }

        return 'unknown';
    }
}
