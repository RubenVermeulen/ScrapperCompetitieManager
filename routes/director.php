<?php

use Project\Queue;
use Slim\Http\Request;
use Slim\Http\Response;

$app->get('/', function(Request $request, Response $response) {

    $item = Queue::first();

    if ($item) {
        $item->delete();

        $output = null;

        exec('php bin/run.php ' . $item->route, $output);

        $response->getBody()->write($output[0]);
    }
    else {
        $response->getBody()->write('Queue is empty');
    }

    return $response;

});