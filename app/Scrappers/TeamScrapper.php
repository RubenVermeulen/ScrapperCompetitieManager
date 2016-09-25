<?php


namespace Project\Scrappers;


use Project\Helpers\Url;
use Project\Model;
use Project\Player;
use Project\PlayersTeams;

class TeamScrapper implements IScrapper
{
    private $dom;
    private $model;

    public function __construct(\DOMDocument $dom, Model $model)
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
        $this->scrapeTeamPlayers();
    }

    private function scrapeTeamPlayers() {
        $captions = $this->dom->getElementsByTagName('caption');

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
                $tempPlayerTeams = PlayersTeams::where('player_id', $player->id)->where('team_id', $this->model->id)->first();

                if ($tempPlayerTeams) continue;

                $playersTeams = new PlayersTeams();

                $playersTeams->player_id = $player->id;
                $playersTeams->team_id = $this->model->id;
                $playersTeams->tracking_id = $trackingId;

                $playersTeams->save();
            }
        }
    }
}