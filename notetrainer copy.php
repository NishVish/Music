<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Swaram Spider - Ear Trainer</title>
    <style>
        body { background: #121212; color: #eee; font-family: sans-serif; text-align: center; }
        .container { position: relative; width: 500px; height: 500px; margin: 0 auto; }
        canvas { background: #1a1a1a; border-radius: 50%; border: 2px solid #333; cursor: crosshair; }
        #stats { margin-top: 20px; font-family: monospace; color: #00ffcc; font-size: 1.2rem; min-height: 1.5em; }
        .controls { margin: 20px; }
        button { padding: 12px 24px; border: none; border-radius: 5px; cursor: pointer; font-weight: bold; margin: 5px; transition: 0.2s; }
        #startBtn { background: #f1c40f; color: #000; }
        .ref-btn { background: #444; color: #fff; font-size: 12px; }
        .ref-btn:hover { background: #666; }
        .ref-btn.playing { background: #00ffcc; color: #000; }
    </style>
</head>
<body>

    <h1>Spider Ear Trainer</h1>
    <p>Click a <b>Swaram Button</b> to hear it, then sing/play to match the web.</p>
    
    <div class="controls">
        <button id="startBtn">1. START MICROPHONE</button>
        <div id="refButtons">
            </div>
    </div>

    <div class="container">
        <canvas id="spiderCanvas" width="500" height="500"></canvas>
    </div>

    <div id="stats">Ready...</div>
<!DOCTYPE html>
<html>
<head>
<style>
    table {
        font-family: Arial, sans-serif;
        border-collapse: collapse;
        width: 100%;
        max-width: 800px;
        margin: 20px auto;
        background-color: #1a1a1a;
        color: #eee;
    }

    th {
        background-color: #333;
        color: #00ffcc;
        font-weight: bold;
        text-align: left;
        padding: 12px;
        border: 1px solid #444;
    }

    td {
        border: 1px solid #444;
        text-align: left;
        padding: 12px;
    }

    tr:nth-child(even) {
        background-color: #222;
    }

    tr:hover {
        background-color: #2a2a2a;
    }

    .swaram-col {
        font-weight: bold;
        color: #f1c40f;
    }
</style>
</head>
<body>

<table>
  <thead>
    <tr>
      <th>Swaram</th>
      <th>Note</th>
      <th>Target Frequency</th>
      <th>Pitch Range (Approx)</th>
    </tr>
  </thead>
  <tbody>
    <tr>
      <td class="swaram-col">S (Shadjam)</td>
      <td>C4</td>
      <td>261.63 Hz</td>
      <td>254 Hz – 270 Hz</td>
    </tr>
    <tr>
      <td class="swaram-col">R (Rishabham)</td>
      <td>D4</td>
      <td>293.66 Hz</td>
      <td>285 Hz – 302 Hz</td>
    </tr>
    <tr>
      <td class="swaram-col">G (Gandharam)</td>
      <td>E4</td>
      <td>329.63 Hz</td>
      <td>320 Hz – 339 Hz</td>
    </tr>
    <tr>
      <td class="swaram-col">M (Madhyamam)</td>
      <td>F4</td>
      <td>349.23 Hz</td>
      <td>340 Hz – 370 Hz</td>
    </tr>
    <tr>
      <td class="swaram-col">P (Panchamam)</td>
      <td>G4</td>
      <td>392.00 Hz</td>
      <td>380 Hz – 410 Hz</td>
    </tr>
    <tr>
      <td class="swaram-col">D (Dhaivatham)</td>
      <td>A4</td>
      <td>440.00 Hz</td>
      <td>425 Hz – 455 Hz</td>
    </tr>
    <tr>
      <td class="swaram-col">N (Nishadam)</td>
      <td>B4</td>
      <td>4493.88 Hz</td>
      <td>480 Hz – 510 Hz</td>
    </tr>
    <tr>
      <td class="swaram-col">Ṡ (Tara S)</td>
      <td>C5</td>
      <td>523.25 Hz</td>
      <td>511 Hz – 540 Hz</td>
    </tr>
  </tbody>
</table>

</body>
</html>
<script>
{
    const canvas = document.getElementById('spiderCanvas');
    const ctx = canvas.getContext('2d');
    const stats = document.getElementById('stats');
    
    const swarams = [
        { label: "S", freq: 261.63 },
        { label: "R", freq: 293.66 },
        { label: "G", freq: 329.63 },
        { label: "M", freq: 349.23 },
        { label: "P", freq: 392.00 },
        { label: "D", freq: 440.00 },
        { label: "N", freq: 493.88 }
    ];

    let audioCtx, analyser;
    let intensities = new Array(swarams.length).fill(0);
    let referenceOscillator = null;

    // Generate Reference Buttons
    const btnContainer = document.getElementById('refButtons');
    swarams.forEach((s, i) => {
        const btn = document.createElement('button');
        btn.className = 'ref-btn';
        btn.innerText = `Hear ${s.label}`;
        btn.onmousedown = () => playReference(s.freq, btn);
        btn.onmouseup = stopReference;
        btn.onmouseleave = stopReference;
        btnContainer.appendChild(btn);
    });

    function playReference(freq, btn) {
        if (!audioCtx) audioCtx = new (window.AudioContext || window.webkitAudioContext)();
        if (audioCtx.state === 'suspended') audioCtx.resume();
        
        stopReference(); // Clean up old one

        const osc = audioCtx.createOscillator();
        const gain = audioCtx.createGain();
        
        osc.type = 'triangle'; // Smoother sound like a harmonium
        osc.frequency.setValueAtTime(freq, audioCtx.currentTime);
        
        gain.gain.setValueAtTime(0, audioCtx.currentTime);
        gain.gain.linearRampToValueAtTime(0.2, audioCtx.currentTime + 0.1);
        
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

        // Draw Web Background
        ctx.strokeStyle = "#333";
        ctx.lineWidth = 1;
        for (let j = 1; j <= 4; j++) {
            ctx.beginPath();
            for (let i = 0; i < swarams.length; i++) {
                const angle = (i / swarams.length) * Math.PI * 2 - Math.PI / 2;
                const x = centerX + Math.cos(angle) * (radius * (j / 4));
                const y = centerY + Math.sin(angle) * (radius * (j / 4));
                i === 0 ? ctx.moveTo(x, y) : ctx.lineTo(x, y);
            }
            ctx.closePath();
            ctx.stroke();
        }

        // Labels
        swarams.forEach((s, i) => {
            const angle = (i / swarams.length) * Math.PI * 2 - Math.PI / 2;
            ctx.fillStyle = "#888";
            ctx.font = "bold 16px sans-serif";
            ctx.fillText(s.label, centerX + Math.cos(angle) * (radius + 35) - 5, centerY + Math.sin(angle) * (radius + 35) + 5);
        });

        // Live Web
        ctx.beginPath();
        ctx.strokeStyle = "#00ffcc";
        ctx.fillStyle = "rgba(0, 255, 204, 0.2)";
        ctx.lineWidth = 3;

        for (let i = 0; i < swarams.length; i++) {
            const angle = (i / swarams.length) * Math.PI * 2 - Math.PI / 2;
            intensities[i] *= 0.96; 
            const val = Math.max(15, intensities[i] * radius);
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
            stats.innerText = `Live: ${freq.toFixed(1)} Hz`;
            swarams.forEach((s, i) => {
                const centsOff = Math.abs(1200 * Math.log2(freq / s.freq));
                if (centsOff < 40) {
                    const power = 1 - (centsOff / 40);
                    intensities[i] = Math.max(intensities[i], power);
                }
            });
        }
        requestAnimationFrame(processAudio);
    }

    function autoCorrelate(buf, sr) {
        let rms = 0;
        for (let i=0; i<buf.length; i++) rms += buf[i]*buf[i];
        if (Math.sqrt(rms/buf.length) < 0.015) return -1;
        let r1=0, r2=buf.length-1, thres=0.2;
        for (let i=0; i<buf.length/2; i++) if (Math.abs(buf[i])<thres) { r1=i; break; }
        for (let i=1; i<buf.length/2; i++) if (Math.abs(buf[buf.length-i])<thres) { r2=buf.length-i; break; }
        buf = buf.slice(r1,r2);
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