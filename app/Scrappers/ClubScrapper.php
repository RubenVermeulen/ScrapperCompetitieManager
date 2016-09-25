<?php

namespace Project\Scrappers;


use Project\Competition;
use Project\Draw;
use Project\Helpers\Url;
use Project\Model;
use Project\Team;

class ClubScrapper implements IScrapper
{
    private $dom;
    private $model;
    private $main;

    public function __construct(\DOMDocument $dom, Model $model)
    {
        $this->dom = $dom;
        $this->model = $model;
        $this->main = $this->dom->getElementById('main');
    }

    public function scrape() {
        $this->scrapeClubDetails();

        $this->model->save();

        $this->scrapeClubCompetitions();
    }

    private function compare($attr, $value) {
        if ($this->model->{$attr} != $value) {
            $this->model->{$attr} = $value;
        }
    }

    /**
     * Scrapes club information.
     */
    private function scrapeClubDetails() {
        $details = $this->main->getElementsByTagName('div')->item(0);

        $this->model->name = $details->getElementsByTagName('h3')->item(0)->nodeValue;

        $table = $details->getElementsByTagName('table')->item(0);

        if (isset($table)) { // Possible there is no information, only the name
            $trs = $table->getElementsByTagName('tr');

            foreach ($trs as $tr) {
                $cols = $tr->childNodes;
                $value = $cols->item(1)->nodeValue;

                switch (rtrim($cols->item(0)->nodeValue, ':')) {
                    case 'Adres': $this->compare('address', $value);
                        break;
                    case 'Contact': $this->compare('contact_person', $value);
                        break;
                    case 'Telefoon': $this->compare('tel', $value);
                        break;
                    case 'E-mail': $this->compare('email', $value);
                        break;
                    case 'Website': $this->compare('website', $value);
                        break;
                }
            }
        }
    }

    /**
     * Scrapes competitions where the club participates.
     * Also the team and draws are fetched.
     */
    private function scrapeClubCompetitions() {
        $details = null;
        $hasCompetitions = false;

        foreach ($this->main->getElementsByTagName('h3') as $item) {
            if (trim($item->nodeValue) == 'Teams') {
                $details = $item->parentNode;
                $hasCompetitions = true;
                break;
            }
        }

        if ($hasCompetitions) {
            $table = $details->getElementsByTagName('table')->item(0);
            $trs = $table->getElementsByTagName('tr');

            $competition = null;

            foreach ($trs as $tr) {
                $cols = $tr->childNodes;

                if ($cols->item(0)->tagName == 'th') { // Competition
                    $competitionDetails = $cols->item(0);
                    $aTagTeam = $competitionDetails->getElementsByTagName('a')->item(0);

                    $competition = new Competition();

                    $competition->name = $aTagTeam->nodeValue;
                    $competition->tracking_id = Url::extractId($aTagTeam->attributes->item(0)->value);

                    $tempCompetition = $competition->where('tracking_id', $competition->tracking_id)->first();

                    if ( ! $tempCompetition) {
                        $competition->save();
                    }
                    else {
                        $competition = $tempCompetition;
                    }
                }
                else { // Draw and Team

                    // Draw
                    $drawDetails = $cols->item(1);
                    $aTagDraw = $drawDetails->getElementsByTagName('a')->item(0);

                    if ( ! isset($aTagDraw)) { // If no draw exists, do not continue
                        continue;
                    }

                    $draw = new Draw();

                    $draw->name = $aTagDraw->nodeValue;
                    $draw->competition_id = $competition->id;
                    $draw->tracking_id = Url::extractDrawId($aTagDraw->attributes->item(0)->value);

                    $tempDraw = $draw->where('tracking_id', $draw->tracking_id)->where('competition_id', $competition->id)->first();

                    if ( ! $tempDraw) {
                        $draw->save();
                    }
                    else {
                        $draw = $tempDraw;
                    }

                    // Team
                    $teamDetails = $cols->item(0);
                    $aTagTeam = $teamDetails->getElementsByTagName('a')->item(0);

                    $team = new Team();

                    $team->name = $aTagTeam->nodeValue;
                    $team->draw_id = $draw->id;
                    $team->tracking_id = Url::extractTeamId($aTagTeam->attributes->item(0)->value);
                    $team->club_id = $this->model->id;
                    $team->competition_id = $competition->id;

                    $tempTeam = $team->where('competition_id', $competition->id)->where('draw_id', $draw->id)->where('name', $team->name)->first();

                    if ( ! $tempTeam) {
                        $team->save();
                    }
                    else {
                        if ( ! isset($tempTeam->club_id)) {
                            $tempTeam->club_id = $this->model->id;
                            $tempTeam->save();
                        }
                    }
                }
            }
        }
    }
}