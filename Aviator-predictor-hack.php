<?php
// যদি কোনো POST বা GET রিকোয়েস্ট হ্যান্ডেল করতে চান, তা এখানে রাখতে পারেন।
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Aviator Ultimate V5</title>
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; }
        body {
            background-color: #000;
            color: white;
            font-family: 'Consolas', monospace;
            height: 100vh; overflow: hidden;
            display: flex; flex-direction: column;
        }

        /* পাসওয়ার্ড স্ক্রিনের ডিজাইন */
        #loginScreen {
            position: fixed; inset: 0; z-index: 1000;
            background: #000000;
            display: flex; flex-direction: column;
            justify-content: center; align-items: center;
            padding: 20px;
            overflow-y: auto;
        }
        
        .plane-logo {
            width: 170px;
            height: auto;
            margin-bottom: 15px;
            border-radius: 10px;
        }

        .login-instruction {
            color: #ff334b;
            font-size: 14px;
            margin-bottom: 20px;
            text-align: center;
            font-weight: bold;
            text-shadow: 0 0 8px rgba(255,0,51,0.4);
        }

        .login-card {
            background: #080808; 
            text-align: center; 
            width: 100%; 
            max-width: 380px;
        }

        .login-card input {
            width: 100%; padding: 15px; margin-bottom: 15px;
            background: #000; border: 3px solid #ff0033; color: #ff0033;
            text-align: center; font-size: 16px; font-weight: bold; border-radius: 10px; outline: none;
            box-shadow: inset 0 0 10px rgba(255,0,51,0.3);
        }

        .custom-btn {
            background: #0a0a0a; color: #ff0033; border: 3px solid #ff0033; width: 100%;
            padding: 14px; border-radius: 10px; font-weight: bold; cursor: pointer;
            text-decoration: none; display: block; margin-bottom: 12px; font-size: 13px;
            box-shadow: 0 0 12px rgba(255,0,51,0.3);
            transition: 0.2s;
        }
        .custom-btn:hover { background: #ff0033; color: #fff; }

        .login-main-btn {
            background: linear-gradient(to right, #ff0033, #99001f); color: white; border: 3px solid #ff334b; width: 100%;
            padding: 15px; border-radius: 10px; font-weight: bold; cursor: pointer;
            font-size: 16px; margin-bottom: 15px; box-shadow: 0 0 18px rgba(255,0,51,0.6);
        }

        .info-box-border {
            border: 3px solid #ff0033;
            border-radius: 10px;
            padding: 12px;
            margin-bottom: 15px;
            font-size: 11px;
            color: #ddd;
            line-height: 1.4;
            background: #050505;
            box-shadow: inset 0 0 12px rgba(255,0,51,0.25), 0 0 10px rgba(255,0,51,0.2);
            font-weight: bold;
        }

        .btn-row {
            display: flex;
            gap: 10px;
        }

        /* মেইন অ্যাপ ডিজাইন */
        #mainApp { display: none; flex-direction: column; height: 100%; }
        .top-bar {
            background-color: #0c0c0c; padding: 10px 15px;
            display: flex; justify-content: space-between;
            font-size: 14px; color: #ff0033; font-weight: bold;
            border-bottom: 2px solid #222;
        }
        .app-container { flex-grow: 1; padding: 20px; display: flex; flex-direction: column; gap: 15px; overflow-y: auto; }
        
        .game-window {
            position: relative; background: #000000;
            height: 230px; border-radius: 12px; border: 3px solid #ff0033;
            display: flex; justify-content: center; align-items: center; overflow: hidden;
            flex-shrink: 0;
            box-shadow: 0 0 15px rgba(255,0,51,0.3);
        }
        #multiplierText { font-size: 70px; font-weight: bold; z-index: 10; color: #fff; text-shadow: 0 0 20px rgba(255,0,51,0.5); }
        
        #planeBox { 
            position: absolute; bottom: 20px; left: -100px; width: 90px; z-index: 5; 
            transition: 0.1s linear;
        }
        .game-plane-img {
            width: 100%;
            height: auto;
            border-radius: 6px;
        }

        /* হ্যাকিং স্টাইল টার্মিনাল বক্স */
        .terminal-box {
            background-color: #050a05; 
            border: 3px solid #00ff00; 
            border-radius: 12px;
            padding: 15px; 
            height: 140px; 
            overflow-y: auto; 
            color: #00ff00; 
            font-size: 12px;
            box-shadow: inset 0 0 15px rgba(0, 255, 0, 0.25), 0 0 12px rgba(0, 255, 0, 0.4);
            position: relative;
            flex-shrink: 0;
        }

        .terminal-box::after {
            content: "";
            position: absolute; inset: 0;
            background: linear-gradient(rgba(0,0,0,0) 50%, rgba(0, 255, 0, 0.03) 50%);
            background-size: 100% 3px;
            pointer-events: none;
        }

        .log-line { display: flex; align-items: center; margin-bottom: 6px; text-shadow: 0 0 5px rgba(0,255,0,0.5); }
        .log-icon { width: 8px; height: 8px; border-radius: 50%; background: #00ff00; box-shadow: 0 0 8px #00ff00; margin-right: 8px; animation: blinkIcon 0.8s infinite alternate; }

        @keyframes blinkIcon {
            0% { opacity: 0.3; }
            100% { opacity: 1; }
        }

        .main-btn {
            background-color: #ff0033; color: white; border: none;
            width: 100%; padding: 18px; font-size: 20px; font-weight: bold;
            border-radius: 12px; cursor: pointer; box-shadow: 0 0 20px rgba(255,0,51,0.5);
            flex-shrink: 0;
            border: 2px solid #ff334b;
        }
        .stats-area { display: grid; grid-template-columns: 1fr 1.1fr; gap: 15px; flex-shrink: 0; }
        .win-box { background-color: #0c0c0c; border: 3px solid #ff0033; border-radius: 12px; padding: 15px; text-align: center; box-shadow: 0 0 10px rgba(255,0,51,0.2); }
        .win-percent { font-size: 38px; font-weight: bold; color: #00ff00; text-shadow: 0 0 8px rgba(0,255,0,0.4); }
        .s-link { padding: 14px; border-radius: 10px; text-align: center; text-decoration: none; color: white; font-weight: bold; display: block; margin-bottom: 5px; font-size: 12px; border: 2px solid rgba(255,255,255,0.2); }

        /* ঘূর্ণয়মান চাকা ও অ্যানিমেশন বক্স */
        .marked-matrix-box {
            background-color: #030803;
            border: 3px solid #00ff00;
            border-radius: 12px;
            height: 160px;
            padding: 10px;
            position: relative;
            overflow: hidden;
            box-shadow: inset 0 0 20px rgba(0, 255, 0, 0.3), 0 0 15px rgba(0, 255, 0, 0.5);
            flex-shrink: 0;
            display: flex;
            align-items: center;
            justify-content: space-between;
        }

        .cyber-wheel-container {
            width: 120px;
            height: 120px;
            position: relative;
            display: flex;
            align-items: center;
            justify-content: center;
            flex-shrink: 0;
        }

        .outer-ring {
            position: absolute;
            width: 100%;
            height: 100%;
            border: 3px dashed rgba(0, 255, 0, 0.8);
            border-radius: 50%;
            animation: spinClockwise 8s linear infinite;
        }

        .inner-ring {
            position: absolute;
            width: 75px;
            height: 75px;
            border: 3px solid #ff0033;
            border-top: 3px solid transparent;
            border-radius: 50%;
            animation: spinCounter 4s linear infinite;
            box-shadow: 0 0 12px rgba(255, 0, 51, 0.6);
        }

        .center-core {
            width: 30px;
            height: 30px;
            background: rgba(0, 255, 0, 0.2);
            border: 3px solid #00ff00;
            border-radius: 50%;
            box-shadow: 0 0 15px #00ff00;
            animation: pulseCore 1s ease-in-out infinite alternate;
            z-index: 2;
        }

        @keyframes spinClockwise {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }

        @keyframes spinCounter {
            0% { transform: rotate(360deg); }
            100% { transform: rotate(0deg); }
        }

        @keyframes pulseCore {
            0% { transform: scale(0.9); opacity: 0.6; }
            100% { transform: scale(1.1); opacity: 1; }
        }

        .matrix-info-panel {
            flex-grow: 1;
            padding-left: 12px;
            display: flex;
            flex-direction: column;
            justify-content: center;
            z-index: 2;
        }

        .matrix-top-bar {
            font-size: 10px;
            color: #ff0033;
            font-weight: bold;
            border-bottom: 2px dashed rgba(255,0,51,0.7);
            padding-bottom: 4px;
            margin-bottom: 6px;
            display: flex;
            justify-content: space-between;
        }

        .matrix-scrolling-text {
            font-size: 10px;
            color: #00ff00;
            line-height: 1.4;
            font-family: 'Consolas', monospace;
            text-shadow: 0 0 5px rgba(0,255,0,0.7);
        }
    </style>
</head>
<body>

    <div id="loginScreen">
        <img src="https://iili.io/nARaC2p.md.jpg" alt="Aviator Logo" class="plane-logo">

        <div class="login-instruction">You need a password to open hack</div>

        <div class="login-card">
            <input type="password" id="passkey" placeholder="Enter Password Here">
            <button class="login-main-btn" onclick="playClickSound(); checkPass()">Login</button>
            
            <div class="info-box-border">
                ⚡ সার্ভার বাইপাস হ্যাকটি আনলক করতে ফ্রি পাসওয়ার্ড প্রয়োজন? নিচের <span style="color: #ff0033;">"Get Password"</span> বাটনে ক্লিক করুন অথবা সরাসরি ডেভেলপার টিমের সাথে যোগাযোগ করুন!
            </div>

            <a href="https://t.me/EHRUMON" target="_blank" class="custom-btn" onclick="playClickSound()">Get Password</a>

            <div class="btn-row">
                <a href="https://t.me/+yP6jdlXJqq4wZGVl" target="_blank" class="custom-btn" style="margin-bottom:0;" onclick="playClickSound()">Join Telegram</a>
                <a href="https://t.me/XrootCodeX" target="_blank" class="custom-btn" style="margin-bottom:0;" onclick="playClickSound()">Customer Care</a>
            </div>

            <p id="errorMsg" style="color: #ff334b; font-size: 12px; margin-top: 10px; font-weight: bold;"></p>
        </div>
    </div>

    <div id="mainApp">
        <header class="top-bar">
            <span> Vip Aviator predictor </span>
            <span style="color:#00ff00">● SERVER CONNECTED</span>
        </header>

        <div class="app-container">
            <div class="game-window">
                <div id="multiplierText">1.00x</div>
                <div id="planeBox">
                    <img src="https://iili.io/nARvEAX.md.jpg" alt="Plane" class="game-plane-img">
                </div>
            </div>

            <div class="terminal-box" id="hackLogs">
                <div class="log-line"><div class="log-icon"></div><div>> SYSTEM_IDLE: READY</div></div>
            </div>

            <button class="main-btn" id="actionBtn" onclick="playClickSound(); startHack()">GET SERVER DATA</button>
            
            <div class="stats-area">
                <div class="win-box">
                    <div style="font-size: 10px; color: #888;">WIN CHANCE</div>
                    <div class="win-percent" id="winChanceDisp">0%</div>
                </div>
                <div>
                    <a href="https://t.me/ehgrupff" target="_blank" class="s-link" style="background:#0088cc" onclick="playClickSound()">TELEGRAM</a>
                    <a href="https://t.me/+yP6jdlXJqq4wZGVl" target="_blank" class="s-link" style="background:#2a2a2a" onclick="playClickSound()">DEVELOPER</a>
                </div>
            </div>

            <div class="marked-matrix-box">
                <div class="cyber-wheel-container">
                    <div class="outer-ring"></div>
                    <div class="inner-ring"></div>
                    <div class="center-core"></div>
                </div>

                <div class="matrix-info-panel">
                    <div class="matrix-top-bar">
                        <span>RADAR SHIELD v5.0</span>
                        <span style="color: #00ff00; animation: blinkIcon 1s infinite;">● ACTIVE</span>
                    </div>
                    <div class="matrix-scrolling-text" id="matrixStream">
                        > [RADAR]: Scanning active sockets...<br>
                        > [ENCRYPTION]: SHA-256 bypass engaged<br>
                        > [NODE]: Target server locked [OK]
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- ব্যাকগ্রাউন্ড মিউজিক প্লেয়ার -->
    <audio id="bgMusic" loop>
        <source src="https://files.catbox.moe/qj5oeb.mp3" type="audio/mp3">
    </audio>

    <!-- বাটন ক্লিকের জন্য সাউন্ড ইফেক্ট -->
    <audio id="clickSound">
        <source src="https://assets.mixkit.co/active_storage/sfx/2568/2568-preview.mp3" type="audio/mp3">
    </audio>

    <script>
        function playClickSound() {
            var clickAudio = document.getElementById('clickSound');
            clickAudio.currentTime = 0;
            clickAudio.play().catch(error => console.log("Click sound blocked"));
        }

        function checkPass() {
            const pass = document.getElementById('passkey').value;
            const error = document.getElementById('errorMsg');
            if(pass === "zxcpoi") {
                document.getElementById('loginScreen').style.display = 'none';
                document.getElementById('mainApp').style.display = 'flex';
                
                var music = document.getElementById('bgMusic');
                music.play().catch(error => console.log("Audio autoplay restricted"));
            } else {
                error.innerText = "পাসওয়ার্ড ভুল! সঠিক পাসওয়ার্ড দিন।";
            }
        }

        document.addEventListener('click', function() {
            var music = document.getElementById('bgMusic');
            if (music.paused) {
                music.play().catch(error => console.log("Audio play blocked"));
            }
        }, { once: true });

        const liveMatrixPool = [
            "> [BYPASS]: Injecting shellcode...",
            "> [DECRYPT]: RSA handshake verified",
            "> [TRACE]: Masking proxy tunnel...",
            "> [PACKET]: Intercepting data stream...",
            "> [AI_CORE]: Parsing crash vectors...",
            "> [SECURITY]: Root access granted!"
        ];

        setInterval(() => {
            const matrixBox = document.getElementById('matrixStream');
            if(matrixBox) {
                let logA = liveMatrixPool[Math.floor(Math.random() * liveMatrixPool.length)];
                let logB = liveMatrixPool[Math.floor(Math.random() * liveMatrixPool.length)];
                let randomHex = "0x" + Math.floor(Math.random()*100000000).toString(16).toUpperCase();
                matrixBox.innerHTML = `${logA}<br>` +
                                      `> [HEX]: ${randomHex} [SECURE]<br>` +
                                      `${logB}`;
            }
        }, 1500);

        let isRunning = false;
        function startHack() {
            if (isRunning) return;
            isRunning = true;
            const btn = document.getElementById('actionBtn');
            const logs = document.getElementById('hackLogs');
            const chanceDisp = document.getElementById('winChanceDisp');
            const multText = document.getElementById('multiplierText');
            const plane = document.getElementById('planeBox');
            
            btn.disabled = true; btn.innerText = "BYPASSING...";
            logs.innerHTML = ""; multText.style.color = "white"; 
            plane.style.left = "-100px"; plane.style.bottom = "20px";
            
            const newChance = Math.floor(Math.random() * (99 - 88 + 1)) + 88;
            chanceDisp.innerText = newChance + "%";
            
            addLog("CONNECTING TO CRASH SERVER...");
            setTimeout(() => addLog("INJECTING EXPLOIT SCRIPTS..."), 700);
            setTimeout(() => addLog("BYPASSING RSA SECURITY..."), 1400);
            setTimeout(() => addLog("DECRYPTING HASH PACKETS..."), 2100);
            
            setTimeout(() => {
                let target = (Math.random() * (4.20 - 1.60) + 1.60).toFixed(2);
                addLog(`[+] EXPLOIT SUCCESS: ${target}x`);
                let current = 1.00;
                
                const timer = setInterval(() => {
                    if(current >= target) {
                        clearInterval(timer); 
                        multText.style.color = "#ff0033";
                        btn.disabled = false; 
                        btn.innerText = "GET SERVER DATA"; 
                        isRunning = false;
                    } else {
                        current += 0.02; 
                        multText.innerText = current.toFixed(2) + "x";
                        plane.style.left = (current * 35 - 30) + "px";
                        plane.style.bottom = (current * 25 + 20) + "px";
                    }
                }, 50);
            }, 2800);
        }

        function addLog(txt) {
            const l = document.getElementById('hackLogs');
            l.innerHTML += `<div class="log-line"><div class="log-icon"></div><div>> ${txt}</div></div>`;
            l.scrollTop = l.scrollHeight;
        }
    </script>
</body>
</html>
