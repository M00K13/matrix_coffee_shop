<?php
// public/index.php
session_start();
require_once('../config/database.php');
require_once('../includes/auth.php');
require_once('../includes/functions.php');

// Haal featured producten op
$query = "SELECT * FROM products WHERE featured = 1";
$featured_products = mysqli_query($conn, $query);
?>

<!DOCTYPE html>
<html lang="nl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Matrix Coffee Shop - Voor Hackers Die Diep In De Rabbit Hole Willen Duiken</title>
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body class="matrix-theme">
    <header>
        <div class="logo">
            <h1>MATRIX COFFEE</h1>
            <p class="tagline">De Realiteit Is Optioneel, Koffie Is Essentieel</p>
        </div>
        <nav>
            <ul>
                <li><a href="index.php">Home</a></li>
                <li><a href="products.php">Producten</a></li>
                <li><a href="about.php">Over Ons</a></li>
                <li><a href="contact.php">Contact</a></li>
                <?php if (is_logged_in()): ?>
                    <li><a href="dashboard.php">Dashboard</a></li>
                    <li><a href="logout.php">Uitloggen</a></li>
                <?php else: ?>
                    <li><a href="login.php">Inloggen</a></li>
                    <li><a href="register.php">Registreren</a></li>
                <?php endif; ?>
                <li><a href="cart.php" class="cart-link">Winkelwagen (0)</a></li>
            </ul>
        </nav>
    </header>
    
    <main>
        <section class="hero">
            <div class="hero-content">
                <h2>Welkom in de Matrix</h2>
                <p>Ben je klaar om te ontdekken wat er gebeurt als je de rode pil neemt in je koffie?</p>
                <a href="products.php" class="btn">Ontdek Onze Koffie</a>
            </div>
        </section>
        
        <section class="featured-products">
            <h2>Uitgelichte Koffieblends</h2>
            <div class="products-grid">
                <?php while ($product = mysqli_fetch_assoc($featured_products)): ?>
                    <div class="product-card">
                        <h3><?php echo $product['name']; ?></h3>
                        <p><?php echo $product['description']; ?></p>
                        <div class="product-price">&euro;<?php echo $product['price']; ?></div>
                        <a href="product.php?id=<?php echo $product['id']; ?>" class="btn">Details</a>
                        <button class="btn add-to-cart" data-product-id="<?php echo $product['id']; ?>">Toevoegen</button>
                    </div>
                <?php endwhile; ?>
            </div>
        </section>
    </main>
    
    <footer>
        <div class="footer-content">
            <div class="footer-section">
                <h3>Over Matrix Coffee</h3>
                <p>Onze koffie helpt je de echte werkelijkheid te zien. Drink verantwoordelijk.</p>
            </div>
            <div class="footer-section">
                <h3>Links</h3>
                <ul>
                    <li><a href="privacy.php">Privacy Policy</a></li>
                    <li><a href="terms.php">Gebruiksvoorwaarden</a></li>
                    <li><a href="admin/login.php">Admin</a></li>
                </ul>
            </div>
            <div class="footer-section">
                <h3>Contact</h3>
                <p>Konijnenhol 101<br>1999 ZN<br>Email: info@matrixcoffee.nl</p>
            </div>
        </div>
	<div class="copyright">
            &copy; <?php echo date('Y'); ?> Matrix Coffee Shop - Dit is een fictieve winkel voor educatieve doeleinden
        </div>
    </footer>
    
    <script src="assets/js/main.js"></script>
</body>
</html>
