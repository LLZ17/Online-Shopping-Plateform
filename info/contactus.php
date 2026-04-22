<?php 
// 引用你的 head 文件
include('../includes/_info_head.php'); 
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="<?php echo $path_prefix; ?>../css/app.css">
</head>
<body>

<div class="contact-container">
    <div class="contact-header">
        <h1>Contact Us</h1>
        <p>We are here to assist you with your fashion needs.</p>
    </div>

    <div class="contact-content">
        <div class="contact-info-card">
            <h2>PXX MALL SDN BHD</h2>
            
            <div class="info-item">
                <strong>Phone</strong>
                <p>013-211 1222 / 03-2161 8888</p>
            </div>

            <div class="info-item">
                <strong>Email</strong>
                <p>pxxmallmalaysia@gmail.com</p>
            </div>

            <div class="info-item">
                <strong>Address</strong>
                <p>
                    Level 12, Menara KPJ,<br>
                    238, Jln Tun Razak, 50400 Kuala Lumpur,<br>
                    Federal Territory of Kuala Lumpur, Malaysia.
                </p>
            </div>
        </div>

        <div class="contact-info-card">
            <h2>Business Hours</h2>
            <div class="info-item">
                <strong>Customer Service</strong>
                <p>Monday - Friday: 9:00 AM - 6:00 PM</p>
                <p>Saturday: 9:00 AM - 1:00 PM</p>
                <p>Sunday & Public Holidays: Closed</p>
            </div>
            <div class="info-item">
                <p style="font-style: italic; color: #666;">
                    * For online orders, our website is available 24/7.
                </p>
            </div>
        </div>
    </div>
</div>

<?php 
// 引用页脚
if(file_exists('../includes/_foot.php')) {
    include('../includes/_foot.php');
} else {
    echo '<footer style="text-align:center; padding:30px; color:#FF0000; border-top:2px solid #FF0000; font-weight:bold;">© 2026 PXX Mall. All Rights Reserved.</footer>';
}
?>

</body>
</html>