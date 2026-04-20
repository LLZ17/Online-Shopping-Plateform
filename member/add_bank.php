<?php
// 1. 启动会话
session_start();

// 2. 身份检测
if (!isset($_SESSION['user_id'])) {
    header("Location: ../index.php");
    exit();
}

// 3. 引入数据库配置
require_once __DIR__ . '/../includes/db.php';

$user_id = $_SESSION['user_id'];
$message = "";

// --- 4. 模拟 JSON 银行数据 (你可以把这部分放进单独的 .json 文件) ---
$banks_json = '[
    {"id": "pbb", "name": "Public Bank"},
    {"id": "maybank", "name": "Maybank"},
    {"id": "cimb", "name": "CIMB Bank"},
    {"id": "rhb", "name": "RHB Bank"},
    {"id": "hlb", "name": "Hong Leong Bank"}
]';
$bank_list = json_decode($banks_json, true);

// --- 5. 处理提交逻辑 ---
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['bank_name'])) {
    $selected_bank = trim($_POST['bank_name']);

    try {
        // 检查用户是否已经设置过银行 (假设表名是 users，字段是 preferred_bank)
        $stmt = $pdo->prepare("SELECT preferred_bank FROM users WHERE user_id = ?");
        $stmt->execute([$user_id]);
        $row = $stmt->fetch();

        // 逻辑：如果 preferred_bank 为空或不存在记录，则更新/插入
        // 这里假设 user 记录已存在，我们只需更新该字段
        $sql = "UPDATE `users` SET `preferred_bank` = ? WHERE `user_id` = ?";
        $update_stmt = $pdo->prepare($sql);
        $success = $update_stmt->execute([$selected_bank, $user_id]);

        if ($success) {
            echo "<script>
                    alert('✅ Bank preference updated successfully!');
                    window.location.href = 'profile.php?page=security';
                  </script>";
            exit();
        }
    } catch (PDOException $e) {
        $message = "Error: " . $e->getMessage();
    }
}

// 6. 引入头部
require_once __DIR__ . '/../includes/_head.php'; 
?>

<style>
    /* 页面背景 - 引用你之前的风格 */
    body {
        background-image: url('../images/bg.jpeg'); 
        background-size: cover;
        background-position: center;
        background-attachment: fixed;
        margin: 0;
    }

    .bank-wrapper {
        display: flex;
        justify-content: center;
        align-items: center;
        min-height: 80vh;
        padding: 20px;
    }

    .bank-card {
        max-width: 480px;
        width: 100%;
        background: rgba(255, 255, 255, 0.95); /* 半透明清晰效果 */
        padding: 40px;
        border-radius: 12px;
        box-shadow: 0 8px 30px rgba(0,0,0,0.1);
        text-align: center;
    }

    h1 {
        color: #1a1a1a;
        font-size: 20px;
        margin-bottom: 25px;
        border-bottom: 2px solid #e60012;
        padding-bottom: 12px;
        display: inline-block;
        text-transform: uppercase;
        letter-spacing: 1px;
    }

    /* 模拟图片中的展示框样式 */
    .current-selection-box {
        background: #fff;
        border: 1px solid #eee;
        border-radius: 10px;
        padding: 15px;
        display: flex;
        align-items: center;
        justify-content: space-between;
        margin-bottom: 25px;
        text-align: left;
    }

    .bank-icon {
        font-size: 24px;
        margin-right: 15px;
    }

    .bank-name-display {
        font-size: 18px;
        color: #333;
        font-weight: 500;
        flex: 1;
    }

    /* 下拉选择框样式 */
    .bank-select {
        width: 100%;
        padding: 12px;
        border: 1px solid #ddd;
        border-radius: 8px;
        font-size: 16px;
        margin-bottom: 20px;
        background: #f9f9f9;
    }

    .btn-save {
        background: #e60012;
        color: #fff;
        padding: 14px;
        border: none;
        border-radius: 8px;
        font-weight: bold;
        cursor: pointer;
        width: 100%;
        font-size: 16px;
        transition: 0.3s;
    }

    .btn-save:hover {
        background: #333;
    }

    .btn-cancel {
        display: block;
        margin-top: 20px;
        color: #999;
        text-decoration: none;
        font-size: 14px;
    }

    .btn-cancel:hover {
        color: #e60012;
    }
</style>

<div class="bank-wrapper">
    <div class="bank-card">
        <h2 style="color: #888; font-size: 14px; text-align: left; margin-bottom: 5px;">PREFERRED BANK</h2>
        
        <div class="current-selection-box">
            <span class="bank-icon">🏦</span>
            <span class="bank-name-display" id="display_name">Please select a bank</span>
            <span style="color: #e60012; font-weight: bold; font-size: 14px;">EDIT</span>
        </div>

        <?php if (!empty($message)): ?>
            <p style="color: red;"><?php echo $message; ?></p>
        <?php endif; ?>

        <form action="" method="POST">
            <label style="display: block; text-align: left; margin-bottom: 10px; color: #666; font-size: 13px;">Choose your bank from the list:</label>
            
            <select name="bank_name" class="bank-select" required onchange="updateDisplay(this)">
                <option value="" disabled selected>Select your bank</option>
                <?php foreach ($bank_list as $bank): ?>
                    <option value="<?php echo htmlspecialchars($bank['name']); ?>">
                        <?php echo htmlspecialchars($bank['name']); ?>
                    </option>
                <?php endforeach; ?>
            </select>

            <button type="submit" class="btn-save">Link Bank Account</button>
        </form>

        <a href="profile.php?page=security" class="btn-cancel">Cancel</a>
    </div>
</div>

<script>
// 实现选择下拉框时，上面的展示框同步变化
function updateDisplay(selectElement) {
    const displayName = document.getElementById('display_name');
    displayName.innerText = selectElement.value;
}
</script>

<?php require_once '../includes/_foot.php'; ?>