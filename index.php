<?php
require('vendor/autoload.php');
// require('library/autoload.php');

define("API_VERSION", 1);
// require('config/config.php');

$app = new \Slim\Slim(array(
        'view'      => new \D3R\Monitor\View\Json(),
        'debug'     => false
    )
);

$app->add(new \D3R\Monitor\Middleware\Jsonp());

$app->error(function (\Exception $ex) use ($app) {
    $app->render($ex->getCode(), array($ex->getMessage()));
});

// Be a bit aggressive with 404s - send back 400 Bad Request
$app->notFound(function () use ($app) {
    $app->render(400, array("Bad request"));
});

$app->get('/:module/:component', function($module, $component) use ($app) {
    foreach (array('module', 'component') as $variable)
    {
        $$variable = ucfirst(strtolower($$variable));
    }
    $class = '\D3R\Monitor\\' . $module . '\Base';

    if (!class_exists($class))
    {
        throw new \Exception("Invalid module", 400);
    }

    $obj = $class::Factory($component, $app->request);
    $app->render(200, $obj->getData());
});

$app->run();
