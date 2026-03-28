<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Harmonium - Single Interface</title>
    <style>
        body { background: #1a1a1a; color: white; font-family: sans-serif; text-align: center; padding: 20px; user-select: none; }
        .piano { display: flex; justify-content: center; margin: 20px auto; background: #333; padding: 15px; border-radius: 8px; width: fit-content; border: 4px solid #444; }
        .key { width: 50px; height: 180px; background: white; border: 1px solid #111; cursor: pointer; border-radius: 0 0 5px 5px; position: relative; color: #333; transition: background 0.1s; }
        .key.black { width: 34px; height: 110px; background: #000; margin: 0 -17px; z-index: 2; border-radius: 0 0 3px 3px; color: white; }
        .key.active { background: #bdc3c7 !important; transform: translateY(2px); }
        .key.black.active { background: #444 !important; }
        .label { position: absolute; bottom: 10px; width: 100%; left: 0; font-size: 10px; pointer-events: none; }
        #msg { margin-top: 20px; font-family: monospace; color: #aaa; }
    </style>
</head>
<body>

    <h1>Harmonium Web</h1>
    <p>Click once to start, then use Mouse or Keys (A, W, S, E, D, F, T, G, Y, H, U, J)</p>

    <div class="piano" id="pianoContainer"></div>
    <div id="msg">Click anywhere to activate Audio</div>

<script>
{
    const audioCtx = new (window.AudioContext || window.webkitAudioContext)();
    const soundPath = 'harmonium-kannan-orig.wav';
    const activeVoices = {}; 

    const keys = [
        { n: "C", r: 1.00, k: "a" }, { n: "C#", r: 1.06, k: "w", b: true },
        { n: "D", r: 1.12, k: "s" }, { n: "D#", r: 1.19, k: "e", b: true },
        { n: "E", r: 1.26, k: "d" }, { n: "F", r: 1.33, k: "f" },
        { n: "F#", r: 1.41, k: "t", b: true }, { n: "G", r: 1.50, k: "g" },
        { n: "G#", r: 1.59, k: "y", b: true }, { n: "A", r: 1.68, k: "h" },
        { n: "A#", r: 1.78, k: "u", b: true }, { n: "B", r: 1.89, k: "j" }
    ];

    function playNote(item) {
        if (activeVoices[item.k]) return;
        if (audioCtx.state === 'suspended') audioCtx.resume();

        const el = new Audio(soundPath);
        el.loop = true;
        el.preservesPitch = false; 
        el.playbackRate = item.r;

        const source = audioCtx.createMediaElementSource(el);
        const gainNode = audioCtx.createGain();
        
        source.connect(gainNode);
        gainNode.connect(audioCtx.destination);

        el.play();
        activeVoices[item.k] = { el, gainNode };
    }

    function stopNote(keyStr) {
        const voice = activeVoices[keyStr];
        if (voice) {
            voice.gainNode.gain.setTargetAtTime(0, audioCtx.currentTime, 0.03);
            setTimeout(() => {
                voice.el.pause();
                voice.el.remove();
            }, 100);
            delete activeVoices[keyStr];
        }
    }

    // This creates the UI ONCE on page load
    function createUI() {
        const container = document.getElementById('pianoContainer');
        container.innerHTML = ''; // Safety clear
        keys.forEach(item => {
            const div = document.createElement('div');
            div.className = `key ${item.b ? 'black' : ''}`;
            div.id = `k-${item.k}`;
            div.innerHTML = `<span class="label">${item.k.toUpperCase()}</span>`;
            
            div.onmousedown = (e) => { e.preventDefault(); playNote(item); div.classList.add('active'); };
            div.onmouseup = () => { stopNote(item.k); div.classList.remove('active'); };
            div.onmouseleave = () => { stopNote(item.k); div.classList.remove('active'); };
            
            container.appendChild(div);
        });

        // Keyboard Events
        window.addEventListener('keydown', (e) => {
            if (e.repeat) return; // Prevent stuttering when holding key
            const key = keys.find(k => k.k === e.key.toLowerCase());
            if (key) {
                playNote(key);
                const el = document.getElementById(`k-${key.k}`);
                if (el) el.classList.add('active');
            }
        });
        window.addEventListener('keyup', (e) => {
            const key = keys.find(k => k.k === e.key.toLowerCase());
            if (key) {
                stopNote(key.k);
                const el = document.getElementById(`k-${key.k}`);
                if (el) el.classList.remove('active');
            }
        });
    }

    // 1. Run UI creation immediately
    createUI();

    // 2. Resume AudioContext only on the first click
    window.addEventListener('click', () => {
        if (audioCtx.state === 'suspended') {
            audioCtx.resume().then(() => {
                document.getElementById('msg').innerText = "Audio Active. Enjoy!";
            });
        }
    }, { once: true });
}
</script>
</body>
</html>