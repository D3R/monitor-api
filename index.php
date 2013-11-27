<?php
require('vendor/autoload.php');
require('library/autoload.php');

require('config/config.php');

$app = new \Slim\Slim(array(
        'view'      => new \D3R\View\Json(),
        'debug'     => false
    )
);

$app->add(new \D3R\Middleware\HttpBasicAuth(USERNAME, PASSWORD, REALM));
$app->add(new \D3R\Middleware\Jsonp());

$app->error(function (\Exception $ex) use ($app) {
    $app->render($ex->getCode(), array($ex->getMessage()));
});

// Be a bit aggressive with 404s - send back 400 Bad Request
$app->notFound(function () use ($app) {
    $app->render(400, array("Bad request"));
});

$app->get('/:component/:metric', function($component, $metric) use ($app) {
    foreach (array('component', 'metric') as $variable)
    {
        $$variable = ucfirst(strtolower($$variable));
    }
    $class = '\D3R\\' . $component . '\Base';

    if (!class_exists($class))
    {
        throw new \Exception("Invalid component", 400);
    }

    $obj = $class::Factory($metric);
    $app->render(200, $obj->getData());
});

$app->run();
