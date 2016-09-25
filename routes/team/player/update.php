<?php

use Project\Scrappers\TeamScrapper;
use Project\Team;
use Slim\Http\Request;
use Slim\Http\Response;

$app->get('/team/{id}/player/update', function(Request $request, Response $response, $args) {

    $team = Team::where('id', $args['id'])->first();

    if ( ! $team) return $response->getBody()->write("Team could not be found");

    $competitionTrackingId = $team->competition()
        ->first()
        ->tracking_id;

    $url = "http://www.badmintonvlaanderen.be/sport/teamplayers.aspx?id={$competitionTrackingId}&tid={$team->tracking_id}";
    $dom = new \DOMDocument();
    $html = @file_get_contents($url);

    if ($html === false) {
        $route = 'team/' . $team->id . '/player/insert';

        Queue::createJob($route);

        return $response->getBody()->write("Connection failed ($route), job is added to the queue again");
    }

    $dom->loadHTML($html);

    $scrapper = new TeamScrapper($dom, $team);
    $scrapper->scrape();

    return $response->getBody()->write('Team players fetched and stored');

})->setName('team.player.insert');