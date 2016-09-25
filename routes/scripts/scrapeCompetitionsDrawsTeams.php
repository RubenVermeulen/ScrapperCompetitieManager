<?php

use Project\Club;
use Project\Competition;
use Project\Draw;
use Project\Helpers\Url;
use Project\Team;

require '../bootstrap.php';

$clubs = Club::all();
//$clubs = Club::where('tracking_id', '3CB9709E-D975-4C39-AB21-2F5ABA02E7FB')->get();

$dom = new \DOMDocument();

$success = 0;
$failed = 0;
$failedUrls = [];

foreach ($clubs as $club)
{

    $url = 'http://www.badmintonvlaanderen.be/group/' . $club->tracking_id;

    $html = @file_get_contents($url);

    if ($html === FALSE) {
        $failedUrls[] = $url;
        $failed++;
        continue;
    }

    $dom->loadHTML($html);

    $main = $dom->getElementById('main');

    $details = null;
    $hasCompetitions = false;

    foreach ($main->getElementsByTagName('h3') as $item) {
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
                $team->club_id = $club->id;
                $team->competition_id = $competition->id;

                $tempTeam = $team->where('competition_id', $competition->id)->where('draw_id', $draw->id)->where('name', $team->name)->first();

                if ( ! $tempTeam) {
                    $team->save();
                }
                else {
                    if ( ! isset($tempTeam->club_id)) {
                        $tempTeam->club_id = $club->id;
                        $tempTeam->save();
                    }
                }
            }
        }
    }

    $success++;
}
?>

<!-- Display information box -->
<h1>Information box</h1>
<table border="1">
    <tr>
        <th>Success</th>
        <td><?= $success ?></td>
    </tr>
    <tr>
        <th rowspan="2">Failed</th>
        <td><?= $failed ?></td>
    </tr>
    <tr>
        <td>
            <?php
            foreach ($failedUrls as $url) {
                echo $url . '<br>';
            }
            ?>
        </td>
    </tr>
</table>
