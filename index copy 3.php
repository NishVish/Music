<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Harmonium - Organized Swaram</title>
    <style>
        body { background: #1a1a1a; color: white; font-family: sans-serif; text-align: center; padding: 20px; user-select: none; }
        .piano { display: flex; justify-content: center; background: #333; padding: 20px; border-radius: 12px; width: fit-content; margin: 0 auto; border: 4px solid #444; }
        
        .key { width: 45px; height: 180px; background: white; border: 1px solid #111; cursor: pointer; border-radius: 0 0 6px 6px; position: relative; color: #333; flex-shrink: 0; }
        .key.black { width: 32px; height: 110px; background: #222; margin: 0 -16px; z-index: 2; border-radius: 0 0 4px 4px; color: white; border-color: #000; }
        
        .key.active { background: #f1c40f !important; transform: translateY(4px); box-shadow: 0 4px 10px rgba(241, 196, 15, 0.4); }
        .swaram { position: absolute; bottom: 30px; width: 100%; left: 0; font-size: 16px; font-weight: bold; }
        .kbd-hint { position: absolute; bottom: 8px; width: 100%; left: 0; font-size: 10px; color: #777; font-weight: bold; }
        .black .kbd-hint { color: #aaa; }
    </style>
</head>
<body>

    <h1>Swaram Harmonium</h1>
    <div class="piano" id="piano"></div>
    <p id="msg">Click anywhere to start</p>

<script>
{
    const audioCtx = new (window.AudioContext || window.webkitAudioContext)();
    const soundPath = 'harmonium-kannan-orig.wav';
    const activeVoices = {};

    // Your specific mapping organized by MIDI number to prevent "doubling" the UI
    const noteData = [
        { midi: 53, swaram: "Ṃ", keys: ["s", "a"] },
        { midi: 54, swaram: "Ṃ", keys: ["a"] }, 
        { midi: 55, swaram: "P̣", keys: ["`"] },
        { midi: 56, swaram: "Ḍ", keys: ["1"] },
        { midi: 57, swaram: "Ḍ", keys: ["q"] },
        { midi: 58, swaram: "Ṇ", keys: ["2"] },
        { midi: 59, swaram: "Ṇ", keys: ["w"] },
        { midi: 60, swaram: "S",  keys: ["e"] },
        { midi: 61, swaram: "R",  keys: ["4"] },
        { midi: 62, swaram: "R",  keys: ["r"] },
        { midi: 63, swaram: "G",  keys: ["5"] },
        { midi: 64, swaram: "G",  keys: ["t"] },
        { midi: 65, swaram: "M",  keys: ["y"] },
        { midi: 66, swaram: "M",  keys: ["7"] },
        { midi: 67, swaram: "P",  keys: ["u"] },
        { midi: 68, swaram: "D",  keys: ["8"] },
        { midi: 69, swaram: "D",  keys: ["i"] },
        { midi: 70, swaram: "N",  keys: ["9"] },
        { midi: 71, swaram: "N",  keys: ["o"] },
        { midi: 72, swaram: "Ṡ",  keys: ["p"] },
        { midi: 73, swaram: "Ṙ",  keys: ["-"] },
        { midi: 74, swaram: "Ṙ",  keys: ["["] },
        { midi: 75, swaram: "Ġ",  keys: ["="] },
        { midi: 76, swaram: "Ġ",  keys: ["]"] },
        { midi: 77, swaram: "Ṁ",  keys: ["\\"] },
        { midi: 78, swaram: "Ṁ",  keys: ["'"] },
        { midi: 79, swaram: "Ṗ",  keys: [";"] }
    ];

    const blackNoteOffsets = [1, 3, 6, 8, 10]; // Sharps relative to C

    function playNote(midi, triggerKey) {
        if (activeVoices[midi]) return;
        
        const el = new Audio(soundPath);
        el.loop = true;
        el.preservesPitch = false;
        el.playbackRate = Math.pow(2, (midi - 60) / 12);

        const source = audioCtx.createMediaElementSource(el);
        const gain = audioCtx.createGain();
        source.connect(gain); gain.connect(audioCtx.destination);
        
        el.play();
        activeVoices[midi] = { el, gain, triggerKey };
    }

    function stopNote(midi) {
        const voice = activeVoices[midi];
        if (voice) {
            voice.gain.gain.setTargetAtTime(0, audioCtx.currentTime, 0.03);
            setTimeout(() => { voice.el.pause(); voice.el.remove(); }, 100);
            delete activeVoices[midi];
        }
    }

    function init() {
        const pianoDiv = document.getElementById('piano');
        pianoDiv.innerHTML = '';

        noteData.forEach(n => {
            const div = document.createElement('div');
            // Logic to decide if it's a black key based on MIDI standard
            const isBlack = [1, 3, 6, 8, 10].includes(n.midi % 12);
            
            div.className = `key ${isBlack ? 'black' : ''}`;
            div.id = `midi-${n.midi}`;
            div.innerHTML = `
                <div class="swaram">${n.swaram}</div>
                <div class="kbd-hint">${n.keys[0].toUpperCase()}</div>
            `;

            div.onmousedown = () => { playNote(n.midi, 'mouse'); div.classList.add('active'); };
            div.onmouseup = () => { stopNote(n.midi); div.classList.remove('active'); };
            div.onmouseleave = () => { stopNote(n.midi); div.classList.remove('active'); };
            
            pianoDiv.appendChild(div);
        });

        window.onkeydown = (e) => {
            if (e.repeat) return;
            const key = e.key.toLowerCase();
            const note = noteData.find(n => n.keys.includes(key));
            if (note) {
                playNote(note.midi, key);
                document.getElementById(`midi-${note.midi}`).classList.add('active');
            }
        };

        window.onkeyup = (e) => {
            const key = e.key.toLowerCase();
            const note = noteData.find(n => n.keys.includes(key));
            if (note) {
                stopNote(note.midi);
                document.getElementById(`midi-${note.midi}`).classList.remove('active');
            }
        };
        document.getElementById('msg').innerText = "Organized Swaram Map Loaded.";
    }

    window.onclick = () => {
        if (audioCtx.state === 'suspended') audioCtx.resume();
        init();
    }, { once: true };
}
</script>
</body>
</html>