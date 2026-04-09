<?php
require_once 'includes/_base.php';
require_once 'includes/db.php'; 

$_title = 'Admin | Member Maintenance';
include 'includes/_admin_head.php';

// 使用 PDO 获取所有会员 (排除管理员自己，或者全部列出) [cite: 18]
$stmt = $pdo->query("SELECT * FROM users");
$members = $stmt->fetchAll();
?>

<div class="container">
    <h1>Member Maintenance (Admin)</h1>
    <table border="1" width="100%" style="border-collapse: collapse; margin-top: 20px;">
        <thead>
            <tr>
                <th>ID</th>
                <th>Username</th>
                <th>Email</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($members as $m): ?>
            <tr>
                <td><?= $m['user_id'] ?></td>
                <td><?= $m['username'] ?></td>
                <td><?= $m['email'] ?></td>
                <td>
                    <a href="admin_member_detail.php?id=<?= $m['user_id'] ?>">Detail</a>
                </td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>

<?php include 'includes/_foot.php'; ?>