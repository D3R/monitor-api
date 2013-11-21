<?php

namespace D3R\View;

class Json extends \Slim\View 
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
