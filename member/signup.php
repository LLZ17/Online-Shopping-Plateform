<?php
/**
 * 完整注册页面，包含用户名检测和注册逻辑
 */

session_start();
date_default_timezone_set('Asia/Kuala_Lumpur');

// 模拟数据库连接（请根据实际环境连接数据库）
try {
    $pdo = new PDO('mysql:host=localhost;dbname=your_db_name;charset=utf8mb4', 'your_username', 'your_password');
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (Exception $e) {
    die("数据库连接失败：".$e->getMessage());
}

// 处理AJAX请求检测用户名
if (isset($_GET['check_user'])) {
    $requested_user = trim($_GET['check_user']);
    if (empty($requested_user)) {
        echo "empty";
        exit;
    }
    // 查询数据库
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM users WHERE username = ?");
    $stmt->execute([$requested_user]);
    $exists = $stmt->fetchColumn();
    if ($exists > 0) {
        echo "taken";
    } else {
        echo "available";
    }
    exit;
}

// 生成新ID
function generate_member_id($pdo) {
    $sql = "SELECT user_id FROM users WHERE user_id LIKE 'm%' ORDER BY CAST(SUBSTRING(user_id, 2) AS UNSIGNED) DESC LIMIT 1";
    $stmt = $pdo->query($sql);
    $last_id = $stmt->fetchColumn();
    if (!$last_id) return "m1060";
    $num = (int)substr($last_id,1);
    return "m" . ($num < 1060 ? 1060 : $num + 1);
}

// 处理注册提交
$errors = "";
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['pxx_final_submit'])) {
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';
    $real_name = trim($_POST['real_name'] ?? '');
    $gender = $_POST['gender'] ?? 'Male';
    $birthday = $_POST['birthday'] ?? '';
    $phone = trim($_POST['phone_num'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $address1 = trim($_POST['address_line1'] ?? '');
    $address2 = trim($_POST['address_line2'] ?? '');
    $address3 = trim($_POST['address_line3'] ?? '');
    $address = $address1 . ($address2 ? " | $address2" : "") . ($address3 ? " | $address3" : "");
    $postcode = trim($_POST['postcode'] ?? '');
    $city = trim($_POST['city'] ?? '');
    $state = $_POST['state'] ?? '';
    $tnc_checked = isset($_POST['tnc_check']);

    // 重新验证
    if (!$tnc_checked) {
        $errors = "请阅读并同意条款。";
    } else {
        // 后端验证用户名唯一
        $stmt_check = $pdo->prepare("SELECT user_id FROM users WHERE username = ? LIMIT 1");
        $stmt_check->execute([$username]);
        if ($stmt_check->fetch()) {
            $errors = "用户名已被占用，请更换。";
        } else {
            // 注册
            $member_id = generate_member_id($pdo);
            $hashed_pw = password_hash($password, PASSWORD_DEFAULT);
            $now = date('Y-m-d H:i:s');

            $sql = "INSERT INTO users (user_id, username, password_hashed, real_name, gender, birthday, phone_num, email, address, Postcode, City, State, created_at, status, role, points)
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'Active', 'Member', 100)";
            $stmt = $pdo->prepare($sql);
            $success = $stmt->execute([$member_id, $username, $hashed_pw, $real_name, $gender, $birthday, $phone, $email, $address, $postcode, $city, $state, $now]);

            if ($success) {
                $_SESSION['user_id'] = $member_id;
                $_SESSION['username'] = $username;
                $_SESSION['role'] = 'Member';
                echo "<script>alert('注册成功！您的ID：$member_id'); window.location='index.php';</script>";
                exit;
            } else {
                $errors = "注册失败，请稍后重试。";
            }
        }
    }
}

?>

<!DOCTYPE html>
<html lang="zh">
<head>
<meta charset="UTF-8" />
<title>会员注册</title>
<style>
/* 样式略，使用你之前的CSS样式或自行定义 */
body { font-family: Arial, sans-serif; }
#user_feedback { margin-top: 5px; font-weight: bold; }
.pxx-warning-hint { display: none; font-size: 14px; padding: 5px 10px; border-radius: 8px; }
.taken { background-color: #ff4d4f; color: #fff; display: block; }
.available { background-color: #2ecc71; color: #fff; display: block; }
</style>
</head>
<body>

<h2>会员注册</h2>
<?php if ($errors): ?>
<div style="color:red;"><?php echo $errors; ?></div>
<?php endif; ?>

<form method="POST" action="">
<label>用户名：</label>
<input type="text" name="username" id="f_user" oninput="checkUsername()" required />
<div id="user_feedback" class="pxx-warning-hint"></div>

<label>密码：</label>
<input type="password" name="password" required />

<label>确认密码：</label>
<input type="password" required />

<h3>个人信息</h3>
<label>姓名：</label>
<input type="text" name="real_name" required />

<label>性别：</label>
<select name="gender">
<option value="Male">男</option>
<option value="Female">女</option>
</select>

<label>生日：</label>
<input type="date" name="birthday" required />

<label>手机：</label>
<input type="text" name="phone_num" required />

<label>邮箱：</label>
<input type="email" name="email" required />

<h3>地址信息</h3>
<label>地址行1：</label>
<input type="text" name="address_line1" required />

<label>地址行2：</label>
<input type="text" name="address_line2" />

<label>地址行3：</label>
<input type="text" name="address_line3" />

<label>邮政编码：</label>
<input type="text" name="postcode" required />

<label>地区：</label>
<select name="state" id="f_state" onchange="loadCities()" required>
<option value="">选择地区</option>
<!-- 你可以手动添加地区或通过JS加载 -->
</select>

<label>城市：</label>
<select name="city" id="f_city" disabled required>
<option value="">选择城市</option>
</select>

<label>
<input type="checkbox" name="tnc_check" id="f_agree" onchange="validateStep3()" /> 我已阅读条款
</label>

<br/><br/>
<button type="submit" onclick="return validateForm()">注册</button>
<input type="hidden" name="pxx_final_submit" value="1" />
</form>

<script>
let pxx_user_valid = false;

// 用户名检测
async function checkUsername() {
    const input = document.getElementById('f_user');
    const feedback = document.getElementById('user_feedback');
    const username = input.value.trim();

    if (username.length < 3) {
        feedback.style.display = 'none';
        pxx_user_valid = false;
        return;
    }
    try {
        const res = await fetch('?check_user=' + encodeURIComponent(username));
        const status = await res.text();
        if (status === 'taken') {
            feedback.innerHTML = '❌ 用户名已存在！';
            feedback.className = 'pxx-warning-hint taken';
            feedback.style.display = 'block';
            pxx_user_valid = false;
        } else if (status === 'available') {
            feedback.innerHTML = '✅ 用户名可用。';
            feedback.className = 'pxx-warning-hint available';
            feedback.style.display = 'block';
            pxx_user_valid = true;
        } else {
            feedback.style.display = 'none';
            pxx_user_valid = false;
        }
    } catch (e) {
        console.error(e);
    }
}

// 提交前验证
function validateForm() {
    const username = document.getElementById('f_user').value.trim();
    const pass1 = document.querySelector('input[name=password]').value;
    const pass2 = document.querySelector('input[name=confirm_password]').value;
    if (!pxx_user_valid) {
        alert('请确保用户名已验证且未被占用！');
        return false;
    }
    if (pass1 !== pass2) {
        alert('两次密码不一致！');
        return false;
    }
    // 其他验证...
    return true;
}

function loadCities() {
    // 你这里可以加载城市
}
</script>
</body>
</html>