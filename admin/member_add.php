<?php
// TEE DE YONG's Part: Member Add (Blue Premium UI + Password Hashing)
ini_set('display_errors', 1);
error_reporting(E_ALL);

require_once __DIR__ . '/../includes/db.php'; 

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // 获取表单数据
    $user_id = $_POST['user_id'] ?? '';
    $username = $_POST['username'] ?? '';
    $real_name = $_POST['real_name'] ?? '';
    $email = $_POST['email'] ?? '';
    $password = $_POST['password'] ?? '';

    // 💡 安全第一：把用户输入的明文密码转换成数据库里的哈希密文 (Hash)
    $password_hashed = password_hash($password, PASSWORD_DEFAULT);

    try {
        // 插入新数据到 users 表
        $insertStmt = $pdo->prepare("INSERT INTO users (user_id, username, real_name, email, password_hashed) VALUES (?, ?, ?, ?, ?)");
        $insertStmt->execute([$user_id, $username, $real_name, $email, $password_hashed]);

        echo "<script>alert('✨ New member successfully added!'); window.location.href='member_crud.php';</script>";
        exit;

    } catch (Exception $e) {
        die("<div style='padding:20px; color:red;'>Insert Error: " . $e->getMessage() . "</div>");
    }
}

$admin_head = __DIR__ . '/../includes/_admin_head.php';
if (file_exists($admin_head)) { include $admin_head; }
?>

<style>
    .page-title-box { display: none !important; }
    html, body { background: #f8fafc !important; overflow: hidden !important; }
    
    .scroll-wrapper { height: 100vh; overflow-y: auto; padding-bottom: 150px; box-sizing: border-box; }

    .premium-form-card { max-width: 650px; margin: 30px auto; background: #ffffff; border-radius: 20px; box-shadow: 0 15px 35px rgba(0,0,0,0.08); border: 1px solid rgba(0,0,0,0.04); overflow: hidden; font-family: 'Inter', sans-serif; }
    
    /* 会员专属蓝色顶部 */
    .form-header { background: #111; padding: 25px 35px; border-bottom: 4px solid #2563eb; }
    .form-header h2 { margin: 0; color: white; font-size: 24px; font-weight: 900; letter-spacing: -0.5px; }
    .form-header p { margin: 5px 0 0; color: #94a3b8; font-size: 12px; font-weight: 600; text-transform: uppercase; }

    .form-body { padding: 30px 35px; }
    
    .grid-row { display: grid; grid-template-columns: 1fr 1fr; gap: 15px; margin-bottom: 20px; }
    .form-group { margin-bottom: 20px; }
    .grid-row .form-group { margin-bottom: 0; }

    .form-group label { display: block; font-weight: 800; color: #64748b; margin-bottom: 10px; font-size: 12px; text-transform: uppercase; letter-spacing: 1px; }
    
    .input-style { width: 100%; padding: 14px 18px; border-radius: 12px; border: 2px solid #f1f5f9; font-size: 14px; font-weight: 600; color: #1e293b; background: #f8fafc; transition: all 0.3s ease; box-sizing: border-box; }
    .input-style:focus { border-color: #2563eb; background: #fff; outline: none; box-shadow: 0 0 0 3px rgba(37,99,235,0.1); }

    .btn-row { display: flex; justify-content: space-between; align-items: center; margin-top: 25px; padding-top: 25px; border-top: 1px solid #f1f5f9; }
    
    /* 会员专属蓝色按钮 */
    .btn-submit { background: linear-gradient(135deg, #2563eb 0%, #1d4ed8 100%); color: white; padding: 16px 35px; border: none; border-radius: 12px; font-size: 14px; font-weight: 900; cursor: pointer; transition: all 0.3s ease; box-shadow: 0 8px 20px rgba(37,99,235,0.2); }
    .btn-submit:hover { transform: translateY(-2px); box-shadow: 0 10px 25px rgba(37,99,235,0.3); }
    
    .btn-back { color: #94a3b8; text-decoration: none; font-size: 13px; font-weight: 800; padding-left: 5px; transition: 0.3s; }
    .btn-back:hover { color: #1e293b; }
</style>

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

<script>
    // ⚔️ 布局保护逻辑
    document.addEventListener("DOMContentLoaded", function() {
        const myContent = document.getElementById('add-wrapper');
        if (!myContent) return;
        myContent.style.display = 'block';

        let viewport = document.querySelector('.admin-content-viewport, .main-content, .content-wrapper, .page-content, main');
        
        if (!viewport) {
            const allElements = document.body.getElementsByTagName('*');
            for (let el of allElements) {
                if (el.textContent && el.textContent.includes("Administrative Workspace Ready")) {
                    viewport = el.parentElement;
                    break;
                }
            }
        }

        if (viewport) {
            viewport.innerHTML = ''; 
            viewport.appendChild(myContent);
        } else {
            myContent.style.position = 'fixed';
            myContent.style.top = '60px'; 
            myContent.style.right = '0';
            myContent.style.width = 'calc(100vw - 250px)'; 
            myContent.style.height = 'calc(100vh - 60px)';
            myContent.style.background = '#f8fafc';
            myContent.style.zIndex = '9999';
            document.body.appendChild(myContent);
        }
    });
</script>

<?php 
$foot = __DIR__ . '/../includes/_foot.php';
if (file_exists($foot)) { include $foot; } 
?>