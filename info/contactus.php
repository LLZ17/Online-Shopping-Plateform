<?php 
// 引用你的 head 文件
include('../includes/_info_head.php'); 
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <style>
        .contact-container {
            max-width: 1000px;
            margin: 40px auto;
            padding: 20px;
            line-height: 1.8;
            color: #333;
            font-family: Arial, sans-serif;
        }

        .contact-header {
            border-left: 8px solid #FF0000;
            padding-left: 20px;
            margin-bottom: 40px;
        }

        .contact-header h1 {
            color: #FF0000;
            font-size: 2.5rem;
            margin: 0;
            font-family: 'Arial Black', sans-serif;
            text-transform: uppercase;
        }

        .contact-content {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 30px;
        }

        .contact-info-card {
            background: #fff;
            padding: 30px;
            border: 2px solid #FF0000;
            border-radius: 4px;
        }

        .contact-info-card h2 {
            color: #FF0000;
            font-size: 1.6rem;
            margin-bottom: 20px;
            text-transform: uppercase;
            border-bottom: 2px solid #FF0000;
            display: inline-block;
        }

        .info-item {
            margin-bottom: 20px;
        }

        .info-item strong {
            display: block;
            color: #FF0000;
            font-size: 1.1rem;
            text-transform: uppercase;
            margin-bottom: 5px;
        }

        .info-item p {
            font-size: 1.1rem;
            color: #333;
            margin: 0;
        }

        /* 简单的地图占位框 */
        .map-placeholder {
            width: 100%;
            height: 300px;
            background-color: #f0f0f0;
            border: 1px solid #ddd;
            display: flex;
            align-items: center;
            justify-content: center;
            color: #666;
            margin-top: 20px;
        }
    </style>
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