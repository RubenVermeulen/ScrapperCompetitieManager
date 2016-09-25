<?php

use Project\Helpers\Url;
use Project\Player;
use Project\PlayersTeams;
use Project\Team;

require '../bootstrap.php';

$dom = new \DOMDocument();

$start = microtime(true);

$teams = Team::whereNotNull('tracking_id')->get();

foreach ($teams as $team) {
    $competitionTrackingId = $team->competition()
        ->first()
        ->tracking_id;

    $url = "http://www.badmintonvlaanderen.be/sport/teamplayers.aspx?id={$competitionTrackingId}&tid={$team->tracking_id}";

    $html = @file_get_contents($url);

    if ($html === false) {
        echo 'Following URL failed: ' . $url . '<br>';
        continue;
    }

    $dom->loadHTML($html);

    $captions = $dom->getElementsByTagName('caption');

    $nodes = [];

    foreach ($captions as $caption) {
        switch(trim($caption->nodeValue)) {
            case 'Heren':
                $nodes[] = [
                    'ref' => $caption->parentNode,
                    'gender' => 0,
                ];
                break;
            case 'Dames':
                $nodes[] = [
                    'ref' => $caption->parentNode,
                    'gender' => 1,
                ];
                break;
        }
    }

    foreach ($nodes as $node) {
        $tbody = $node['ref']->getElementsByTagName('tbody')->item(0);
        $trs = $tbody->getElementsByTagName('tr');

        foreach ($trs as $tr) {
            $cols = $tr->childNodes;

            // Player
            $player = new Player();

            $aTagName = $cols->item(2)->getElementsByTagName('a')->item(0);

            $name = explode(',', $aTagName->nodeValue);

            $trackingId = Url::extractPlayerId($aTagName->attributes->item(0)->value);

            $player->gender = $node['gender'];
            $player->membership_id = $cols->item(4)->nodeValue;

            // Does player exists
            $tempPlayer = Player::where('membership_id', $player->membership_id)->first();

            if ($tempPlayer) { // Update info if player exists
                $player = $tempPlayer;
            }

            $player->first_name = trim($name[1]);
            $player->last_name = trim($name[0]);
            $player->ranking_single = $cols->item(6)->nodeValue;
            $player->ranking_double = $cols->item(7)->nodeValue;
            $player->ranking_mix = $cols->item(8)->nodeValue;

            $player->save();

            // Player team relation
            // Does relation exists
            $tempPlayerTeams = PlayersTeams::where('player_id', $player->id)->where('team_id', $team->id)->first();

            if ($tempPlayerTeams) continue;

            $playersTeams = new PlayersTeams();

            $playersTeams->player_id = $player->id;
            $playersTeams->team_id = $team->id;
            $playersTeams->tracking_id = $trackingId;

            $playersTeams->save();
        }
    }
}

echo 'Execution time: ' . (microtime(true) - $start);