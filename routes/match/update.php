<?php

use Project\Queue;
use Project\Scrappers\MatchPageScrapper;
use Slim\Http\Request;
use Slim\Http\Response;

$app->get('/match/{id}/update', function(Request $request, Response $response, $args) {

    $match = \Project\Match::where('id', $args['id'])->first();

    if ( ! $match) return $response->getBody()->write("Match could not be found");

    $competitionTrackingId = $match->draw()
        ->first()
        ->competition()
        ->first()
        ->tracking_id;

    $url = "http://www.badmintonvlaanderen.be/sport/teammatch.aspx?id={$competitionTrackingId}&match={$match->tracking_id}";
    $dom = new \DOMDocument();
    $html = @file_get_contents($url);

    if ($html === false) {
        $route = 'match/' . $match->id . '/update';

        Queue::createJob($route);

        return $response->getBody()->write("Connection failed ($route), job is added to the queue again");
    }

    $dom->loadHTML($html);

    $scrapper = new MatchPageScrapper($dom, $match);
    $scrapper->scrape();

    return $response->getBody()->write('Match data fetched and stored');

})->setName('match.update');