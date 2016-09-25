<?php

// update/club/310F44D3-EEC3-4D9C-9FAE-5DD269AE899D

use Project\Club;
use Project\Queue;
use Project\Scrappers\ClubScrapper;
use Slim\Http\Request;
use Slim\Http\Response;

$app->get('/club/update/{id}', function(Request $request, Response $response, $args) {

    $club = Club::where('id', $args['id'])->first();

    if ( ! $club) return $response->getBody()->write("Club could not be found");

    $url = 'http://www.badmintonvlaanderen.be/group/' . $club->tracking_id;
    $dom = new \DOMDocument();
    $html = @file_get_contents($url);

    if ($html === false) {
        $route = 'club/update/' . $club->id;

        Queue::createJob($route);

        return $response->getBody()->write("Connection failed ($route), job is added to the queue again");
    }

    $dom->loadHTML($html);

    $scrapper = new ClubScrapper($dom, $club);
    $scrapper->scrape();

    return $response->getBody()->write('Club data fetched and stored');

})->setName('club.update');