<?php

/**
 * Created by IntelliJ IDEA.
 * User: pierr
 * Date: 13.12.2017
 * Time: 12:33
 */

include "dbconn.php";

function degrees($r)
{
    switch ($r % 4) {
        case 0:
            return 0;
        case 1:
            return 90;
        case 2:
            return 180;
        default:
            return 270;
    }
}

function renderPlayer($name, $score, $x, $y, $deg)
{
    $htmlname = htmlspecialchars("$name ($score)");
    echo "
<div class='player' style='left: {$x}px; top: {$y}px;'>
    <div style='display: block; text-align: center;'>$htmlname</div>
    <img src='player.png' style='transform: rotate({$deg}deg);'>
</div>
";
}

function renderBullet($x, $y, $r, $offsetPercent)
{
    $animTime = 300;

    $offset = ($animTime * $offsetPercent) / 100;

    echo "<img class='bullet' src='bullet.png' data-r='$r' style='left: {$x}px; top: {$y}px; animation-delay: -{$offset}ms;'>";
}

function playerTile($name, $score, $tileX, $tileY, $r)
{
    renderPlayer($name, $score, $tileX * 100, $tileY * 100, degrees($r));
}

function bulletTile($tileX, $tileY, $r, $offsetPercent)
{
    renderBullet($tileX * 100 + 20, $tileY * 100 + 50, $r, $offsetPercent);
}

function listPlayers($dbConn, $cX, $cY, $pX, $pY)
{
    $result = mysqli_query($dbConn, "SELECT * FROM players WHERE dead = 0");
    while ($row = mysqli_fetch_array($result)) {
        $x = $row["x"] + $cX - $pX;
        $y = $row["y"] + $cY - $pY;
        playerTile($row["playername"], $row["score"], $x, $y, $row["r"]);
    }
}

function listBullets($dbConn, $cX, $cY, $pX, $pY)
{
    $result = mysqli_query($dbConn, "SELECT * FROM bullets");
    $timestamp = nowWithMillis();
    while ($row = mysqli_fetch_array($result)) {
        $x = $row["x"] + $cX - $pX;
        $y = $row["y"] + $cY - $pY;
        $offset = milliDiff($timestamp, $row["lastupdated"]);
        bulletTile($x, $y, $row["r"], $offset / 4);
    }
}

function cleanPlayers($dbConn)
{
    $stmt = mysqli_prepare($dbConn, "DELETE FROM players WHERE lastupdated < ?");
    $timeout = date('Y-m-d G:i:s', time() - strtotime("10 seconds", 0));
    mysqli_stmt_bind_param($stmt, "s", $timeout);
    mysqli_stmt_execute($stmt);
}

function offset($r)
{
    $x = 0;
    $y = 0;
    switch ($r) {
        case 0:
            $y = -1;
            break;
        case 1:
            $x = 1;
            break;
        case 2:
            $y = 1;
            break;
        case 3:
            $x = -1;
            break;
        default:
    }
    return array($x, $y);
}

function movePlayer($dbConn, $name, $r)
{
    list($x, $y) = offset($r);

    $stmt = mysqli_prepare($dbConn, "UPDATE players SET x = x + ?, y = y + ?, r = ? WHERE playername = ?");
    mysqli_stmt_bind_param($stmt, "iiis", $x, $y, $r, $name);
    mysqli_stmt_execute($stmt);
}

function updatePlayer($dbConn, $playername)
{
    $timestamp = date('Y-m-d G:i:s');
    $stmt = mysqli_prepare($dbConn, "UPDATE players SET lastupdated = '$timestamp' WHERE playername = ?");
    mysqli_stmt_bind_param($stmt, "s", $playername);
    mysqli_stmt_execute($stmt);
}

function getPlayerCoords($dbConn, $name)
{
    $stmt = mysqli_prepare($dbConn, "SELECT * FROM players WHERE playername = ?");
    mysqli_stmt_bind_param($stmt, "s", $name);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    $row = mysqli_fetch_assoc($result);
    return array($row["x"], $row["y"], $row["r"]);
}

function shoot($dbConn, $name, $x, $y, $r)
{
    $stmt = mysqli_prepare($dbConn, "INSERT INTO bullets (x, y, r, playername, lastupdated, created) VALUES (?, ?, ?, ?, ?, ?)");
    $timestamp = nowWithMillis();
    mysqli_stmt_bind_param($stmt, "iiisss", $x, $y, $r, $name, $timestamp, $timestamp);
    mysqli_stmt_execute($stmt);
}

function fraction($float)
{
    $parts = explode('.', $float);
    if (sizeof($parts) > 1)
        return $parts[1];
    else
        return '0';
}

