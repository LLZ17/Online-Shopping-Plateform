<?php
/**
 * PXX Mall - Edit Phone Number & Verification (Merged Version)
 * 功能：背景图支持、新旧号码查重、OTP 模拟发送、严格验证
 */
session_start();

// 1. 身份检测：确保用户已登录
if (!isset($_SESSION['user_id'])) {
    header("Location: ../index.php");
    exit();
}

// 2. 引入数据库配置
require_once __DIR__ . '/../includes/db.php';

$user_id = $_SESSION['user_id'];
$message = "";
$otp_sent = false;

// --- 获取当前用户的旧手机号 (用于查重) ---
$stmt_check = $pdo->prepare("SELECT phone_num FROM users WHERE user_id = ?");
$stmt_check->execute([$user_id]);
$current_user = $stmt_check->fetch();
$old_phone = $current_user['phone_num'] ?? '';

// 3. 处理逻辑 (Invisible Logic)
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    
    // A. 模拟发送验证码逻辑
    if (isset($_POST['action']) && $_POST['action'] == 'send_otp') {
        $full_number = $_POST['prefix'] . $_POST['phone_num'];
        
        // 【新增检查】：新号码不可以和现有的一样
        if ($full_number === $old_phone) {
            $message = "❌ New phone number cannot be the same as your current one.";
        } else {
            $generated_otp = rand(100000, 999999); // 生成6位数字
            
            // 将验证码和号码存入 Session 供下一步比对
            $_SESSION['temp_otp'] = $generated_otp;
            $_SESSION['temp_phone'] = $full_number;
            
            $otp_sent = true;
            // 本地环境模拟：直接通过页面变量告知用户验证码
            $message = "【PXX Mall】Your verification code is: $generated_otp (Simulated SMS)";
        }
    }

    // B. 核心验证与分支重定向逻辑
    if (isset($_POST['action']) && $_POST['action'] == 'verify_otp') {
        $input_otp = trim($_POST['otp_code']);
        $saved_otp = $_SESSION['temp_otp'] ?? '';
        $new_phone = $_SESSION['temp_phone'] ?? '';

        // 严格比对验证码
        if ($input_otp == $saved_otp && !empty($input_otp)) {
            try {
                // 更新数据库：修改手机号并设为 Verified
                $sql = "UPDATE `users` SET `phone_num` = ?, `phone_status` = 'Verified' WHERE `user_id` = ?";
                $stmt = $pdo->prepare($sql);
                $stmt->execute([$new_phone, $user_id]);

                // 清除临时验证数据
                unset($_SESSION['temp_otp'], $_SESSION['temp_phone']);

                // 🔥 精准重定向：返回 profile.php 的安全设置分支
                echo "<script>
                        alert('✅ Verification Successful! Phone number has been updated.');
                        window.location.href = 'profile.php?page=security';
                      </script>";
                exit();
            } catch (PDOException $e) {
                $message = "Database Error: " . $e->getMessage();
            }
        } else {
            // 验证失败：将数据库状态标记为 Unverified (可选逻辑)
            $sql = "UPDATE `users` SET `phone_status` = 'Unverified' WHERE `user_id` = ?";
            $pdo->prepare($sql)->execute([$user_id]);
            $message = "❌ Incorrect verification code. Please try again.";
        }
    }
}

require_once __DIR__ . '/../includes/_head.php'; 
?>

