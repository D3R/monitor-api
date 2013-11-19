<?php
require 'vendor/autoload.php';

define("API_VERSION", 1);

class Json_View extends \Slim\View 
{
    public function render($status)
    {
        $app = \Slim\Slim::getInstance();

        $this->data->remove('flash');

        $body = array(
                'version'   => API_VERSION,
                'status'    => intval($status),
                'data'      => $this->all()
            );

        $app->response()->status($status);
        $app->response()->header('Content-Type', 'application/json');
        $app->response()->body(json_encode($body));

        $app->stop();
    }
}

abstract class Metric
{
    static public function Factory($metric)
    {
        $class = "Metric_" . ucfirst(strtolower($metric));
        if (class_exists($class))
        {
            return new $class;
        }
        throw new Exception("Metric {$metric} not supported", 400);
    }

    abstract public function getData($param = false);
}

class Metric_Load extends Metric
{
    public function getData($param = false)
    {
        $load = file_get_contents('/proc/loadavg');
        list($load1, $load5, $load15, $dummy) = explode(" ", trim($load), 4);
        
        return array(
                '1_min' => $load1,
                '5_min' => $load5,
                '15_min' => $load15,
        );
    }
}

class Metric_Uptime extends Metric
{
    public function getData($param = false)
    {
        $uptime = file_get_contents('/proc/uptime');
        if (false == $uptime)
        {
            throw new Exception("Unable to get uptime");
        }
        
        list($all, $idle) = explode(" ", trim($uptime), 2);
        return array(
            'since_boot' => $all,
            'idle' => $idle
        );
    }
}

class Metric_Disk extends Metric
{
    public function getData($param = false)
    {
        return array(
                'disk' => $param,
                'free' => 100
            );
    }
}

$app = new \Slim\Slim(array(
        'view'      => new \Json_View(),
        'debug'     => false
    )
);

$app->error(function (\Exception $ex) use ($app) {
    $app->render($ex->getCode(), array($ex->getMessage()));
});

$app->notFound(function () use ($app) {
    $app->render(404, array("Bad request"));
});

$app->get('/metrics/:metric(/:param)', function($metric, $param = false) use ($app) {
    $obj = \Metric::Factory($metric);
    $app->render(200, $obj->getData($param));
});

$app->run();