function nowWithMillis()
{
    return dateWithMillis(time(), explode(' ', microtime())[0]);
}

function dateWithMillis($seconds, $millis)
{
    return date('Y-m-d G:i:s.' . fraction($millis), $seconds);
}

function datePlusMillis($date, $millis)
{
    $dateSeconds = strtotime(explode('.', $date)[0]);
    $dateMillis = floatval('0.' . explode('.', $date)[1]);
    $dateMillis += $millis / 1000;
    $dateSeconds += floor($dateMillis);
    if ($dateMillis < 0) {
        $milliFraction = fraction($dateMillis - floor($dateMillis));
    } else
        $milliFraction = fraction($dateMillis);
    return date('Y-m-d G:i:s.' . $milliFraction, $dateSeconds);
}

function milliDiff($date1, $date2)
{
    $date1Seconds = strtotime(explode('.', $date1)[0]);
    $date1Millis = floatval('0.' . explode('.', $date1)[1]) * 1000;
    $date2Seconds = strtotime(explode('.', $date2)[0]);
    $date2Millis = floatval('0.' . explode('.', $date2)[1]) * 1000;
    $diffSeconds = $date1Seconds - $date2Seconds;
    $diffMillis = $date1Millis - $date2Millis;
    return $diffMillis + ($diffSeconds * 1000);
}

function updateBullets($dbConn)
{
    $timestamp = nowWithMillis();
    $interval = 180;
    $refresh = datePlusMillis($timestamp, -$interval);
    $result = mysqli_query($dbConn, "SELECT * FROM bullets WHERE lastupdated < '$refresh'");
    while ($row = mysqli_fetch_array($result)) {
        $id = $row["id"];
        list($x, $y) = array($row["x"], $row["y"]);
        list($xOff, $yOff) = offset($row["r"]);
        list($newX, $newY) = array($x + $xOff, $y + $yOff);
        if (milliDiff($timestamp, $row["created"]) > 10000)//($newX < 0 || $newY < 0 || $x > 20 || $y > 20)
            mysqli_query($dbConn, "DELETE FROM bullets WHERE id = $id");
        else
            mysqli_query($dbConn, "UPDATE bullets SET x = $newX, y = $newY, lastupdated = '$timestamp' WHERE id = $id");
    }

    checkCollisions($dbConn);
}

function addScore($dbConn, $playername, $score)
{
    mysqli_query($dbConn, "SELECT ");
    $stmt = mysqli_prepare($dbConn, "UPDATE players SET score = score + $score WHERE playername = ?");
    mysqli_stmt_bind_param($stmt, "s", $playername);
    mysqli_stmt_execute($stmt);
}

function hit($dbConn, $playername)
{
    $stmt = mysqli_prepare($dbConn, "UPDATE players SET dead = 1 WHERE playername = ?");
    mysqli_stmt_bind_param($stmt, "s", $playername);
    mysqli_stmt_execute($stmt);
    $score = getScore($dbConn, $playername);
    if ($score > 0) {
        $stmt = mysqli_prepare($dbConn, "INSERT INTO leaderboard (playername, score) Values (?, $score)");
        mysqli_stmt_bind_param($stmt, "s", $playername);
        mysqli_stmt_execute($stmt);
    }
}

function checkCollisions($dbConn)
{
    $result = mysqli_query($dbConn, "SELECT players.playername AS target, bullets.playername AS source FROM bullets, players WHERE bullets.x = players.x AND bullets.y = players.y AND bullets.playername != players.playername AND players.dead = 0");
    while ($row = mysqli_fetch_array($result)) {
        hit($dbConn, $row["target"]);
        addScore($dbConn, $row["source"], 10);
    }
}

function redirect($location)
{
    header("Location: $location");
}

/*function refresh($playername, $token)
{
    redirect("./game.php?playername=$playername&token=$token");
}*/

function addParam($param)
{
    redirect("./game.php?" . $_SERVER['QUERY_STRING'] . "&$param");
}

function showMenu()
{
    echo "
<div id='controls'>
    <a href='./game.php?" . $_SERVER['QUERY_STRING'] . "&move=3'><img class='button' src='left.png'></a>
    <a href='./game.php?" . $_SERVER['QUERY_STRING'] . "&move=1'><img class='button' src='right.png'></a>
    <a href='./game.php?" . $_SERVER['QUERY_STRING'] . "&move=0'><img class='button' src='up.png'></a>
    <a href='./game.php?" . $_SERVER['QUERY_STRING'] . "&move=2'><img class='button' src='down.png'></a>
    <a href='./game.php?" . $_SERVER['QUERY_STRING'] . "&shoot=1'><img class='button' src='shoot.png'></a>
</div>
";
}

