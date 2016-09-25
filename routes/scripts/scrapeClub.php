<?php

use Project\Club;
use Project\Helpers\Url;

require '../../bootstrap/app.php';

$url = 'http://www.badmintonvlaanderen.be/group/EB2B4638-6630-4DCF-BD7E-A85347A23570/VAG-EVERGEM-BC';
$dom = new \DOMDocument();
$html = @file_get_contents($url);

if ($html === false) {
    echo 'Could not fetch data';
    die();
}

$dom->loadHTML($html);

$club = new Club();

$club->tracking_id = Url::extractId($url);

$main = $dom->getElementById('main');
$details = $main->getElementsByTagName('div')->item(0);

$club->name = $details->getElementsByTagName('h3')->item(0)->nodeValue;

$table = $details->getElementsByTagName('table')->item(0);

if (isset($table)) { // Possible there is no information, only the name
    $trs = $table->getElementsByTagName('tr');

    foreach ($trs as $tr) {
        $cols = $tr->childNodes;

        switch (rtrim($cols->item(0)->nodeValue, ':')) {
            case 'Adres': $club->address = $cols->item(1)->nodeValue;
                break;
            case 'Contact': $club->contact_person = $cols->item(1)->nodeValue;
                break;
            case 'Telefoon': $club->tel = $cols->item(1)->nodeValue;
                break;
            case 'E-mail': $club->email = $cols->item(1)->nodeValue;
                break;
            case 'Website': $club->website = $cols->item(1)->nodeValue;
                break;
        }
    }
}

$tempClub = Club::where('tracking_id', $club->tracking_id)->first();
$updated = false;

if ($tempClub) { // Update values
    $attributes = ['name', 'address', 'contact_person', 'tel', 'email', 'website'];

    foreach ($attributes as $attribute) {
        $tempClub->{$attribute} = $club->{$attribute};
    }

    $updated = true;

    $tempClub->save();
}
else {
    $club->save();
}


echo 'Data ' . ($updated ? 'updated' : 'saved');
