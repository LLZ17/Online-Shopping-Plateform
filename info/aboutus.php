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

        /* 4-Person Team Grid */
        .team-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
            gap: 25px;
            margin-top: 30px;
        }

        .team-card {
            background: #fff;
            padding: 30px 20px;
            text-align: center;
            border-radius: 4px;
            border: 2px solid #FF0000;
            transition: all 0.3s ease;
        }

        .team-card:hover {
            background-color: #FF0000;
            color: #fff;
            transform: translateY(-10px);
            box-shadow: 0 10px 20px rgba(255, 0, 0, 0.2);
        }

        .team-card h3 {
            color: #FF0000;
            margin-bottom: 10px;
            font-size: 1.4rem;
        }

        .team-card:hover h3 {
            color: #fff;
        }

        .team-card p {
            font-size: 0.9rem;
            font-weight: bold;
            text-transform: uppercase;
        }

        strong {
            color: #FF0000;
        }
        
        /* Highlight Box */
        .highlight-box {
            background: #fff5f5;
            border-right: 4px solid #FF0000;
            padding: 25px;
            margin: 20px 0;
            font-style: italic;
        }
    </style>
</head>
<body>

<div class="about-container">
    <div class="about-header">
        <h1>ABOUT PXX Mall</h1>
        <p>Your best online clothing platform.</p>
    </div>

    <div class="content-section">
        <h2>Our Story</h2>
        <div class="content-text">
            <p>
                Founded in <strong>2020</strong>, <strong>PXX Mall</strong> emerged as a premier online fashion destination dedicated to bringing the latest trends to your doorstep. 
                We believe that fashion is more than just clothing—it is a powerful form of self-expression. 
                Our curation focuses on high-quality <strong>Men's and Women's clothing</strong> designed for the modern individual.
            </p>
            <div class="highlight-box">
                "What started as a small online clothing platform has evolved into a thriving community for fashion enthusiasts. From casual streetwear to elegant professional attire, we ensure style is accessible to everyone."
            </div>
            <p>
                As a digital-first retailer, we eliminate the overhead costs of traditional brick-and-mortar stores. 
                This allows us to provide premium fabrics and cutting-edge designs at competitive prices, ensuring that every piece delivered to you meets our rigorous quality standards.
            </p>
        </div>
    </div>

    <div class="content-section">
        <h2>Why Choose Us?</h2>
        <div class="content-text">
            <p>
                <strong>Trend-Driven Design:</strong> Our team monitors global fashion runways and street culture to ensure our collections are always ahead of the curve.<br>
                <strong>Premium Quality:</strong> From breathable cotton tees to precision-tailored blazers, we prioritize comfort and durability in every thread.<br>
                <strong>Seamless Shopping:</strong> With secure payments and localized logistics, we bring the ultimate shopping experience directly to your screen.
            </p>
        </div>
    </div>

    <div class="content-section">
        <h2>Meet Our Team</h2>
        <div class="team-grid">
            <div class="team-card">
                <h3>Member 1</h3>
                <p>Founder & CEO</p>
            </div>
            <div class="team-card">
                <h3>Member 2</h3>
                <p>Creative Director</p>
            </div>
            <div class="team-card">
                <h3>Member 3</h3>
                <p>Marketing Head</p>
            </div>
            <div class="team-card">
                <h3>Member 4</h3>
                <p>Lead Developer</p>
            </div>
        </div>
    </div>
</div>

<?php 
// Footer implementation
if(file_exists('../includes/_foot.php')) {
    include('../includes/_foot.php');
} else {
    echo '<footer style="text-align:center; padding:30px; color:#FF0000; border-top:2px solid #FF0000; font-weight:bold;">© 2026 PXX Mall. All Rights Reserved.</footer>';
}
?>

</body>
</html>