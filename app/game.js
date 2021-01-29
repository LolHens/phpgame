function getQueryVariable(variable) {
    var query = window.location.search.substring(1);
    var vars = query.split("&");
    for (var i = 0; i < vars.length; i++) {
        var pair = vars[i].split("=");
        if (pair[0] === variable) {
            return pair[1];
        }
    }
    console.error('Query Variable ' + variable + ' not found');
}

function httpGet(theUrl) {
    var xmlHttp = new XMLHttpRequest();
    xmlHttp.open("GET", theUrl, false); // false for synchronous request
    xmlHttp.send(null);
    return xmlHttp.responseText;
}

var playername = getQueryVariable("playername");
var token = getQueryVariable("token")||"";

var cX = Math.floor(window.innerWidth / 2 / 100);
var cY = Math.floor(window.innerHeight / 2 / 100);

var exit = false;

document.addEventListener('keydown', function (event) {
    var key = event.which;

    var direction = -1;
    switch (key) {
        /*case 38:
            direction = 0;
            break;
        case 39:
            direction = 1;
            break;
        case 40:
            direction = 2;
            break;
        case 37:
            direction = 3;
            break;*/
        case 87:
            direction = 0;
            break;
        case 68:
            direction = 1;
            break;
        case 83:
            direction = 2;
            break;
        case 65:
            direction = 3;
            break;
    }

    var shoot = 0;
    if (key === 32) shoot = 1;

    if (direction >= 0 || shoot > 0) {
        exit = true;
        window.location = "game.php?playername=" + playername + "&token=" + token + "&cx=" + cX + "&cy=" + cY + "&move=" + direction + "&shoot=" + shoot;
    }
});

setTimeout(function () {
    if (!exit) {
        window.location.href = "game.php?playername=" + playername + "&token=" + token + "&cx=" + cX + "&cy=" + cY;
    }
}, 200);
