<?php

use Project\Queue;
use Slim\Http\Request;
use Slim\Http\Response;

$app->get('/home', function(Request $request, Response $response) {

//    Queue::createJob('club/insert/310F44D3-EEC3-4D9C-9FAE-5DD269AE899D');
//    Queue::createJob('club/update/1');
//    Queue::createJob('draw/4/team/insert');
//    Queue::createJob('team/11/player/update');
//    Queue::createJob('draw/4/matches/insert');
    Queue::createJob('match/1/update');

    return $response->getBody()->write('Jobs inserted');

})->setName('home');