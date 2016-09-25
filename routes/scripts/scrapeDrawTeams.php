<?php

use Project\Competition;
use Project\Helpers\Url;
use Project\Team;

require '../bootstrap.php';

$dom = new \DOMDocument();

$competitions = Competition::all();

foreach ($competitions as $competition) {
    $draws = $competition->draws();

    foreach ($draws->orderBy('name')->get() as $draw) {
        $url = 'http://www.badmintonvlaanderen.be/sport/draw.aspx?id=' . $competition->tracking_id . '&draw=' . $draw->tracking_id;
        $html = @file_get_contents($url);

        if ($html === false) {
            echo 'Could not fetch html from: ' . $url . '<br>';
            continue;
        }

        $dom->loadHTML($html);

        $table = $dom->getElementsByTagName('table')->item(0);
        $tbody = $table->getElementsByTagName('tbody')->item(0);
        $trs = $tbody->getElementsByTagName('tr');

        foreach ($trs as $tr) {
            $cols = $tr->childNodes;

            $aTag = $cols->item(1)->getElementsByTagName('a')->item(0);
            $name = $aTag->nodeValue;
            $trackingId = Url::extractTeamId($aTag->attributes->item(0)->value);

            $tempTeam = Team::where('tracking_id', $trackingId)->where('draw_id', $draw->id)->first();

            if ($tempTeam) continue;

            $team = new Team();

            $team->tracking_id = $trackingId;
            $team->competition_id = $competition->id;
            $team->draw_id = $draw->id;
            $team->name = $name;

            $team->save();
        }
    }
}

echo 'Execution time: ' . (microtime(true) - $startExecution);