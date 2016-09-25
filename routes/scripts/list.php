<?php
use Project\Club;

require 'vendor/autoload.php';
require 'database/database.php';

$clubs = Club::all();
$x = 1;
?>

<!doctype html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport"
          content="width=device-width, user-scalable=no, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Document</title>
</head>
<body>
    <table>
        <tr>
            <th>#</th>
            <th>Tracking ID</th>
            <th>Name</th>
            <th>Address</th>
            <th>Contact person</th>
            <th>Tel</th>
            <th>Email</th>
            <th>Website</th>
        </tr>

        <?php
        foreach ($clubs as $club) {
            ?>

            <tr>
                <td><?= $x ?></td>
                <td><?= $club->tracking_id ?></td>
                <td><?= $club->name ?></td>
                <td><?= $club->address ?></td>
                <td><?= $club->contact_person ?></td>
                <td><?= $club->tel ?></td>
                <td><a href="mailto:<?= $club->email ?>"><?= $club->email ?></a></td>
                <td><a href="<?= $club->website ?>" target="_blank"><?= $club->website ?></a></td>
            </tr>

            <?php

            $x++;
        }
        ?>

    </table>
</body>
</html>
