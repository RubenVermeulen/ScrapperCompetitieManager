<?php

use Carbon\Carbon;
use Project\Game;
use Project\GamesPlayers;
use Project\Helpers\Url;
use Project\Match;
use Project\PlayersTeams;
use Project\Set;

ob_start();

require '../bootstrap.php';

$dom = new \DOMDocument();

$start = microtime(true);

$matches = Match::all();



foreach ($matches as $match) {
    $competitionTrackingId = $match->draw()
        ->first()
        ->competition()
        ->first()
        ->tracking_id;

    $url = "http://www.badmintonvlaanderen.be/sport/teammatch.aspx?id={$competitionTrackingId}&match={$match->tracking_id}";

//    $html = @file_get_contents($url);

    $html = Url::extractHtml($url);

    if ($html === false) {
        echo 'Following URL failed: ' . $url . '<br>';
        ob_flush();
        continue;
    }

    $dom->loadHTML($html);

    $tables = $dom->getElementsByTagName('table');
    $tableMatchDetails = $tables->item(0);
    $tableScoreDetails = $tables->item(1);

    // Match details
    $trsMatchDetails = $tableMatchDetails->getElementsByTagName('tr');

    foreach ($trsMatchDetails as $tr) {
        $cols = $tr->childNodes;

        if ($cols->length == 0) continue;

        switch (rtrim($cols->item(0)->nodeValue, ':')) {
            case 'Tijdstip':
                $playedAt = Carbon::createFromFormat('d/m/Y H:i', Url::extractTimestamp($cols->item(1)->nodeValue));

                if ($playedAt->ne($match->played_at)) {
                    $match->played_at = $playedAt;
                }

                break;
            case 'Uitslag':
                $result = $cols->item(1)->nodeValue;

                if (empty($result) || $result != $match->result) {
                    $match->result = $result;
                }
                break;
        }
    }

    $match->save();

    // Score details
    $tbodyScoreDetails = $tableScoreDetails->getElementsByTagName('tbody')->item(0);
    $trsScoreDetails = $tbodyScoreDetails->childNodes;

    foreach ($trsScoreDetails as $tr) {
        $cols = $tr->childNodes;

        // Are there results
        if (empty($cols->item(1)->nodeValue)) break;

        $type = $cols->item(0)->nodeValue;

        $game = new Game();

        $game->match_id = $match->id;
        $game->type = $type;

        $tempGame = Game::where('match_id', $match->id)
            ->where('type', $type)
            ->first();

        if ($tempGame) {
            $game = $tempGame;
        }
        else {
            $game->save();
        }

        // Players
        $colNumbers = [1,3];

        foreach ($colNumbers as $nr) {
            $tablePlayers = $cols->item($nr)->getElementsByTagName('table')->item(0);

            $trsPlayers = $tablePlayers->getElementsByTagName('tr');

            foreach ($trsPlayers as $trPlayer) {
                $aTag = $trPlayer->getElementsByTagName('a')->item(0);
                $trackingId = Url::extractPlayerId($aTag->attributes->item(1)->value);

                $teamAttribute = $nr == 1 ? 'home_team_id' : 'away_team_id';

                $playerId = PlayersTeams::where('team_id', $match->{$teamAttribute})
                    ->where('tracking_id', $trackingId)
                    ->first()
                    ->player_id;

                $tempGamesPlayers = GamesPlayers::where('game_id', $game->id)->where('player_id', $playerId)->first();

                if ( ! $tempGamesPlayers) {
                    GamesPlayers::create([
                        'game_id' => $game->id,
                        'player_id' => $playerId,
                    ]);
                }
            }
        }

        // Results
        $results = explode(' ', $cols->item(4)->getElementsByTagName('span')->item(0)->nodeValue);
        $countResults = count($results);

        $tempSets = Set::where('game_id', $game->id)->get();
        $countSets = count($tempSets);

        if ($countResults != $countSets) {
            if ($countSets != 0) {
                foreach ($tempSets as $set) $set->delete();
            }

            foreach ($results as $result)
            {
                Set::create([
                    'game_id' => $game->id,
                    'result' => $result,
                ]);
            }
        }
        else {
            $updated = false;

            for ($i = 0; $i < $countResults; $i++)
            {
                if ($tempSets[$i]->result != $results[$i]) {
                    $tempSets[$i]->result = $results[$i];
                    $updated = true;
                }

            }

            if ($updated) {
                foreach ($tempSets as $set) $set->save();
            }
        }
    }
}

echo 'Execution time: ' . (microtime(true) - $start);