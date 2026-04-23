<?php
// TEE DE YONG's Part: Member Add (Logic & Structure)
ini_set('display_errors', 1);
error_reporting(E_ALL);

require_once __DIR__ . '/../includes/db.php'; 

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $user_id = $_POST['user_id'] ?? '';
    $username = $_POST['username'] ?? '';
    $real_name = $_POST['real_name'] ?? '';
    $email = $_POST['email'] ?? '';
    $password = $_POST['password'] ?? '';
    $address = $_POST['address'] ?? ''; // 💡 获取地址

    $password_hashed = password_hash($password, PASSWORD_DEFAULT);

    try {
        // 💡 确保数据库 users 表中有 address 这一列
        $insertStmt = $pdo->prepare("INSERT INTO users (user_id, username, real_name, email, password_hashed, address, role) VALUES (?, ?, ?, ?, ?, ?, 'Member')");
        $insertStmt->execute([$user_id, $username, $real_name, $email, $password_hashed, $address]);

        echo "<script>alert('✨ New member successfully added!'); window.location.href='member_crud.php';</script>";
        exit;
    } catch (Exception $e) {
        die("<div style='padding:20px; color:red;'>Insert Error: " . $e->getMessage() . "</div>");
    }
}

$admin_head = __DIR__ . '/../includes/_admin_head.php';
if (file_exists($admin_head)) { include $admin_head; }
?>

<link rel="stylesheet" href="../css/app.css?v=<?= time(); ?>">

<div id="add-wrapper" class="scroll-wrapper" style="display:none;">
    <div id="add-member-app" class="premium-form-card">
        <div class="form-header">
            <h2>New Member Registration</h2>
            <p>ADD A NEW USER TO THE SYSTEM</p>
        </div>
        <div class="form-body">
            <form method="POST" action="">
                <div class="grid-row">
                    <div class="form-group">
                        <label>User ID</label>
                        <input type="text" name="user_id" class="input-style" placeholder="e.g. a105" required>
                    </div>
                    <div class="form-group">
                        <label>Username</label>
                        <input type="text" name="username" class="input-style" placeholder="e.g. johndoe99" required>
                    </div>
                </div>

                <div class="form-group">
                    <label>Real Name</label>
                    <input type="text" name="real_name" class="input-style" placeholder="John Doe" required>
                </div>
                
                <div class="form-group">
                    <label>Email Address</label>
                    <input type="email" name="email" class="input-style" placeholder="john@example.com" required>
                </div>

                <div class="form-group">
                    <label>Home Address</label>
                    <textarea name="address" class="input-style" placeholder="Enter full address..." rows="3" style="resize: none;"></textarea>
                </div>

                <div class="form-group">
                    <label>Account Password</label>
                    <input type="password" name="password" class="input-style" placeholder="Enter a secure password" required>
                </div>

                <div class="btn-row">
                    <a href="member_crud.php" class="btn-back">← CANCEL</a>
                    <button type="submit" class="btn-submit">CREATE MEMBER</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script src="../js/app.js?v=<?= time(); ?>"></script>

<?php 
$foot = __DIR__ . '/../includes/_foot.php';
if (file_exists($foot)) { include $foot; } 
?>
