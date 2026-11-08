<?php
declare(strict_types=1);
error_reporting(0);
ini_set('display_errors', '0');

// আপনার অ্যাডমিন পাসওয়ার্ড
$admin_password = "483236"; 

$statusFile = __DIR__ . '/api_status.json';
$logFile = __DIR__ . '/api_logs.json';

// সেশন চালু করা (যাতে বারবার লগইন করা না লাগে)
session_start();

// ডিফল্ট স্ট্যাটাস ফাইল তৈরি (যদি না থাকে)
if (!file_exists($statusFile)) {
    $defaultData = [
        "is_active" => true,
        "notice_title" => "এপিআই সার্ভিস সাময়িকভাবে বন্ধ রয়েছে",
        "notice_message" => "এই এপিআই সার্ভিসটি বর্তমানে রক্ষণাবেক্ষণের কারণে বন্ধ রাখা হয়েছে। পরবর্তী আপডেট পেতে আমাদের টেলিগ্রাম চ্যানেলে যুক্ত থাকুন।",
        "telegram_channel" => "https://t.me/CobraX_Empire"
    ];
    file_put_contents($statusFile, json_encode($defaultData, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
}

// লগ ফাইল তৈরি (যদি না থাকে)
if (!file_exists($logFile)) {
    file_put_contents($logFile, json_encode([], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
}

$statusData = json_decode(file_get_contents($statusFile), true);
$logsData = json_decode(file_get_contents($logFile), true);
if (!is_array($logsData)) $logsData = [];

// লগইন প্রসেস
if (isset($_POST['password'])) {
    if ($_POST['password'] === $admin_password) {
        $_SESSION['admin_logged'] = true;
        header("Location: admin.php");
        exit;
    } else {
        $login_error = "ভুল পাসওয়ার্ড! আবার চেষ্টা করুন।";
    }
}

// লগআউট প্রসেস
if (isset($_GET['logout'])) {
    unset($_SESSION['admin_logged']);
    session_destroy();
    header("Location: admin.php");
    exit;
}

$is_logged_in = isset($_SESSION['admin_logged']) && $_SESSION['admin_logged'] === true;

$success_msg = "";
if ($is_logged_in && $_SERVER['REQUEST_METHOD'] === 'POST') {
    // লগ ক্লিয়ার করার অপশন
    if (isset($_POST['clear_logs'])) {
        file_put_contents($logFile, json_encode([], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
        $success_msg = "সকল ইউজার ও টার্গেট লগ সফলভাবে মুছে ফেলা হয়েছে!";
        $logsData = [];
    }
    
    // সেটিংস আপডেট
    if (isset($_POST['update_status'])) {
        $statusData['is_active'] = (isset($_POST['api_status']) && $_POST['api_status'] === '1') ? true : false;
        $statusData['notice_title'] = trim($_POST['notice_title']);
        $statusData['notice_message'] = trim($_POST['notice_message']);
        $statusData['telegram_channel'] = trim($_POST['telegram_channel']);

        file_put_contents($statusFile, json_encode($statusData, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
        $success_msg = "সফল! এপিআই স্ট্যাটাস ও নোটিশ আপডেট করা হয়েছে।";
        $statusData = json_decode(file_get_contents($statusFile), true);
    }
}

// অ্যানালিটিক্স হিসাব
$total_requests = count($logsData);
$unique_ips = count(array_unique(array_column($logsData, 'ip')));
?>
<!DOCTYPE html>
<html lang="bn">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Master API Control Panel</title>
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; font-family: 'Segoe UI', Tahoma, sans-serif; }
        body { background: #090d16; color: #f1f5f9; padding: 20px; }
        .container { max-width: 850px; margin: 40px auto; background: #111827; border: 1px solid #1f2937; border-radius: 16px; padding: 30px; box-shadow: 0 15px 30px rgba(0,0,0,0.6); }
        h1 { color: #f8fafc; font-size: 20px; margin-bottom: 20px; border-bottom: 1px solid #1f2937; padding-bottom: 12px; display: flex; justify-content: space-between; align-items: center; }
        .login-box { max-width: 400px; margin: 100px auto; background: #111827; padding: 30px; border-radius: 16px; border: 1px solid #1f2937; text-align: center; box-shadow: 0 15px 30px rgba(0,0,0,0.5); }
        input[type="password"], input[type="text"], textarea { width: 100%; padding: 12px 14px; background: #1f2937; border: 1px solid #374151; border-radius: 8px; color: #fff; margin-bottom: 15px; font-size: 14px; outline: none; }
        textarea { resize: vertical; height: 90px; }
        button { background: #2563eb; color: #fff; border: none; padding: 12px 20px; border-radius: 8px; font-weight: 600; cursor: pointer; transition: 0.2s; width: 100%; font-size: 15px; }
        button:hover { background: #1d4ed8; }
        .alert { background: rgba(16, 185, 129, 0.15); color: #34d399; padding: 12px; border-radius: 8px; margin-bottom: 20px; font-size: 14px; border: 1px solid rgba(16, 185, 129, 0.3); }
        .logout { color: #f43f5e; text-decoration: none; font-size: 14px; }
        label { cursor: pointer; color: #94a3b8; font-size: 13px; font-weight: 600; display: block; margin-bottom: 6px; }
        
        /* অন/অফ বাটন ডিজাইন */
        .status-box { background: #1f2937; padding: 18px 20px; border-radius: 12px; border: 1px solid #374151; margin-bottom: 20px; display: flex; justify-content: space-between; align-items: center; }
        .status-title { font-size: 15px; font-weight: 600; color: #fff; }
        .toggle-group { display: flex; gap: 10px; }
        .toggle-btn { padding: 8px 20px; border-radius: 8px; font-weight: 600; font-size: 14px; cursor: pointer; border: 1px solid #374151; background: #374151; color: #94a3b8; transition: 0.2s; width: auto; }
        .toggle-btn.active-on { background: #10b981; color: #fff; border-color: #059669; box-shadow: 0 0 10px rgba(16,185,129,0.4); }
        .toggle-btn.active-off { background: #ef4444; color: #fff; border-color: #dc2626; box-shadow: 0 0 10px rgba(239,68,68,0.4); }

        /* স্ট্যাটিস্টিকস কার্ড ও টেবিল */
        .stats-grid { display: grid; grid-template-columns: repeat(3, 1fr); gap: 15px; margin-bottom: 25px; }
        .stat-card { background: #1f2937; border: 1px solid #374151; padding: 15px; border-radius: 10px; text-align: center; }
        .stat-card h3 { font-size: 22px; color: #38bdf8; margin-bottom: 5px; }
        .stat-card p { font-size: 12px; color: #94a3b8; font-weight: 600; }
        table { width: 100%; border-collapse: collapse; margin-top: 10px; }
        th, td { padding: 10px; text-align: left; border-bottom: 1px solid #1f2937; font-size: 13px; }
        th { color: #94a3b8; font-weight: 600; }
        .clear-btn { background: #dc2626; width: auto; padding: 6px 14px; font-size: 12px; margin-bottom: 15px; }
        .clear-btn:hover { background: #b91c1c; }
    </style>
    <script>
        function setStatus(val) {
            document.getElementById('api_status_input').value = val;
            const btnOn = document.getElementById('btn_on');
            const btnOff = document.getElementById('btn_off');
            
            if(val === '1') {
                btnOn.className = "toggle-btn active-on";
                btnOff.className = "toggle-btn";
            } else {
                btnOn.className = "toggle-btn";
                btnOff.className = "toggle-btn active-off";
            }
        }
    </script>
</head>
<body>
    <div class="container">
        <?php if (!$is_logged_in): ?>
            <div class="login-box">
                <h2 style="margin-bottom: 20px; color:#fff;">Master Admin Login</h2>
                <?php if (isset($login_error)): ?>
                    <p style="color: #f43f5e; margin-bottom: 15px; font-size:13px;"><?php echo $login_error; ?></p>
                <?php endif; ?>
                <form method="POST">
                    <input type="password" name="password" placeholder="অ্যাডমিন পাসওয়ার্ড দিন (483236)" required>
                    <button type="submit">লগইন করুন</button>
                </form>
            </div>
        <?php else: ?>
            <h1>
                <span>🛡️ Master API Control Panel</span>
                <a href="?logout=true" class="logout">লগআউট</a>
            </h1>

            <?php if (!empty($success_msg)): ?>
                <div class="alert"><?php echo $success_msg; ?></div>
            <?php endif; ?>

            <!-- অ্যানালিটিক্স কার্ড -->
            <div class="stats-grid">
                <div class="stat-card">
                    <h3><?php echo $unique_ips; ?></h3>
                    <p>ইউজার আইপি (Unique IPs)</p>
                </div>
                <div class="stat-card">
                    <h3><?php echo $total_requests; ?></h3>
                    <p>মোট রিকোয়েস্ট (Total Hits)</p>
                </div>
                <div class="stat-card">
                    <h3><?php echo $statusData['is_active'] ? 'ONLINE' : 'OFFLINE'; ?></h3>
                    <p>সার্ভার স্ট্যাটাস</p>
                </div>
            </div>

            <form method="POST">
                <input type="hidden" name="update_status" value="1">
                <input type="hidden" name="api_status" id="api_status_input" value="<?php echo $statusData['is_active'] ? '1' : '0'; ?>">
                
                <div class="status-box">
                    <div class="status-title">এপিআই সার্ভার মাস্টার সুইচ</div>
                    <div class="toggle-group">
                        <button type="button" id="btn_on" class="toggle-btn <?php echo $statusData['is_active'] ? 'active-on' : ''; ?>" onclick="setStatus('1')">ON</button>
                        <button type="button" id="btn_off" class="toggle-btn <?php echo !$statusData['is_active'] ? 'active-off' : ''; ?>" onclick="setStatus('0')">OFF</button>
                    </div>
                </div>

                <label>মেইন নোটিশ শিরোনাম (Title):</label>
                <input type="text" name="notice_title" value="<?php echo htmlspecialchars($statusData['notice_title']); ?>" required>

                <label>নোটিশের বিস্তারিত বার্তা (Message):</label>
                <textarea name="notice_message" required><?php echo htmlspecialchars($statusData['notice_message']); ?></textarea>

                <label>অফিশিয়াল টেলিগ্রাম চ্যানেল লিংক:</label>
                <input type="text" name="telegram_channel" value="<?php echo htmlspecialchars($statusData['telegram_channel']); ?>" required>

                <button type="submit" style="margin-top: 10px; padding: 14px;">সেটিংস সেভ করুন</button>
            </form>

            <h3 style="margin-top: 35px; margin-bottom: 12px; color: #38bdf8; font-size: 16px;">📊 রিকোয়েস্ট লগ ও অ্যানালিটিক্স (Recent Logs)</h3>
            
            <form method="POST" onsubmit="return confirm('সব লগ মুছে ফেলতে চান?');">
                <button type="submit" name="clear_logs" value="1" class="clear-btn">লগ রিসেট করুন</button>
            </form>

            <div style="background: #1f2937; border-radius: 10px; padding: 10px; overflow-x: auto; border: 1px solid #374151;">
                <table>
                    <tr>
                        <th>ইউজার আইপি (IP)</th>
                        <th>টার্গেট ইউজারনেম</th>
                        <th>সময়</th>
                    </tr>
                    <?php 
                    $recent_logs = array_reverse(array_slice($logsData, -30)); 
                    if (empty($recent_logs)): 
                    ?>
                    <tr>
                        <td colspan="3" style="text-align: center; color: #94a3b8;">কোনো রিকোয়েস্টের ডেটা পাওয়া যায়নি।</td>
                    </tr>
                    <?php else: foreach ($recent_logs as $log): ?>
                    <tr>
                        <td style="font-family: monospace; color: #34d399;"><?php echo htmlspecialchars($log['ip']); ?></td>
                        <td style="font-family: monospace; color: #f8fafc;"><?php echo htmlspecialchars($log['username'] ?? $log['phone'] ?? 'N/A'); ?></td>
                        <td style="font-size: 12px; color: #94a3b8;"><?php echo htmlspecialchars($log['time']); ?></td>
                    </tr>
                    <?php endforeach; endif; ?>
                </table>
            </div>
        <?php endif; ?>
    </div>
</body>
</html>
