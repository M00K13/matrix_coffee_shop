<?php
// public/products.php
session_start();
require_once('../config/database.php');
require_once('../includes/auth.php');
require_once('../includes/functions.php');

// Zoekfunctionaliteit
$products = [];
$search_term = '';

if (isset($_GET['search'])) {
    $search_term = $_GET['search'];
    // KWETSBAAR: SQL Injection mogelijk in deze functie
    $products = search_products($search_term);
} else {
    // Alle producten ophalen als er niet wordt gezocht
    $products = get_all_products();
}
?>

<!DOCTYPE html>
<html lang="nl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Onze Koffie - Matrix Coffee Shop</title>
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body class="matrix-theme">
    <header>
        <div class="logo">
            <h1>MATRIX COFFEE</h1>
        </div>
        <nav>
            <ul>
                <li><a href="index.php">Home</a></li>
                <li><a href="products.php" class="active">Producten</a></li>
                <li><a href="about.php">Over Ons</a></li>
                <li><a href="contact.php">Contact</a></li>
                <?php if (is_logged_in()): ?>
                    <li><a href="dashboard.php">Dashboard</a></li>
                    <li><a href="logout.php">Uitloggen</a></li>
                <?php else: ?>
                    <li><a href="login.php">Inloggen</a></li>
                    <li><a href="register.php">Registreren</a></li>
                <?php endif; ?>
                <li><a href="cart.php" class="cart-link">Winkelwagen</a></li>
            </ul>
        </nav>
    </header>
    
    <main>
        <section class="products-header">
            <h2>Onze Koffievarianten</h2>
            <p>Ontdek de smaken die je werkelijkheid veranderen.</p>
            
            <!-- Kwetsbare zoekfunctie -->
            <div class="search-container">
                <form method="GET" action="">
                    <input type="text" name="search" placeholder="Zoek producten..." value="<?php echo htmlspecialchars($search_term); ?>">
                    <button type="submit" class="btn">Zoeken</button>
                </form>
            </div>
        </section>
        
        <section class="products-list">
            <?php if (empty($products)): ?>
                <p class="no-results">Geen producten gevonden die overeenkomen met je zoekopdracht.</p>
            <?php else: ?>
                <div class="products-grid">
                    <?php foreach ($products as $product): ?>
                        <div class="product-card">
                            <h3><?php echo $product['name']; ?></h3>
                            <p><?php echo $product['description']; ?></p>
                            <div class="product-price">&euro;<?php echo $product['price']; ?></div>
                            <div class="product-actions">
                                <a href="product.php?id=<?php echo $product['id']; ?>" class="btn">Details</a>
                                <button class="btn add-to-cart" data-product-id="<?php echo $product['id']; ?>">Toevoegen</button>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
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
