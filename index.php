<?php
require 'vendor/autoload.php';
require 'library/autoload.php';

define("API_VERSION", 1);

$app = new \Slim\Slim(array(
        'view'      => new \D3R\View\Json(),
        'debug'     => false
    )
);

$app->error(function (\Exception $ex) use ($app) {
    $app->render($ex->getCode(), array($ex->getMessage()));
});

$app->notFound(function () use ($app) {
    $app->render(404, array("Bad request"));
});

$app->get('/metric/:metric', function($metric, $param = false) use ($app) {
    $obj = \D3R\Metric\Base::Factory($metric);
    $app->render(200, $obj->getData($param));
});

$app->get('/configuration/:key', function($key, $param = false) use ($app) {
    $obj = \D3R\Configuration\Base::Factory($key);
    $app->render(200, $obj->getData($param));
});

$app->run();