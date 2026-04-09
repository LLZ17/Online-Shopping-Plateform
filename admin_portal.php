<?php include 'includes/_admin_head.php'; ?>

<div class="stats-grid">
    <div class="stat-card" style="border-color: #3498db;">
        <small style="color: #888;">Total Categories</small>
        <h2 style="margin: 10px 0;">12</h2>
        <a href="category_maintenance.php" style="font-size: 12px; color: #3498db; text-decoration: none;">Manage Categories →</a>
    </div>
    
    <div class="stat-card" style="border-color: #2ecc71;">
        <small style="color: #888;">Fake Payment Logs</small>
        <h2 style="margin: 10px 0;">RM 5,240.00</h2>
        <span style="font-size: 12px; color: #888;">Based on Billplz Sandbox</span>
    </div>
    
    <div class="stat-card" style="border-color: #f1c40f;">
        <small style="color: #888;">Total Members</small>
        <h2 style="margin: 10px 0;">85</h2>
        <span style="font-size: 12px; color: #888;">Active users</span>
    </div>
</div>

<div style="background: white; padding: 25px; border-radius: 12px; box-shadow: 0 4px 6px rgba(0,0,0,0.02);">
    <h3>System Announcements</h3>
    <p style="color: #666; line-height: 1.6;">
        Welcome to the **PXX Mall Administrative Portal**. <br>
        As an administrator, you have the authority to manage product categories, monitor sales data from the Billplz integration, and maintain system security settings.
    </p>
    <hr style="border: 0; border-top: 1px solid #eee; margin: 20px 0;">
    <p><strong>Last Login:</strong> <?php echo date('Y-m-d H:i:s'); ?></p>
</div>

<?php include 'includes/_admin_foot.php'; // 底部依然可以用回公共的 ?>