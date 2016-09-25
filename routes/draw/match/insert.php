<?php

use Project\Club;
use Project\Draw;
use Project\Queue;
use Project\Scrappers\ClubScrapper;
use Project\Scrappers\DrawScrapper;
use Slim\Http\Request;
use Slim\Http\Response;

$app->get('/draw/{id}/matches/insert', function(Request $request, Response $response, $args) {

    $draw = Draw::where('id', $args['id'])->first();

    if ( ! $draw) return $response->getBody()->write("Draw could not be found");

    $url = 'http://www.badmintonvlaanderen.be/sport/drawmatches.aspx?id=' . $draw->competition()->first()->tracking_id . '&draw=' . $draw->tracking_id;
    $dom = new \DOMDocument();
    $html = @file_get_contents($url);

    if ($html === false) {
        $route = 'draw/' . $draw->id . '/matches/insert';

        Queue::createJob($route);

        return $response->getBody()->write("Connection failed ($route), job is added to the queue again");
    }

    $dom->loadHTML($html);

    $scrapper = new DrawScrapper($dom, $draw);
    $scrapper->scrapeMatches();

    return $response->getBody()->write('Draw matches fetched and stored');

})->setName('draw.team.insert');