<?php

// insert/club/310F44D3-EEC3-4D9C-9FAE-5DD269AE899D

use Project\Club;
use Project\Draw;
use Project\Queue;
use Project\Scrappers\ClubScrapper;
use Project\Scrappers\DrawScrapper;
use Slim\Http\Request;
use Slim\Http\Response;

$app->get('/draw/{id}/team/insert', function(Request $request, Response $response, $args) {

    $draw = Draw::where('id', $args['id'])->first();

    if ( ! $draw) return $response->getBody()->write("Draw could not be found");

    $url = 'http://www.badmintonvlaanderen.be/sport/draw.aspx?id=' . $draw->competition()->first()->tracking_id . '&draw=' . $draw->tracking_id;
    $dom = new \DOMDocument();
    $html = @file_get_contents($url);

    if ($html === false) {
        $route = 'draw/' . $draw->id . '/team/insert';

        Queue::createJob($route);

        return $response->getBody()->write("Connection failed ($route), job is added to the queue again");
    }

    $dom->loadHTML($html);

    $scrapper = new DrawScrapper($dom, $draw);
    $scrapper->scrape();

    return $response->getBody()->write('Draw teams fetched and stored');

})->setName('draw.team.insert');