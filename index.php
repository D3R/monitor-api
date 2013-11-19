<?php
require 'vendor/autoload.php';

define(API_VERSION, 1);

class Json_View extends \Slim\View 
{
    public function render($status)
    {
        $app = \Slim\Slim::getInstance();

        // $status = intval($status);

        //append error bool
        // if (!$this->has('error')) {
        //     $this->set('error', false);
        // }

        //append status code
        // $this->set('status', $status);

        $body = array(
                'version' => API_VERSION,
                'data' => $this->all()
            );

        $app->response()->status($status);
        $app->response()->header('Content-Type', 'application/json');
        $app->response()->body($body);

        $app->stop();
    }
}

// class App extends \Slim\Slim
// {
//     public function view()
//     {
//         return Json_View::Factory();
//     }

//     public function render()
//     {
//         //body
//     }
// }

$app = new \Slim\Slim();
$app->view(new \Json_View());

// $app->view(new \JsonApiView());
// $app->add(new \JsonApiMiddleware());

$app->get('/metrics/load', function() use ($app) {
    $load = file_get_contents('/proc/loadavg');
    list($load, $dummy) = explode(" ", trim($load), 2);
    
    $app->render(200, array(
            'load' => $load
        )
    );
});

$app->get('/metrics/uptime', function() use ($app) {
    
    $uptime = file_get_contents('/proc/uptime');
    if (false == $uptimes)
    {
        $app->render(500, 'Unable to read uptime');
        return;
    }
    
    list($all, $idle) = explode(" ", trim($uptime), 2);
    $code = 200;
    $data = array(
        'since_boot' => $all,
        'idle' => $idle
    );

    $app->render($code, $data); 
});

$app->run();