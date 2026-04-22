<?php 
// 引用你的 head 文件
include('../includes/_info_head.php'); 
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="../css/app.css">

</head>
<body>

<div class="policy-container">
    <div class="policy-header">
        <h1>Customer Policy</h1>
        <p>Your satisfaction and security are our top priorities.</p>
    </div>

    <div class="policy-section">
        <h2>Return & Exchange Policy</h2>
        <p>We want you to love what you ordered! If the fit isn't right, we offer a hassle-free return process:</p>
        <ul>
            <li>Items must be returned within <strong>7 days</strong> of receiving your parcel.</li>
            <li>Clothes must be <strong>unworn, unwashed</strong>, and have all original tags attached.</li>
            <li>Original shipping fees are non-refundable unless the item is defective.</li>
        </ul>
    </div>

    <div class="policy-section">
        <h2>Shipping & Delivery</h2>
        <p>PXX Mall partners with reliable couriers to ensure your fashion finds arrive safely:</p>
        <ul>
            <li><strong>Processing Time:</strong> Orders are processed within 1-2 business days.</li>
            <li><strong>Delivery:</strong> Standard delivery takes 3-5 business days within West Malaysia and 5-7 days for East Malaysia.</li>
            <li><strong>Tracking:</strong> A tracking number will be sent to your email once the parcel is dispatched.</li>
        </ul>
    </div>

    <div class="policy-section">
        <h2>Secure Payment</h2>
        <p>Shop with confidence. We support multiple secure payment methods including:</p>
        <ul>
            <li>Major Credit/Debit Cards (Visa/Mastercard).</li>
            <li>FPX Online Banking (through Toyyib Pay).</li>
        </ul>
        <p>Your financial data is encrypted and never stored on our servers.</p>
    </div>

    <div class="policy-section">
        <h2>Quality Guarantee</h2>
        <p>Every piece of clothing at <strong>PXX Mall</strong> undergoes a strict quality check. If you receive an item with a manufacturing defect, please contact us immediately for a full refund or a 1-to-1 replacement.</p>
    </div>

    <div class="contact-box">
        <p>Have questions about your order? Our support team is here to help.</p>
        <p>Email us at pxxmallmalaysia@gmail.com </p>
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