function isTokenValid($dbConn, $playername, $token)
{
    $stmt = mysqli_prepare($dbConn, "SELECT token FROM players WHERE playername = ?");
    mysqli_stmt_bind_param($stmt, "s", $playername);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    $numRows = mysqli_num_rows($result);
    if ($numRows > 0) {
        $row = mysqli_fetch_assoc($result);
        return $row["token"] == $token;
    } else {
        $stmt = mysqli_prepare($dbConn, "INSERT INTO players (playername, token, x, y, r, lastupdated) VALUES (?, ?, ?, ?, ?, ?)");
        $timestamp = date('Y-m-d G:i:s');
        $x = rand(-10, 10);
        $y = rand(-10, 10);
        $r = 1;
        mysqli_stmt_bind_param($stmt, "ssiiis", $playername, $token, $x, $y, $r, $timestamp);
        mysqli_stmt_execute($stmt);
        return true;
    }
}

function getScore($dbConn, $playername)
{
    $stmt = mysqli_prepare($dbConn, "SELECT score FROM players WHERE playername = ?");
    mysqli_stmt_bind_param($stmt, "s", $playername);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    $numRows = mysqli_num_rows($result);
    if ($numRows > 0) {
        $row = mysqli_fetch_assoc($result);
        return intval($row["score"]);
    } else
        return 0;
}

function isAlive($dbConn, $playername)
{
    $stmt = mysqli_prepare($dbConn, "SELECT dead FROM players WHERE playername = ?");
    mysqli_stmt_bind_param($stmt, "s", $playername);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    $numRows = mysqli_num_rows($result);
    if ($numRows > 0) {
        $row = mysqli_fetch_assoc($result);
        if ($row["dead"] == "1")
            return false;
        else
            return true;
    } else {
        return true;
    }
}

function showGame($dbConn, $playername, $cX, $cY)
{
    showMenu();

    list($pX, $pY, $pR) = getPlayerCoords($dbConn, $playername);

    listPlayers($dbConn, $cX, $cY, $pX, $pY);
    listBullets($dbConn, $cX, $cY, $pX, $pY);
}

function showDead($dbConn, $playername)
{
    $score = getScore($dbConn, $playername);

    redirect("./login.php?gameover=$score");
    /*echo "
<div>
  <h1>Game Over!</h1>
  $score Punkte erreicht!
  <!--a href='./game.php?respawn'><h2>Respawn</h2></a-->
</div>
";*/
}

function respawn($dbConn, $playername)
{

}

function main($dbConn, $header, $closeHeader)
{
    if (isset($_GET["playername"]) && strlen($_GET["playername"]) < 20) {
        $playername = $_GET["playername"];
        if (!isset($_GET["token"])) {
            $token = genToken(16);
            addParam("token=$token");
        } else {
            $token = $_GET["token"];
        }

        $cX = (isset($_GET["cx"]) ? $_GET["cx"] : 0);
        $cY = (isset($_GET["cy"]) ? $_GET["cy"] : 0);

        if (isTokenValid($dbConn, $playername, $token)) {
            if (isAlive($dbConn, $playername)) {
                echo $header;

                updatePlayer($dbConn, $playername);

                cleanPlayers($dbConn);

                if (isset($_GET["move"]) && $_GET["move"] >= 0) {
                    movePlayer($dbConn, $playername, $_GET["move"]);
                    //refresh($playername, $token);
                }

                if (isset($_GET["shoot"]) && $_GET["shoot"] > 0) {
                    list($x, $y, $r) = getPlayerCoords($dbConn, $playername);
                    shoot($dbConn, $playername, $x, $y, $r);
                    //refresh($playername, $token);
                }

                showGame($dbConn, $playername, $cX, $cY);

                updateBullets($dbConn);

                echo $closeHeader;
            } else {
                showDead($dbConn, $playername);
            }
        } else {
            echo $header;
            echo "Wrong token!";
            echo $closeHeader;
        }
    } else {
        echo $header;
        echo "Invalid playername or token! Cannot exceed 20 characters.";
        echo $closeHeader;
    }
}

?>


<?php

$header = <<<END
<!DOCTYPE html>
<html lang="de">
<head>
    <meta charset="UTF-8">
    <title>BOOM BOOM BANG</title>
    <link rel='stylesheet' type="text/css" href="game.css$stylePostfix">
    <script src="game.js$jsPostfix"></script>
    <script src="keyboard.js"></script>
    <meta name="viewport" content="width=device-width, initial-scale=0.5, maximum-scale=0.5, user-scalable=0">
    <!--meta http-equiv="refresh" content="1"-->
</head>
<body>
END;

$closeHeader = <<<END
</body>
</html>
END;


main($dbConn, $header, $closeHeader);

?>

