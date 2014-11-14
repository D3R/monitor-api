<?php

namespace D3R\Monitor\Middleware;

class Clf extends \Slim\Middleware
{
    public function call()
    {
        $this->next->call();

        $request  = $this->app->request;
        $response = $this->app->response;
        $log      = $this->app->log;
        $entry    = [
            $request->getIp(),
            '-',
            '-',
            '[' . strftime('%d/%b/%Y:%H:%M:%S %z') . ']',
            '"' . $request->getMethod() . ' ' . $request->getPath() . ' HTTP/1.1"',
            $response->getStatus(),
            $response->getLength()
        ];
        $log->info(implode(' ', $entry));
    }
}
