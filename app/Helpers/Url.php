<?php
/**
 * Created by PhpStorm.
 * User: Ruben
 * Date: 20/09/2016
 * Time: 17:12
 */

namespace Project\Helpers;


class Url
{
    public static function extractId($url) {
        $matches = [];

        preg_match('/[A-Za-z0-9]{8}-[A-Za-z0-9]{4}-[A-Za-z0-9]{4}-[A-Za-z0-9]{4}-[A-Za-z0-9]{12}/', $url, $matches);

        if ( ! empty($matches)) {
            return $matches[0];
        }

        return null;
    }

    public static function extractTeamId($url) {
        $matches = [];

        preg_match('/team=[0-9]+/', $url, $matches);

        if ( ! empty($matches)) {
            return substr($matches[0], 5);
        }

        return null;
    }

    public static function extractDrawId($url) {
        $matches = [];

        preg_match('/draw=[0-9]+/', $url, $matches);

        if ( ! empty($matches)) {
            return substr($matches[0], 5);
        }

        return null;
    }

    public static function extractMatchId($url) {
        $matches = [];

        preg_match('/match=[0-9]+/', $url, $matches);

        if ( ! empty($matches)) {
            return substr($matches[0], 6);
        }

        return null;
    }

    public static function extractTimestamp($string) {
        $matches = [];

        preg_match('/[0-9]{1,2}\/[0-9]{2}\/[0-9]{4} [0-9]{1,2}:[0-9]{2}/', $string, $matches);

        if ( ! empty($matches)) {
            return $matches[0];
        }

        return null;
    }

    public static function extractPlayerId($url) {
        $matches = [];

        preg_match('/player=[0-9]+/', $url, $matches);

        if ( ! empty($matches)) {
            return substr($matches[0], 7);
        }

        return null;
    }

    public static function extractHtml($url) {
        $c = curl_init($url);
        curl_setopt($c, CURLOPT_RETURNTRANSFER, true);

        $html = curl_exec($c);

        if (curl_error($c)) {
            return false;
        }

        curl_close($c);

        return $html;
    }
}