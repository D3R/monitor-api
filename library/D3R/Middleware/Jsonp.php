<?php

namespace D3R\Middleware;

class Jsonp extends \Slim\Middleware
{
    public function call()
    {
        $callback = $this->app->request->get('callback');

        $this->next->call();

        if ($callback)
        {
            $this->app->contentType('application/javascript');
            $jsonpBody = htmlspecialchars($callback) . '(' . $this->app->response()->body() . ');';
            $this->app->response()->body($jsonpBody);
        }
    }
}