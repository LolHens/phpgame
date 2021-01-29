<!DOCTYPE html>
<html lang="de">

<?php
include "dbconn.php";
?>

<html>
<head>
    <meta charset="UTF-8">
    <title>BOOM BOOM BANG Login</title>
    <?php
    echo "<link rel='stylesheet' type='text/css' href='login.css$stylePostfix'>";
    ?>
</head>
<body background="background.png">
<div id="loginwindow">
    <div id="logincontent">
        <div id="logintitle">
            <h1>BANG BOOM BANG</h1>
        </div>
        <div id="logintext">
            <?php
            if (isset($_GET["gameover"])) {
                $score = $_GET["gameover"];
                echo "
<h2>Game Over!</h2>
<h2>Du hast $score Punkte erreicht!</h2>
";
            }
            ?>
        </div>
        <div id="loginform">
            <form action="./game.php" method="get">
                <input type="text" name="playername" placeholder="Nickname">
                <input type="submit" name="submit" value="Login">
            </form>
        </div>
        <div id="leaderboard">
            <table>
                <tr>
                    <th>Spielername</th>
                    <th>Score</th>
                </tr>
                <?php
                /**
                 * Created by IntelliJ IDEA.
                 * User: pierr
                 * Date: 01.02.2018
                 * Time: 10:54
                 */

                $result = mysqli_query($dbConn, "SELECT playername, score FROM leaderboard ORDER BY score DESC LIMIT 10");
                while ($row = mysqli_fetch_array($result)) {
                    echo "
<tr>
  <td>" . $row["playername"] . "</td>
  <td>" . $row["score"] . "</td>
</tr>
";
                }
                ?>
            </table>
        </div>
    </div>
</div>
</body>
</html>
