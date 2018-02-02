<?php
/**
 * Created by IntelliJ IDEA.
 * User: pierr
 * Date: 01.02.2018
 * Time: 10:56
 */

function genToken($length)
{
    $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
    $charactersLength = strlen($characters);
    $randomString = '';
    for ($i = 0; $i < $length; $i++) {
        $randomString .= $characters[rand(0, $charactersLength - 1)];
    }
    return $randomString;
}

$dbHost = "localhost";
$dbUser = "root";
$dbPass = "";
$dbName = "game";

$dbConn = mysqli_connect($dbHost, $dbUser, $dbPass, $dbName)
or exit("Keine Verbindung zu MySQL");

$cacheHtml = true;
$cacheStyles = false;
$cacheJs = false;

if (!$cacheHtml) {
    header("Cache-Control: no-cache, no-store, must-revalidate"); // HTTP 1.1
    header("Pragma: no-cache"); // HTTP 1.0
    header("Expires: 0"); // Proxies
}

$nocachePostfix = '?' . date('Y-m-d G:i:s');

if ($cacheStyles) $stylePostfix = ""; else $stylePostfix = $nocachePostfix;
if ($cacheJs) $jsPostfix = ""; else $jsPostfix = $nocachePostfix;
