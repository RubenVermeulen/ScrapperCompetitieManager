<?php


namespace Project\Scrappers;


use Carbon\Carbon;
use Project\Game;
use Project\GamesPlayers;
use Project\Helpers\Url;
use Project\Match;
use Project\PlayersTeams;
use Project\Set;

class MatchPageScrapper implements IScrapper
{
    private $dom;
    private $model;

    public function __construct(\DOMDocument $dom, Match $model)
    {
        $this->dom = $dom;
        $this->model = $model;
    }

    /**
     * Scrapes DOMDocument for data.
     *
     * @return mixed
     */
    public function scrape()
    {
        $tables = $this->dom->getElementsByTagName('table');

        $this->scrapeDetails($tables->item(0));
        $this->scrapeResults($tables->item(1));
    }

    private function scrapeDetails($table) {
        $trs = $table->getElementsByTagName('tr');

        foreach ($trs as $tr) {
            $cols = $tr->childNodes;

            if ($cols->length == 0) continue;

            switch (rtrim($cols->item(0)->nodeValue, ':')) {
                case 'Tijdstip':
                    $playedAt = Carbon::createFromFormat('d/m/Y H:i', Url::extractTimestamp($cols->item(1)->nodeValue));

                    if ($playedAt->ne($this->model->played_at)) {
                        $this->model->played_at = $playedAt;
                    }

                    break;
                case 'Uitslag':
                    $result = $cols->item(1)->nodeValue;

                    if (empty($result) || $result != $this->model->result) {
                        $this->model->result = $result;
                    }
                    break;
            }
        }

        $this->model->save();
    }

    private function scrapeResults($table) {
        $tbody = $table->getElementsByTagName('tbody')->item(0);
        $trs = $tbody->childNodes;

        foreach ($trs as $tr) {
            $cols = $tr->childNodes;

            // Are there results
            if (empty($cols->item(1)->nodeValue)) break;

            $type = $cols->item(0)->nodeValue;

            $game = new Game();

            $game->match_id = $this->model->id;
            $game->type = $type;

            $tempGame = Game::where('match_id', $this->model->id)
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

                    $playerId = PlayersTeams::where('team_id', $this->model->{$teamAttribute})
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
}