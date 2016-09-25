<?php


namespace Project\Scrappers;


use Carbon\Carbon;
use Project\Competition;
use Project\Helpers\Url;
use Project\Match;
use Project\Model;
use Project\Team;

class DrawScrapper implements IScrapper
{
    private $dom;
    private $model;
    private $competitionId;

    public function __construct(\DOMDocument $dom, Model $model)
    {
        $this->dom = $dom;
        $this->model = $model;
        $this->competitionId = $this->model->competition()->first()->id;
    }

    /**
     * Scrapes DOMDocument for data.
     *
     * @return mixed
     */
    public function scrape()
    {
        $this->scrapeTeams();
    }

    private function scrapeTeams() {
        $table = $this->dom->getElementsByTagName('table')->item(0);
        $tbody = $table->getElementsByTagName('tbody')->item(0);
        $trs = $tbody->getElementsByTagName('tr');

        foreach ($trs as $tr) {
            $cols = $tr->childNodes;

            $aTag = $cols->item(1)->getElementsByTagName('a')->item(0);
            $name = $aTag->nodeValue;
            $trackingId = Url::extractTeamId($aTag->attributes->item(0)->value);

            $tempTeam = Team::where('tracking_id', $trackingId)->where('draw_id', $this->model->id)->first();

            if ($tempTeam) continue;

            $team = new Team();

            $team->tracking_id = $trackingId;
            $team->competition_id = $this->competitionId;
            $team->draw_id = $this->model->id;
            $team->name = $name;

            $team->save();
        }
    }

    public function scrapeMatches(Competition $competition) {
        $content = $this->dom->getElementById('content');
        $table = $content->getElementsByTagName('div')->item(0)->getElementsByTagName('table')->item(0);
        $tbody = $table->getElementsByTagName('tbody')->item(0);
        $trs = $tbody->getElementsByTagName('tr');

        foreach ($trs as $tr) {
            $match = new Match();

            $match->draw_id = $this->model->id;

            $cols = $tr->childNodes;

            $match->played_at = Carbon::createFromFormat('d/m/Y H:i', Url::extractTimestamp($cols->item(1)->nodeValue));

            $homeTeam = $cols->item(5);
            $aTagHomeTeam = $homeTeam->getElementsByTagName('a')->item(0);
            $nameHomeTeam = $aTagHomeTeam->nodeValue;

            $tempHomeTeam = Team::where('competition_id', $this->competitionId)->where('draw_id', $this->model->id)->where('name', $nameHomeTeam)->first();

            $match->home_team_id = $tempHomeTeam->id;

            $awayTeam = $cols->item(7);
            $nameAwayTeam = $awayTeam->getElementsByTagName('a')->item(0)->nodeValue;

            $tempAwayTeam = Team::where('competition_id', $this->competitionId)->where('draw_id', $this->model->id)->where('name', $nameAwayTeam)->first();

            $match->away_team_id = $tempAwayTeam->id;

            $match->tracking_id = Url::extractMatchId($aTagHomeTeam->attributes->item(1)->value);

            $tempMatch = Match::where('tracking_id', $match->tracking_id)->where('draw_id', $this->model->id)->first();

            if ( ! $tempMatch) {
                $match->save();
            }
        }
    }
}