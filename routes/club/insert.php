<?php

// insert/club/310F44D3-EEC3-4D9C-9FAE-5DD269AE899D

use Project\Club;
use Project\Queue;
use Project\Scrappers\ClubScrapper;
use Slim\Http\Request;
use Slim\Http\Response;

$app->get('/club/insert/{id}', function(Request $request, Response $response, $args) {

    $trackingId = $args['id'];

    $tempClub = Club::where('tracking_id', $trackingId)->first();

    if ($tempClub) return $response->getBody()->write('Club already exists');

    $url = 'http://www.badmintonvlaanderen.be/group/' . $trackingId;
    $dom = new \DOMDocument();
    $html = @file_get_contents($url);

    if ($html === false) {
        $route = 'club/insert/' . $trackingId;

        Queue::createJob($route);

        return $response->getBody()->write("Connection failed ($route), job is added to the queue again");
    }

    $dom->loadHTML($html);

    $club = new Club();

    $club->tracking_id = $trackingId;

    $scrapper = new ClubScrapper($dom, $club);
    $scrapper->scrape();

    return $response->getBody()->write('Club data fetched and stored');

})->setName('club.insert');