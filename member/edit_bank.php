<?php
session_start();

if (!isset($_SESSION['user_id'])) {
    header("Location: ../index.php");
    exit();
}

require_once __DIR__ . '/../includes/db.php';

$user_id = $_SESSION['user_id'];
$message = "";

// 1. 银行列表数组
$banks = [
    "Affin Bank", "Alliance Bank", "AmBank", "Bank Islam", 
    "Bank Muamalat", "Bank Rakyat", "BSN", "CIMB Clicks", 
    "Hong Leong Bank", "HSBC Bank", "KFH", "Maybank", 
    "OCBC Bank", "Public Bank", "RHB Bank", 
    "Standard Chartered", "UOB Bank", "Agro Bank"
];

// 2. 处理提交 (字段名已修改为 bank_name)
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['bank_selection'])) {
    $selected_bank = trim($_POST['bank_selection']);
    try {
        // 根据你的要求，这里字段名改为 bank_name
        $sql = "UPDATE `users` SET `bank_name` = ? WHERE `user_id` = ?";
        $update_stmt = $pdo->prepare($sql);
        $success = $update_stmt->execute([$selected_bank, $user_id]);

        if ($success) {
            echo "<script>
                    alert('✅ Bank preference updated successfully!');
                    window.location.href = 'profile.php?page=bank';
                  </script>";
            exit();
        }
    } catch (PDOException $e) {
        $message = "Error: " . $e->getMessage();
    }
}

require_once __DIR__ . '/../includes/_head.php'; 
?>

<style>
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
        align-items: flex-start; /* 靠上对齐 */
        min-height: 150vh; /* 强制极高的页面高度 */
        padding: 60px 20px 400px 20px; /* 底部预留 400px 巨大空间强制下拉 */
    }

    .bank-card {
        max-width: 450px;
        width: 100%;
        background: rgba(255, 255, 255, 0.98);
        padding: 40px;
        border-radius: 15px;
        box-shadow: 0 10px 40px rgba(0,0,0,0.2);
        text-align: center;
        /* 确保卡片本身不溢出 */
        position: relative;
        margin-bottom: 200px; 
    }

    .section-title {
        color: #999;
        font-size: 13px;
        text-align: left;
        margin-bottom: 25px;
        font-weight: bold;
        letter-spacing: 1px;
        text-transform: uppercase;
    }

    .bank-select {
        width: 100%;
        padding: 15px;
        border: 1.5px solid #eee;
        border-radius: 10px;
        font-size: 16px;
        margin-bottom: 30px;
        background-color: #f9f9f9;
        color: #333;
        display: block;
        cursor: pointer;
        /* 自定义外观 */
        appearance: none;
        -webkit-appearance: none;
        background-image: url("data:image/svg+xml;charset=UTF-8,%3csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 24 24' fill='none' stroke='%23999' stroke-width='2' stroke-linecap='round' stroke-linejoin='round'%3e%3cpolyline points='6 9 12 15 18 9'%3e%3c/polyline%3e%3c/svg%3e");
        background-repeat: no-repeat;
        background-position: right 15px center;
        background-size: 15px;
    }

    .btn-save {
        background: #e60012;
        color: #fff;
        padding: 15px;
        border: none;
        border-radius: 10px;
        font-weight: bold;
        cursor: pointer;
        width: 100%;
        font-size: 16px;
        display: block;
        margin-bottom: 15px;
    }

    .btn-save:hover { background: #333; }

    .btn-cancel {
        display: block;
        text-align: center;
        color: #888;
        text-decoration: none;
        font-size: 14px;
        padding: 10px;
    }
</style>

<div class="bank-wrapper">
    <div class="bank-card">
        <h2 class="section-title">Preferred Bank</h2>

        <?php if (!empty($message)): ?>
            <p style="color: #e60012; font-size: 14px; margin-bottom: 15px;"><?php echo $message; ?></p>
        <?php endif; ?>

        <form action="" method="POST">
            <label style="display: block; text-align: left; margin-bottom: 12px; color: #555; font-size: 14px;">
                Please select your preferred bank:
            </label>
            
            <select name="bank_selection" class="bank-select" required>
                <option value="" disabled selected>-- Select Bank --</option>
                <?php foreach ($banks as $name): ?>
                    <option value="<?php echo htmlspecialchars($name); ?>">
                        <?php echo htmlspecialchars($name); ?>
                    </option>
                <?php endforeach; ?>
            </select>

            <button type="submit" class="btn-save">Confirm</button>
            <a href="profile.php?page=bank" class="btn-cancel">Cancel</a>
        </form>
    </div>
</div>

<?php require_once '../includes/_foot.php'; ?>