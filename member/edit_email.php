<?php session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: ../index.php");
    exit();
}else {
    require_once __DIR__ . '/../includes/db.php';
    require_once __DIR__ . '/../includes/_head.php';
    }?>

<style>
    /* ==========================================
       1. 页面大框架 (Main Layout)
       ========================================== */
    .cart-container {
        max-width: 1000px;
        margin: 40px auto;
        padding: 30px;
        background: white;
        border-radius: 12px;
        box-shadow: 0 2px 10px rgba(0, 0, 0, 0.05);
    }

    h1 {
        margin-bottom: 25px;
        padding-bottom: 15px;
        color: #333;
        border-bottom: 2px solid #e60012; /* PXX Mall 招牌红 */
    }

    .email-input {
        width: 400px;
        font-weight: 120%;
    }

    .email-input::placeholder {
        font-weight: 600;
    }

    .card-flex {
        display : flex 1;
    }

</style>

<div class="cart-container">
    <h1>📧 Changing Email Address</h1>
    <form action="edit_email.php" method="POST" class="card-flex">
        <label for="email">Enter Your new email address:</label>
        
        <div class="input-group">
            <input type="email" id="email" name="update_email" class="email-input" placeholder="Example: user@example.com" autofocus required>
            
            <button type="submit" class="btn-submit">Update</button>
        </div>
    </form>
</div>
<?php if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['update_email']))
{
    $update_sql = "UPDATE `users` SET `email` = ? WHERE `user_id` = ?;";
    $email_update = $pdo->prepare($update_sql);
    $email_update->execute([$_POST['update_email'], $_SESSION['user_id']]);
}
    require_once '../includes/_foot.php'; ?>