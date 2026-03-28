<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Swaram Web Graph Monitor</title>
    <style>
        body { background: #121212; color: #eee; font-family: 'Courier New', monospace; text-align: center; padding: 20px; }
        .graph-container { position: relative; width: 90%; max-width: 1000px; height: 150px; margin: 50px auto; background: #1a1a1a; border: 2px solid #333; border-radius: 8px; }
        .note-marker { position: absolute; height: 100%; width: 1px; background: #333; top: 0; }
        .note-label { position: absolute; top: -25px; transform: translateX(-50%); font-size: 14px; color: #666; font-weight: bold; }
        
        #live-indicator { 
            position: absolute; bottom: 0; left: 0; width: 4px; height: 100%; 
            background: #00ffcc; box-shadow: 0 0 15px #00ffcc; 
            transition: left 0.05s ease-out; z-index: 5;
            display: none;
        }
        .center-line { position: absolute; top: 0; left: 0; width: 100%; height: 2px; background: rgba(0,255,204,0.1); top: 50%; }
        #freq-display { font-size: 40px; color: #00ffcc; margin-top: 20px; }
        #note-name { font-size: 20px; color: #f1c40f; }
        button { padding: 15px 40px; background: #00ffcc; color: #000; border: none; border-radius: 5px; cursor: pointer; font-weight: bold; }
    </style>
</head>
<body>

    <h1>Pitch Spectrum Monitor</h1>
    
    <div class="graph-container" id="graph">
        <div id="live-indicator"></div>
        <div class="center-line"></div>
        </div>

    <div id="freq-display">0.0 Hz</div>
    <div id="note-name">---</div>
    <br>
    <button id="startBtn">START LIVE FEED</button>

<script>
{
    // Musical scale logic
    const notes = ["C", "C#", "D", "D#", "E", "F", "F#", "G", "G#", "A", "A#", "B"];
    const minFreq = 130; // C3
    const maxFreq = 523; // C5
    
    const graph = document.getElementById('graph');
    const indicator = document.getElementById('live-indicator');
    const freqText = document.getElementById('freq-display');
    const noteText = document.getElementById('note-name');

    // 1. Draw the Note Graph
    function drawGrid() {
        for (let m = 48; m <= 72; m++) { // MIDI notes from C3 to C5
            const f = 440 * Math.pow(2, (m - 69) / 12);
            if (f < minFreq || f > maxFreq) continue;
            
            const pos = (Math.log2(f / minFreq) / Math.log2(maxFreq / minFreq)) * 100;
            const marker = document.createElement('div');
            marker.className = 'note-marker';
            marker.style.left = pos + '%';
            
            const label = document.createElement('div');
            label.className = 'note-label';
            label.innerText = notes[m % 12] + (Math.floor(m / 12) - 1);
            label.style.left = pos + '%';
            
            graph.appendChild(marker);
            graph.appendChild(label);
        }
    }

    // 2. Audio Processing
    let audioCtx, analyser;
    
    async function start() {
        audioCtx = new (window.AudioContext || window.webkitAudioContext)();
        analyser = audioCtx.createAnalyser();
        analyser.fftSize = 2048;

        const stream = await navigator.mediaDevices.getUserMedia({ audio: true });
        audioCtx.createMediaStreamSource(stream).connect(analyser);
        
        indicator.style.display = 'block';
        document.getElementById('startBtn').style.display = 'none';
        process();
    }

    function process() {
        const buffer = new Float32Array(analyser.fftSize);
        analyser.getFloatTimeDomainData(buffer);
        const freq = getPitch(buffer, audioCtx.sampleRate);

        if (freq > minFreq && freq < maxFreq) {
            // Logarithmic mapping of frequency to X-position
            const pos = (Math.log2(freq / minFreq) / Math.log2(maxFreq / minFreq)) * 100;
            indicator.style.left = pos + '%';
            
            freqText.innerText = freq.toFixed(1) + " Hz";
            
            // Find closest note name
            const midi = Math.round(12 * Math.log2(freq / 440) + 69);
            noteText.innerText = "Current Note: " + notes[midi % 12];
        }
        requestAnimationFrame(process);
    }

    // Auto-correlation algorithm
    function getPitch(buf, sr) {
        let rms = 0;
        for (let i=0; i<buf.length; i++) rms += buf[i]*buf[i];
        if (Math.sqrt(rms/buf.length) < 0.01) return -1;

        let r1=0, r2=buf.length-1, thres=0.2;
        for (let i=0; i<buf.length/2; i++) if (Math.abs(buf[i])<thres) { r1=i; break; }
        for (let i=1; i<buf.length/2; i++) if (Math.abs(buf[buf.length-i])<thres) { r2=buf.length-i; break; }
        buf = buf.slice(r1,r2);

        let c = new Array(buf.length).fill(0);
        for (let i=0; i<buf.length; i++)
            for (let j=0; j<buf.length-i; j++) c[i] = c[i] + buf[j]*buf[j+i];

        let d=0; while (c[d]>c[d+1]) d++;
        let maxval=-1, maxpos=-1;
        for (let i=d; i<buf.length; i++) {
            if (c[i] > maxval) { maxval=c[i]; maxpos=i; }
        }
        return sr / maxpos;
    }

    drawGrid();
    document.getElementById('startBtn').onclick = start;
}
</script>
</body>
</html>