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
        .about-container {
            max-width: 1000px;
            margin: 40px auto;
            padding: 20px;
            line-height: 1.8;
            color: #333;
            font-family: Arial, sans-serif;
        }

        .about-header {
            border-left: 8px solid #FF0000; /* Vivid Red Border */
            padding-left: 20px;
            margin-bottom: 40px;
        }

        .about-header h1 {
            color: #FF0000;
            font-size: 3rem;
            margin: 0;
            font-family: 'Arial Black', sans-serif;
            text-transform: uppercase;
        }

        .about-header p {
            font-size: 1.2rem;
            color: #666;
            margin-top: 5px;
        }

        .content-section {
            margin-bottom: 50px;
        }

        .content-section h2 {
            color: #FF0000;
            font-size: 1.8rem;
            border-bottom: 3px solid #FF0000;
            padding-bottom: 8px;
            margin-bottom: 20px;
            display: inline-block;
            text-transform: uppercase;
        }

        .content-text {
            font-size: 1.1rem;
            text-align: justify;
        }

        strong {
            color: #FF0000;
        }
        
        /* Highlight Box for Journey */
        .highlight-box {
            background: #fff5f5;
            border-right: 4px solid #FF0000;
            padding: 25px;
            margin: 20px 0;
            font-style: italic;
        }

        /* Retail Store Gallery - 3 Images */
        .store-gallery {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 20px;
            margin-top: 30px;
        }

        .store-photo {
            width: 100%;
            height: 250px;
            background-color: #f0f0f0; /* Gray placeholder */
            border: 2px solid #FF0000;
            border-radius: 4px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: #666;
            overflow: hidden;
            transition: transform 0.3s ease, box-shadow 0.3s ease;
        }

        /* Placeholder image text */
        .store-photo::after {
            content: "STORE PHOTO";
            font-weight: bold;
            font-size: 1.2rem;
        }

        /* Once you have real images, use this structure:
        .store-photo img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }
        */

        .store-photo:hover {
            transform: scale(1.05);
            box-shadow: 0 10px 20px rgba(255, 0, 0, 0.2);
        }
    </style>
</head>
<body>

<div class="about-container">
    <div class="about-header">
        <h1>ABOUT PXX Mall</h1>
        <p>Your Ultimate Fashion Destination Since 2020.</p>
    </div>

    <div class="content-section">
        <h2>Our Journey</h2>
        <div class="content-text">
            <p>
                Founded in <strong>2020</strong>, during the peak of the global <strong>pandemic era</strong>, 
                <strong>PXX Mall</strong> started as a small, resilient digital boutique. While the world was changing, 
                our passion for fashion remained constant. We recognized a growing need for quality, comfortable, and affordable apparel that people could trust from the safety of their homes.
            </p>
            <div class="highlight-box">
                "What began with a curated selection of essential Men's and Women's apparel gradually scaled up, fueled by our unwavering commitment to quality and our community's trust. Each garment we delivered became a testament to our perseverance."
            </div>
            <p>
                As an online retailer, we prioritized premium fabrics and cutting-edge designs, streamlining our operations to provide the best value directly to our customers. This focus allowed us to expand our collections and refine our services, ensuring every customer experience was seamless.
            </p>
        </div>
    </div>

    <div class="content-section">
        <h2>From Digital to Physical</h2>
        <div class="content-text">
            <p>
                Today, we are thrilled to welcome you not just through our screens, but through our doors. 
                Our journey has brought us full circle with the opening of our first <strong>Retail Flagship Store</strong> in Kuala Lumpur. 
                This physical space allows you to experience the quality and fit of our fashion lines firsthand.
            </p>
        </div>

        <div class="store-gallery">
            <div class="store-photo"></div> <div class="store-photo"></div> <div class="store-photo"></div> </div>
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