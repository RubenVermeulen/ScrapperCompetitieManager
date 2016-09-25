<?php

use Carbon\Carbon;
use Project\Competition;
use Project\Helpers\Url;
use Project\Match;
use Project\Team;

require '../bootstrap.php';

$dom = new \DOMDocument();

$competitions = Competition::all();

foreach ($competitions as $competition) {
    echo '<strong>' . $competition->name . '</strong><hr><hr><hr>';

    $draws = $competition->draws();

    foreach ($draws->orderBy('name')->get() as $draw) {
        echo '<strong>' . $draw->name . '</strong><hr><hr>';

        $url = 'http://www.badmintonvlaanderen.be/sport/drawmatches.aspx?id=' . $competition->tracking_id . '&draw=' . $draw->tracking_id;
        $html = @file_get_contents($url);

        if ($html === false) {
            echo 'Could not fetch html from: ' . $url;
            continue;
        }

        $dom->loadHTML($html);

        $content = $dom->getElementById('content');
        $table = $content->getElementsByTagName('div')->item(0)->getElementsByTagName('table')->item(0);
        $tbody = $table->getElementsByTagName('tbody')->item(0);
        $trs = $tbody->getElementsByTagName('tr');

        foreach ($trs as $tr) {
            $match = new Match();

            $match->draw_id = $draw->id;

            $cols = $tr->childNodes;

            $match->played_at = Carbon::createFromFormat('d/m/Y H:i', Url::extractTimestamp($cols->item(1)->nodeValue));

            $homeTeam = $cols->item(5);
            $aTagHomeTeam = $homeTeam->getElementsByTagName('a')->item(0);
            $nameHomeTeam = $aTagHomeTeam->nodeValue;

            $tempHomeTeam = Team::where('competition_id', $competition->id)->where('draw_id', $draw->id)->where('name', $nameHomeTeam)->first();

            $match->home_team_id = $tempHomeTeam->id;

            $awayTeam = $cols->item(7);
            $nameAwayTeam = $awayTeam->getElementsByTagName('a')->item(0)->nodeValue;

            $tempAwayTeam = Team::where('competition_id', $competition->id)->where('draw_id', $draw->id)->where('name', $nameAwayTeam)->first();

            $match->away_team_id = $tempAwayTeam->id;

            $match->tracking_id = Url::extractMatchId($aTagHomeTeam->attributes->item(1)->value);

            $tempMatch = Match::where('tracking_id', $match->tracking_id)->where('draw_id', $draw->id)->first();

            if ( ! $tempMatch) {
                $match->save();
                echo 'Saved';
            }
            else {
                echo 'Already exists';
            }

            echo '<hr>';
        }
    }

    echo '<br>';
}