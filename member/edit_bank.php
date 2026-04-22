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
    header("Location: profile.php?page=bank&status=success");
    exit();
}
    } catch (PDOException $e) {
        $message = "Error: " . $e->getMessage();
    }
}

require_once __DIR__ . '/../includes/_head.php'; 
?>
    <link rel="stylesheet" href="../css/app.css">



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

<script src="../js/app.js"></script>
<?php require_once '../includes/_foot.php'; ?>