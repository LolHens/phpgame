var AudioContext = window.AudioContext || window.webkitAudioContext;
var audioCtx = new AudioContext();

notes = [
    /*{key: "q", freq: 130.81, type: "square"},
    {key: "2", freq: 138.59, type: "square"},
    {key: "w", freq: 146.83, type: "square"},
    {key: "3", freq: 155.56, type: "square"},
    {key: "e", freq: 164.81, type: "square"},
    {key: "r", freq: 174.61, type: "square"},
    {key: "5", freq: 185.00, type: "square"},
    {key: "t", freq: 196.00, type: "square"},
    {key: "6", freq: 207.65, type: "square"},
    {key: "z", freq: 220.00, type: "square"},
    {key: "7", freq: 233.08, type: "square"},
    {key: "u", freq: 246.94, type: "square"},
    {key: "i", freq: 261.63, type: "square"},
    {key: "9", freq: 277.18, type: "square"},
    {key: "o", freq: 293.66, type: "square"},
    {key: "0", freq: 311.13, type: "square"},
    {key: "p", freq: 329.63, type: "square"},
    {key: "ü", freq: 349.23, type: "square"},
    {key: "´", freq: 369.99, type: "square"},
    {key: "+", freq: 392.00, type: "square"},
    {key: "BACKSPACE", freq: 415.30, type: "square"},
    {key: "ENTER", freq: 440.00, type: "square"},

    {key: "<", freq: 49.00, type: "square"},
    {key: "a", freq: 51.91, type: "square"},
    {key: "y", freq: 55.00, type: "square"},
    {key: "s", freq: 58.27, type: "square"},
    {key: "x", freq: 61.74, type: "square"},
    {key: "c", freq: 65.41, type: "square"},
    {key: "f", freq: 69.30, type: "square"},
    {key: "v", freq: 73.42, type: "square"},
    {key: "g", freq: 77.78, type: "square"},
    {key: "b", freq: 82.41, type: "square"},
    {key: "n", freq: 87.31, type: "square"},
    {key: "j", freq: 92.50, type: "square"},
    {key: "m", freq: 98.00, type: "square"},
    {key: "k", freq: 103.83, type: "square"},
    {key: ",", freq: 110.00, type: "square"},
    {key: "l", freq: 116.54, type: "square"},
    {key: ".", freq: 123.47, type: "square"},
    {key: "-", freq: 130.81, type: "square"},
    {key: "ä", freq: 138.59, type: "square"},
    {key: "SHIFT", freq: 146.83, type: "square"},
    {key: "#", freq: 155.56, type: "square"}

    {key: "SPACE", freq: 82.41, type: "square"}*/
]

function keyCode(string) {
    switch (string) {
        case "SHIFT":
            return 16;
        case "-":
            return 189;
        case ".":
            return 190;
        case ",":
            return 188;
        case "<":
            return 226;
        case "´":
            return 221;
        case "ä":
            return 222;
        case "ö":
            return 192;
        case "ü":
            return 186;
        case "+":
            return 187;
        case "#":
            return 191;
        case "BACKSPACE":
            return 8;
        case "ENTER":
            return 13;
        case "SPACE":
            return 32;
        default:
            return string.toUpperCase().charCodeAt(0);
    }
}

for (var i = 0, len = notes.length; i < len; i++) {
    var oscillator = audioCtx.createOscillator();
    oscillator.type = notes[i].type;
    oscillator.frequency.setValueAtTime(2 * notes[i].freq, 0);
    oscillator.start();

    var gainNode = audioCtx.createGain();
    gainNode.gain.value = 0;

    oscillator.connect(gainNode);
    gainNode.connect(audioCtx.destination);

    notes[i].gainNode = gainNode;

    if (notes[i].key !== undefined) notes[i].keyCode = keyCode(notes[i].key)
}

document.addEventListener('keydown', function (event) {
    var key = event.which;
    console.log(key);

    for (var i = 0, len = notes.length; i < len; i++) {
        if (notes[i].keyCode === event.which) {
            notes[i].gainNode.gain.value = 1;
        }
    }
});

document.addEventListener('keyup', function (event) {
    for (var i = 0, len = notes.length; i < len; i++) {
        if (notes[i].keyCode === event.which) {
            notes[i].gainNode.gain.value = 0;
        }
    }
});
