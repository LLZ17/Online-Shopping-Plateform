<?php 
// Referencing your header file
include('../includes/_info_head.php'); 
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>About PXX Mall</title>
    <style>
        .about-container {
            max-width: 1200px; /* 稍微调宽了容器，适应更大的图片 */
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
            font-size: 3.5rem; /* 稍微加大了标题 */
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
            margin-bottom: 60px;
        }

        .content-section h2 {
            color: #FF0000;
            font-size: 2rem;
            border-bottom: 4px solid #FF0000;
            padding-bottom: 8px;
            margin-bottom: 25px;
            display: inline-block;
            text-transform: uppercase;
        }

        .content-text {
            font-size: 1.15rem;
            text-align: justify;
        }

        strong {
            color: #FF0000;
        }
        
        /* Highlight Box */
        .highlight-box {
            background: #fff5f5;
            border-right: 5px solid #FF0000;
            padding: 30px;
            margin: 30px 0;
            font-style: italic;
            font-size: 1.2rem;
        }

        /* 商店展示区域 - 重点修改部分 */
        .store-gallery {
            display: grid;
            /* 设定每列最小 350px，自动填充 */
            grid-template-columns: repeat(auto-fit, minmax(350px, 1fr));
            gap: 30px; 
            margin-top: 40px;
        }

        .store-photo {
            width: 100%;
            height: 500px; /* 这里大幅增加了高度，让图片更大 */
            background-color: #f9f9f9; 
            border: 3px solid #FF0000; /* 加粗了边框 */
            border-radius: 8px;
            overflow: hidden;
            transition: transform 0.4s ease, box-shadow 0.4s ease;
        }

        /* 确保图片完美填充且不拉伸 */
        .store-photo img {
            width: 100%;
            height: 100%;
            object-fit: cover; /* 核心：裁剪并填满，图片不会压扁 */
            display: block;
        }

        .store-photo:hover {
            transform: translateY(-10px); /* 悬浮时往上跳一点 */
            box-shadow: 0 15px 30px rgba(255, 0, 0, 0.3);
        }

        @media (max-width: 768px) {
            .store-photo {
                height: 350px; /* 在手机上高度稍微调小一点 */
            }
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
            <div class="store-photo">
                <img src="../images/pxx.jpeg" alt="PXX Mall Store Front">
            </div> 
            
            <div class="store-photo">
                <img src="store_02.jpg" alt="PXX Mall Design Concept">
            </div> 
            
            <div class="store-photo">
                <img src="store_03.jpg" alt="PXX Mall Interior">
            </div> 
        </div>
    </div>
</div>

<?php 
// Referencing your footer
if(file_exists('../includes/_foot.php')) {
    include('../includes/_foot.php');
} else {
    echo '<footer style="text-align:center; padding:50px 30px; color:#FF0000; border-top:2px solid #FF0000; font-weight:bold;">© 2026 PXX Mall (Student Project). All Rights Reserved.</footer>';
}
?>

</body>
</html>