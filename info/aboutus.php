<?php 
// Referencing your header file
include('../includes/_info_head.php'); 
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Store Design | PXX Mall Flagship</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="<?php echo $path_prefix; ?>../css/app.css">
</head>
<body>

<div class="about-container">
    <div class="about-header">
        <h1>ABOUT PXX Mall</h1>
        <p>The best online clothing platform provider Since 2020.</p>
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
        <h2>Store Design & Aesthetics</h2>
        <div class="content-text">
            <p>
                Our <strong>Retail Flagship Store</strong> in Kuala Lumpur is not just a shop; it is an architectural embodiment of our brand values. 
                Below is a detailed look at the design elements that define our physical presence.
            </p>
        </div>

        <div class="store-list">
            
            <div class="store-item">
                <div class="store-photo">
                    <img src="../images/pxx.jpeg" alt="Front View">
                </div>
                <div class="store-info">
                    <h3>Front Facade</h3>
                    <p>The entrance features a bold industrial-minimalist aesthetic. By utilizing wide-span glass and vivid red structural accents, we create a high-contrast visual identity that invites natural light and showcases our collections to the vibrant streets of Kuala Lumpur.</p>
                </div>
            </div>

            <div class="store-item">
                <div class="store-photo">
                    <img src="../images/pxx2.jpeg" alt="Interior View">
                </div>
                <div class="store-info">
                    <h3>Spatial Interior</h3>
                    <p>Our interior layout follows a "Gallery Flow" concept, designed to eliminate retail clutter. The open-plan space uses neutral tones and professional studio lighting to ensure that the texture and color of every garment are the true stars of the customer experience.</p>
                </div>
            </div>

            <div class="store-item">
                <div class="store-photo">
                    <img src="../images/bg.jpeg" alt="Design Details">
                </div>
                <div class="store-info">
                    <h3>Materiality & Detail</h3>
                    <p>Every corner of PXX Mall reflects premium craftsmanship. From the custom-built display modules to the acoustic damping in our fitting suites, we blend raw industrial materials with warm wood textures to provide a comfortable yet sophisticated shopping atmosphere.</p>
                </div>
            </div>

        </div>
    </div>
</div>

<?php 
// Referencing your footer
if(file_exists('../includes/_foot.php')) {
    include('../includes/_foot.php');
} else {
    echo '<footer style="text-align:center; padding:50px; color:#FF0000; border-top:2px solid #FF0000; font-weight:bold;">© 2026 PXX Mall (Student Project). All Rights Reserved.</footer>';
}
?>

</body>
</html>