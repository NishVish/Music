<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Swaram Spider - 12 Note Ear Trainer</title>
    <style>
        body { background: #121212; color: #eee; font-family: 'Inter', sans-serif; text-align: center; margin: 0; padding: 20px; }
        .container { position: relative; width: 500px; height: 500px; margin: 0 auto; }
        canvas { background: #1a1a1a; border-radius: 50%; border: 2px solid #333; cursor: crosshair; box-shadow: 0 0 20px rgba(0,0,0,0.5); }
        #stats { margin-top: 20px; font-family: monospace; color: #00ffcc; font-size: 1.4rem; min-height: 1.5em; text-shadow: 0 0 5px #00ffcc; }
        .controls { margin: 20px auto; max-width: 800px; }
        button { padding: 12px 24px; border: none; border-radius: 5px; cursor: pointer; font-weight: bold; margin: 5px; transition: 0.2s; }
        #startBtn { background: #f1c40f; color: #000; font-size: 1.1rem; width: 100%; max-width: 300px; margin-bottom: 20px; }
        #refButtons { display: grid; grid-template-columns: repeat(6, 1fr); gap: 5px; }
        .ref-btn { background: #333; color: #fff; font-size: 11px; padding: 10px 5px; border: 1px solid #444; }
        .ref-btn:hover { background: #555; }
        .ref-btn.playing { background: #00ffcc; color: #000; border-color: #fff; box-shadow: 0 0 10px #00ffcc; }

        table { font-family: Arial, sans-serif; border-collapse: collapse; width: 100%; max-width: 800px; margin: 40px auto; background-color: #1a1a1a; color: #eee; font-size: 13px; }
        th { background-color: #333; color: #00ffcc; padding: 12px; border: 1px solid #444; }
        td { border: 1px solid #444; padding: 10px; }
        .swaram-col { font-weight: bold; color: #f1c40f; }
        .natural { color: #fff; }
        .komal { color: #ff6b6b; } /* Variants */
    </style>
</head>
<body>

    <h1>Spider Ear Trainer <small>(12 Swarasthanas)</small></h1>
    <p>Activate mic and sing. The spider web grows as you hit the correct frequency.</p>
    
    <div class="controls">
        <button id="startBtn">1. START MICROPHONE</button>
        <div id="refButtons"></div>
    </div>

    <div class="container">
        <canvas id="spiderCanvas" width="500" height="500"></canvas>
    </div>

    <div id="stats">Ready...</div>

    <table>
      <thead>
        <tr>
          <th>Swaram (Abbr)</th>
          <th>Swarasthana (Full Name)</th>
          <th>Western Note</th>
          <th>Frequency (Hz)</th>
        </tr>
      </thead>
      <tbody id="tableBody"></tbody>
    </table>

<script>
{
    const canvas = document.getElementById('spiderCanvas');
    const ctx = canvas.getContext('2d');
    const stats = document.getElementById('stats');
    
    // 12 Swarasthanas Frequency Mapping (C4 base)
    const swarams = [
        { label: "S", full: "Shadjam", note: "C4", freq: 261.63 },
        { label: "r1", full: "Suddha Rishabham", note: "Db4", freq: 277.18 },
        { label: "R2", full: "Chatushruti Rishabham", note: "D4", freq: 293.66 },
        { label: "g2", full: "Sadharana Gandharam", note: "Eb4", freq: 311.13 },
        { label: "G3", full: "Antara Gandharam", note: "E4", freq: 329.63 },
        { label: "M1", full: "Suddha Madhyamam", note: "F4", freq: 349.23 },
        { label: "M2", full: "Prati Madhyamam", note: "F#4", freq: 369.99 },
        { label: "P", full: "Panchamam", note: "G4", freq: 392.00 },
        { label: "d1", full: "Suddha Dhaivatham", note: "Ab4", freq: 415.30 },
        { label: "D2", full: "Chatushruti Dhaivatham", note: "A4", freq: 440.00 },
        { label: "n2", full: "Kaishiki Nishadam", note: "Bb4", freq: 466.16 },
        { label: "N3", full: "Kakali Nishadam", note: "B4", freq: 493.88 }
    ];

    let audioCtx, analyser;
    let intensities = new Array(swarams.length).fill(0);
    let referenceOscillator = null;

    // Populate Table and Buttons
    const btnContainer = document.getElementById('refButtons');
    const tableBody = document.getElementById('tableBody');

    swarams.forEach((s, i) => {
        // Buttons
        const btn = document.createElement('button');
        btn.className = 'ref-btn';
        btn.innerText = s.label;
        btn.onmousedown = () => playReference(s.freq, btn);
        btn.onmouseup = stopReference;
        btn.onmouseleave = stopReference;
        btnContainer.appendChild(btn);

        // Table Rows
        const row = `<tr>
            <td class="swaram-col">${s.label}</td>
            <td>${s.full}</td>
            <td>${s.note}</td>
            <td>${s.freq.toFixed(2)} Hz</td>
        </tr>`;
        tableBody.innerHTML += row;
    });

    function playReference(freq, btn) {
        if (!audioCtx) audioCtx = new (window.AudioContext || window.webkitAudioContext)();
        if (audioCtx.state === 'suspended') audioCtx.resume();
        stopReference();

        const osc = audioCtx.createOscillator();
        const gain = audioCtx.createGain();
        osc.type = 'sawtooth'; // Richer harmonics for training
        osc.frequency.setValueAtTime(freq, audioCtx.currentTime);
        gain.gain.setValueAtTime(0, audioCtx.currentTime);
        gain.gain.linearRampToValueAtTime(0.15, audioCtx.currentTime + 0.05);
        osc.connect(gain);
        gain.connect(audioCtx.destination);
        osc.start();
        referenceOscillator = { osc, gain, btn };
        btn.classList.add('playing');
    }

    function stopReference() {
        if (referenceOscillator) {
            const { osc, gain, btn } = referenceOscillator;
            gain.gain.setTargetAtTime(0, audioCtx.currentTime, 0.05);
            setTimeout(() => osc.stop(), 100);
            btn.classList.remove('playing');
            referenceOscillator = null;
        }
    }

    function drawSpider() {
        const centerX = canvas.width / 2;
        const centerY = canvas.height / 2;
        const radius = 180;

        ctx.clearRect(0, 0, canvas.width, canvas.height);

        // Web Background
        ctx.strokeStyle = "#333";
        ctx.lineWidth = 1;
        for (let j = 1; j <= 5; j++) {
            ctx.beginPath();
            for (let i = 0; i < swarams.length; i++) {
                const angle = (i / swarams.length) * Math.PI * 2 - Math.PI / 2;
                const x = centerX + Math.cos(angle) * (radius * (j / 5));
                const y = centerY + Math.sin(angle) * (radius * (j / 5));
                i === 0 ? ctx.moveTo(x, y) : ctx.lineTo(x, y);
            }
            ctx.closePath();
            ctx.stroke();
        }

        // Axial Lines
        swarams.forEach((_, i) => {
            const angle = (i / swarams.length) * Math.PI * 2 - Math.PI / 2;
            ctx.beginPath();
            ctx.moveTo(centerX, centerY);
            ctx.lineTo(centerX + Math.cos(angle) * radius, centerY + Math.sin(angle) * radius);
            ctx.stroke();
        });

        // Labels
        swarams.forEach((s, i) => {
            const angle = (i / swarams.length) * Math.PI * 2 - Math.PI / 2;
            ctx.fillStyle = (s.label.length > 1 && s.label[0] === s.label[0].toLowerCase()) ? "#ff6b6b" : "#f1c40f";
            ctx.font = "bold 13px monospace";
            ctx.fillText(s.label, centerX + Math.cos(angle) * (radius + 25) - 8, centerY + Math.sin(angle) * (radius + 25) + 5);
        });

        // Live Performance Web
        ctx.beginPath();
        ctx.strokeStyle = "#00ffcc";
        ctx.fillStyle = "rgba(0, 255, 204, 0.25)";
        ctx.lineWidth = 3;

        for (let i = 0; i < swarams.length; i++) {
            const angle = (i / swarams.length) * Math.PI * 2 - Math.PI / 2;
            intensities[i] *= 0.98; // Decay
            const val = Math.max(10, intensities[i] * radius);
            const x = centerX + Math.cos(angle) * val;
            const y = centerY + Math.sin(angle) * val;
            i === 0 ? ctx.moveTo(x, y) : ctx.lineTo(x, y);
        }
        ctx.closePath();
        ctx.fill();
        ctx.stroke();

        requestAnimationFrame(drawSpider);
    }

    async function startMic() {
        if (!audioCtx) audioCtx = new (window.AudioContext || window.webkitAudioContext)();
        analyser = audioCtx.createAnalyser();
        analyser.fftSize = 2048;
        try {
            const stream = await navigator.mediaDevices.getUserMedia({ audio: true });
            audioCtx.createMediaStreamSource(stream).connect(analyser);
            document.getElementById('startBtn').style.display = 'none';
            processAudio();
        } catch (e) { stats.innerText = "Error: Need Mic Permission"; }
    }

    function processAudio() {
        const buffer = new Float32Array(analyser.fftSize);
        analyser.getFloatTimeDomainData(buffer);
        const freq = autoCorrelate(buffer, audioCtx.sampleRate);

        if (freq !== -1) {
            stats.innerText = `${freq.toFixed(1)} Hz`;
            swarams.forEach((s, i) => {
                // Check if frequency matches within 30 cents
                const centsOff = Math.abs(1200 * Math.log2(freq / s.freq));
                if (centsOff < 30) {
                    const power = 1 - (centsOff / 30);
                    intensities[i] = Math.max(intensities[i], power);
                }
            });
        }
        requestAnimationFrame(processAudio);
    }

    // Standard Auto-Correlation Pitch Detection
    function autoCorrelate(buf, sr) {
        let rms = 0;
        for (let i=0; i<buf.length; i++) rms += buf[i]*buf[i];
        if (Math.sqrt(rms/buf.length) < 0.01) return -1;
        let c = new Array(buf.length).fill(0);
        for (let i=0; i<buf.length; i++)
            for (let j=0; j<buf.length-i; j++) c[i] = c[i] + buf[j]*buf[j+i];
        let d=0; while (c[d]>c[d+1]) d++;
        let maxval=-1, maxpos=-1;
        for (let i=d; i<buf.length; i++) if (c[i]>maxval) { maxval=c[i]; maxpos=i; }
        return sr / maxpos;
    }

    drawSpider();
    document.getElementById('startBtn').onclick = startMic;
}
</script>
</body>
</html>