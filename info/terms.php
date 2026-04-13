<?php 
// Referencing your header file
include('../includes/_info_head.php'); 
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <style>
        .terms-container {
            max-width: 1000px;
            margin: 40px auto;
            padding: 20px;
            line-height: 1.8;
            color: #333;
            font-family: Arial, sans-serif;
        }

        .terms-header {
            border-left: 8px solid #FF0000;
            padding-left: 20px;
            margin-bottom: 40px;
        }

        .terms-header h1 {
            color: #FF0000;
            font-size: 2.5rem;
            margin: 0;
            font-family: 'Arial Black', sans-serif;
            text-transform: uppercase;
        }

        .terms-section {
            margin-bottom: 30px;
        }

        .terms-section h2 {
            color: #FF0000;
            font-size: 1.4rem;
            border-bottom: 1px solid #eee;
            padding-bottom: 8px;
            margin-bottom: 15px;
            text-transform: uppercase;
        }

        .terms-section p, .terms-section ul {
            font-size: 1rem;
            color: #555;
            margin-bottom: 15px;
        }

        .privacy-highlight {
            background-color: #fff5f5;
            border: 1px dashed #FF0000;
            padding: 20px;
            border-radius: 4px;
        }

        strong {
            color: #FF0000;
        }

        .footer-note {
            margin-top: 50px;
            font-size: 0.9rem;
            color: #999;
            text-align: center;
        }
    </style>
</head>
<body>

<div class="terms-container">
    <div class="terms-header">
        <h1>Terms & Conditions</h1>
        <p>Last Updated: April 2026</p>
    </div>

    <div class="terms-section">
        <h2>1. Agreement to Terms</h2>
        <p>By accessing <strong>PXX Mall</strong>, you agree to be bound by these Terms and Conditions. If you do not agree, please refrain from using our online store.</p>
    </div>

    <div class="terms-section">
        <h2>2. Privacy & Personal Data</h2>
        <div class="privacy-highlight">
            <p>Your privacy is our priority. In compliance with the <strong>Personal Data Protection Act (PDPA)</strong>, we strictly follow these rules:</p>
            <ul>
                <li><strong>Data Collection:</strong> We only collect necessary info (Name, Address, Phone, Email) to process your clothing orders.</li>
                <li><strong>Usage:</strong> Your data is used exclusively for shipping and order updates. We <strong>never</strong> sell your personal information to third-party advertisers.</li>
                <li><strong>Payment Security:</strong> We do not store your credit card or bank login details. All transactions are handled via encrypted, secure payment gateways.</li>
                <li><strong>Account Deletion:</strong> You have the right to request the permanent deletion of your account and personal history from our database at any time.</li>
            </ul>
        </div>
    </div>

    <div class="terms-section">
        <h2>3. User Responsibilities</h2>
        <p>Users are responsible for maintaining the confidentiality of their account passwords. <strong>PXX Mall</strong> shall not be liable for any unauthorized access resulting from user negligence.</p>
    </div>

    <div class="terms-section">
        <h2>4. Accuracy of Information</h2>
        <p>We strive to display our <strong>Men's and Women's apparel</strong> as accurately as possible. However, actual colors may vary slightly due to your monitor settings or photographic lighting.</p>
    </div>

    <div class="terms-section">
        <h2>5. Copyright</h2>
        <p>All content on this site, including the <strong>PXX Mall®</strong> logo, designs, and images, is the exclusive property of our brand. Unauthorized use is strictly prohibited.</p>
    </div>

    <div class="footer-note">
        If you have any questions regarding your privacy, please contact our Data Protection Officer via the Contact Us page.
    </div>
</div>

<?php 
// Referencing your footer
if(file_exists('../includes/_foot.php')) {
    include('../includes/_foot.php');
} else {
    echo '<footer style="text-align:center; padding:30px; color:#FF0000; border-top:2px solid #FF0000; font-weight:bold;">© 2026 PXX Mall. All Rights Reserved.</footer>';
}
?>

</body>
</html>