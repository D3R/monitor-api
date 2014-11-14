<?php

namespace D3R\Monitor\View;

class Json extends \Slim\View
{
    public function render($status)
    {
        $app = \Slim\Slim::getInstance();

        $this->data->remove('flash');

        $body = array(
                'version'   => API_VERSION,
                'timestamp' => time(),
                'status'    => intval($status),
                'data'      => $this->all()
            );

        $app->contentType('application/json');
        $app->response()->status($status);
        $app->response()->body(json_encode($body));

        $app->stop();
    }
}
