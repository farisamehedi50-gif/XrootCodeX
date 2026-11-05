<?php
declare(strict_types=1);
error_reporting(0);
ini_set('display_errors', '0');

// আপনার অ্যাডমিন পাসওয়ার্ড
$admin_password = "483236"; 

$statusFile = __DIR__ . '/api_status.json';

// ডিফল্ট স্ট্যাটাস ফাইল তৈরি (যদি না থাকে)
if (!file_exists($statusFile)) {
    $defaultData = [
        "is_active" => true,
        "notice_title" => "এপিআই সাময়িকভাবে বন্ধ রয়েছে",
        "notice_message" => "এই এপিআইটি বন্ধ রাখা হয়েছে কারণ এটি নিয়ে অনৈতিক ব্যবসা শুরু করা হয়েছিল। অফিশিয়াল আপডেট ও পুনরায় চালুর খবর পেতে আমাদের টেলিগ্রাম চ্যানেলে জয়েন থাকুন।",
        "telegram_channel" => "https://t.me/CobraX_Empire"
    ];
    file_put_contents($statusFile, json_encode($defaultData, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
}

$statusData = json_decode(file_get_contents($statusFile), true);

$is_logged_in = false;
session_start();

// লগইন প্রসেস
if (isset($_POST['password']) && $_POST['password'] === $admin_password) {
    $_SESSION['new_admin_logged'] = true;
}

// লগআউট প্রসেস
if (isset($_GET['logout'])) {
    unset($_SESSION['new_admin_logged']);
    header("Location: admin.php");
    exit;
}

if (isset($_SESSION['new_admin_logged']) && $_SESSION['new_admin_logged'] === true) {
    $is_logged_in = true;
}

$success_msg = "";
if ($is_logged_in && $_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['update_status'])) {
        $statusData['is_active'] = isset($_POST['is_active']) ? true : false;
        $statusData['notice_title'] = trim($_POST['notice_title']);
        $statusData['notice_message'] = trim($_POST['notice_message']);
        $statusData['telegram_channel'] = trim($_POST['telegram_channel']);

        file_put_contents($statusFile, json_encode($statusData, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
        $success_msg = "সাকসেসফুল! সেটিংস আপডেট করা হয়েছে।";
        $statusData = json_decode(file_get_contents($statusFile), true);
    }
}
?>
<!DOCTYPE html>
<html lang="bn">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>New API Control Panel</title>
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; font-family: 'Segoe UI', Tahoma, sans-serif; }
        body { background: #090d16; color: #f1f5f9; padding: 20px; }
        .container { max-width: 600px; margin: 40px auto; background: #111827; border: 1px solid #1f2937; border-radius: 16px; padding: 30px; box-shadow: 0 15px 30px rgba(0,0,0,0.5); }
        h1 { color: #f8fafc; font-size: 20px; margin-bottom: 20px; border-bottom: 1px solid #1f2937; padding-bottom: 10px; display: flex; justify-content: space-between; align-items: center; }
        .login-box { max-width: 400px; margin: 100px auto; background: #111827; padding: 30px; border-radius: 16px; border: 1px solid #1f2937; text-align: center; }
        input[type="text"], input[type="password"], textarea { width: 100%; padding: 12px 14px; background: #1f2937; border: 1px solid #374151; border-radius: 8px; color: #fff; margin-bottom: 15px; font-size: 14px; }
        textarea { resize: vertical; height: 90px; }
        button { background: #2563eb; color: #fff; border: none; padding: 12px 20px; border-radius: 8px; font-weight: 600; cursor: pointer; transition: 0.2s; width: 100%; font-size: 15px; }
        button:hover { background: #1d4ed8; }
        .alert { background: rgba(16, 185, 129, 0.15); color: #34d399; padding: 12px; border-radius: 8px; margin-bottom: 20px; font-size: 14px; border: 1px solid rgba(16, 185, 129, 0.3); }
        .switch-container { display: flex; align-items: center; gap: 12px; margin-bottom: 20px; background: #1f2937; padding: 16px; border-radius: 8px; border: 1px solid #374151; }
        .logout { color: #f43f5e; text-decoration: none; font-size: 14px; }
        label { cursor: pointer; color: #94a3b8; font-size: 13px; font-weight: 600; display: block; margin-bottom: 6px; }
    </style>
</head>
<body>
    <div class="container">
        <?php if (!$is_logged_in): ?>
            <div class="login-box">
                <h2 style="margin-bottom: 20px; color:#fff;">Admin Login</h2>
                <?php if (isset($_POST['password'])): ?>
                    <p style="color: #f43f5e; margin-bottom: 15px; font-size:13px;">ভুল পাসওয়ার্ড!</p>
                <?php endif; ?>
                <form method="POST">
                    <input type="password" name="password" placeholder="অ্যাডমিন পাসওয়ার্ড দিন" required>
                    <button type="submit">লগইন করুন</button>
                </form>
            </div>
        <?php else: ?>
            <h1>
                <span>🛡️ New API Control Panel</span>
                <a href="?logout=true" class="logout">লগআউট</a>
            </h1>

            <?php if (!empty($success_msg)): ?>
                <div class="alert"><?php echo $success_msg; ?></div>
            <?php endif; ?>

            <form method="POST">
                <input type="hidden" name="update_status" value="1">
                
                <div class="switch-container">
                    <input type="checkbox" name="api_active" id="api_active" <?php echo $statusData['is_active'] ? 'checked' : ''; ?> style="width: 24px; height: 24px; cursor: pointer;">
                    <label for="api_active" style="font-weight: 600; font-size: 15px; color: #fff; margin:0;">এপিআই সার্ভার অন রাখুন (টিক উঠিয়ে দিলে বন্ধ হবে)</label>
                </div>

                <label>নোটিশের শিরোনাম (Title):</label>
                <input type="text" name="notice_title" value="<?php echo htmlspecialchars($statusData['notice_title']); ?>" required>

                <label>নোটিশের বিস্তারিত বার্তা (Message):</label>
                <textarea name="notice_message" required><?php echo htmlspecialchars($statusData['notice_message']); ?></textarea>

                <label>টেলিগ্রাম চ্যানেল বা বাটন লিংক:</label>
                <input type="text" name="telegram_channel" value="<?php echo htmlspecialchars($statusData['telegram_channel']); ?>" required>

                <button type="submit" style="margin-top: 10px;">সেটিংস সেভ করুন</button>
            </form>
        <?php endif; ?>
    </div>
</body>
</html>
