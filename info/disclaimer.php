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
        .disclaimer-container {
            max-width: 1000px;
            margin: 40px auto;
            padding: 20px;
            line-height: 1.8;
            color: #333;
            font-family: Arial, sans-serif;
        }

        .disclaimer-header {
            border-left: 8px solid #FF0000;
            padding-left: 20px;
            margin-bottom: 40px;
        }

        .disclaimer-header h1 {
            color: #FF0000;
            font-size: 2.5rem;
            margin: 0;
            font-family: 'Arial Black', sans-serif;
            text-transform: uppercase;
        }

        .disclaimer-section {
            margin-bottom: 35px;
            background: #fff;
            padding: 25px;
            border: 1px solid #FF0000;
            border-radius: 4px;
        }

        .academic-notice {
            background-color: #fff5f5;
            padding: 20px;
            border-left: 5px solid #FF0000;
            margin-bottom: 30px;
        }

        .disclaimer-section h2 {
            color: #FF0000;
            font-size: 1.4rem;
            margin-bottom: 15px;
            text-transform: uppercase;
        }

        .disclaimer-section p {
            font-size: 1rem;
            color: #444;
            margin-bottom: 15px;
        }

        strong {
            color: #FF0000;
        }

        .reference-list {
            padding-left: 20px;
            color: #555;
        }

        .reference-list li {
            margin-bottom: 10px;
        }
    </style>
</head>
<body>

<div class="disclaimer-container">
    <div class="disclaimer-header">
        <h1>Disclaimer</h1>
        <p>Important Notice Regarding This Website.</p>
    </div>

    <div class="academic-notice">
        <h2>Academic Project Notice</h2>
        <p>
            Please be advised that <strong>PXX Mall</strong> is a <strong>student project</strong> created for educational and academic purposes only. 
            This website is <strong>NOT</strong> a real commercial platform and does not engage in actual business transactions.
        </p>
    </div>

    <div class="disclaimer-section">
        <h2>References & Inspirations</h2>
        <p>
            In the process of developing this system, we have referred to and analyzed several existing e-commerce platforms and digital resources to understand industry standards.
        </p>
        <ul class="reference-list">
            <li><strong>Design & Layout:</strong> Inspired by various international and local Malaysian fashion retail websites.</li>
            <li><strong>Content & Media:</strong> Some images, descriptions, or structural ideas may have been used for referencing and educational demonstration.</li>
            <li><strong>Development:</strong> Coding practices and logic are based on academic coursework and open-source documentation.</li>
        </ul>
    </div>

    <div class="disclaimer-section">
        <h2>No Commercial Liability</h2>
        <p>
            Since this is a simulated environment:
        </p>
        <ul>
            <li>No real products are sold, and no financial transactions are processed.</li>
            <li>Any payment information or personal data entered is for testing purposes within the scope of this project.</li>
            <li>The developers of <strong>PXX Mall</strong> are not liable for any misunderstandings regarding the nature of this website as a non-commercial entity.</li>
        </ul>
    </div>

    <div style="text-align: center; color: #999; margin-top: 50px;">
        <p>Developed with passion by TARUMT FOCS DIPLOMA IN INFOMATION TECHNOLOGY GROUP 5 student.</p>
    </div>
</div>

<?php 
// Referencing your footer
if(file_exists('../includes/_foot.php')) {
    include('../includes/_foot.php');
} else {
    echo '<footer style="text-align:center; padding:30px; color:#FF0000; border-top:2px solid #FF0000; font-weight:bold;">© 2026 PXX Mall (Student Project). All Rights Reserved.</footer>';
}
?>

</body>
</html>