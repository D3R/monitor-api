<?php
require(__DIR__ . '/../vendor/autoload.php');

define("API_VERSION", 1);

if (Phar::running()) {
    $_SERVER['PATH_INFO']   = $_SERVER['REQUEST_URI'];
    $_SERVER['SCRIPT_NAME'] = basename(Phar::running(false));
}

$app = new \Slim\Slim(array(
        'view'        => new \D3R\Monitor\View\Json(),
        'debug'       => false,
        'log.level'   => \Slim\Log::DEBUG,
        'log.enabled' => true,
    )
);

$app->add(new \D3R\Monitor\Middleware\Jsonp());
$app->add(new \D3R\Monitor\Middleware\Clf());

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
    $class = '\D3R\Monitor\Component';

    if (!class_exists($class))
    {
        throw new \Exception("Invalid module", 400);
    }

    $obj = $class::Factory($module, $component, $app->request);
    $app->render(200, $obj->getData());
});

$app->run();