<style>
    /* 1. 页面大框架 & 背景图 */
    body {
        /* 在这里修改你的背景图路径 */
        background-image: url('../images/bg.jpeg'); 
        background-size: cover;
        background-position: center;
        background-attachment: fixed;
        margin: 0;
    }

    .edit-phone-wrapper {
        display: flex;
        justify-content: center;
        align-items: center;
        min-height: 80vh;
        padding: 20px;
    }

    .phone-card {
        max-width: 480px;
        width: 100%;
        background: rgba(255, 255, 255, 0.95); /* 半透明白色，保持易读性 */
        padding: 40px;
        border-radius: 12px;
        box-shadow: 0 8px 30px rgba(0,0,0,0.08);
        text-align: center;
    }

    h1 {
        color: #1a1a1a;
        font-size: 22px;
        margin-bottom: 25px;
        border-bottom: 2px solid #e60012;
        padding-bottom: 12px;
        display: inline-block;
    }

    /* 国际区号组合输入框布局 */
    .input-group-pxx {
        display: flex;
        gap: 12px;
        margin: 25px 0;
        justify-content: center;
    }

    .select-prefix {
        width: 90px;
        padding: 12px;
        border: 1px solid #ddd;
        border-radius: 8px;
        background: #fafafa;
        font-weight: 600;
    }

    .phone-input {
        flex: 1;
        padding: 12px;
        border: 1px solid #ddd;
        border-radius: 8px;
        font-size: 16px;
    }

    /* 6位OTP严格输入框样式 */
    .otp-input-large {
        width: 100%;
        max-width: 300px;
        padding: 15px;
        border: 2px solid #e60012;
        border-radius: 8px;
        font-size: 28px;
        text-align: center;
        letter-spacing: 10px;
        margin: 20px auto;
        display: block;
        font-family: monospace;
    }

    .btn-action-pxx {
        background: #e60012;
        color: #fff;
        padding: 14px;
        border: none;
        border-radius: 8px;
        font-weight: bold;
        cursor: pointer;
        width: 100%;
        font-size: 16px;
        transition: 0.2s;
    }

    .btn-action-pxx:hover { background: #333; }

    .btn-cancel {
        display: block;
        margin-top: 25px;
        color: #999;
        text-decoration: none;
        font-size: 14px;
    }

    .btn-cancel:hover { text-decoration: underline; color: #e60012; }

    /* 错误/提示框颜色调整 */
    .msg-alert {
        padding: 15px;
        background: #fffbe6;
        color: #856404;
        border: 1px solid #ffe58f;
        border-radius: 8px;
        margin-bottom: 25px;
        font-size: 14px;
        line-height: 1.5;
    }
</style>

<div class="edit-phone-wrapper">
    <div class="phone-card">
        <h1>📱 Phone Verification</h1>

        <?php if (!empty($message)): ?>
            <div class="msg-alert"><?php echo $message; ?></div>
        <?php endif; ?>

        <form action="" method="POST" style="<?php echo $otp_sent ? 'display:none;' : ''; ?>">
            <input type="hidden" name="action" value="send_otp">
            <p style="color: #666; font-size: 14px;">Select your country code<br>and enter your new phone number:</p>
            
            <div class="input-group-pxx">
                <select name="prefix" class="select-prefix">
                    <option value="+60">+60 🇲🇾</option>
                    <option value="+1">+1 🇺🇸</option>
                    <option value="+86">+86 🇨🇳</option>
                    <option value="+65">+65 🇸🇬</option>
                </select>
                <input type="tel" name="phone_num" class="phone-input" 
                       placeholder="123456789" 
                       value="<?php echo isset($_POST['phone_num']) ? htmlspecialchars($_POST['phone_num']) : ''; ?>"
                       required autofocus>
            </div>
            
            <button type="submit" class="btn-action-pxx">Send OTP Code</button>
        </form>

        <form action="" method="POST" style="<?php echo !$otp_sent ? 'display:none;' : ''; ?>">
            <input type="hidden" name="action" value="verify_otp">
            <p style="color: #666; font-size: 14px;">Please enter the 6-digit code sent to your phone:</p>
            
            <input type="text" 
                   name="otp_code" 
                   class="otp-input-large" 
                   placeholder="000000" 
                   maxlength="6" 
                   inputmode="numeric" 
                   pattern="[0-9]{6}" 
                   required>

            <button type="submit" class="btn-action-pxx">Verify & Update</button>
            <p style="margin-top: 20px;">
                <a href="edit_phone.php" style="color: #0055aa; font-size: 13px; text-decoration:none;">Wrong number? Go back</a>
            </p>
        </form>

        <a href="profile.php?page=security" class="btn-cancel">Cancel and return to Security</a>
    </div>
</div>

<?php require_once '../includes/_foot.php'; ?>