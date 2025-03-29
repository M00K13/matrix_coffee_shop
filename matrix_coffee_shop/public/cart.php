<?php
// public/cart.php
session_start();
require_once('../config/database.php');
require_once('../includes/auth.php');
require_once('../includes/functions.php');

// Controleer of gebruiker is ingelogd
if (!is_logged_in()) {
    header('Location: login.php?redirect=cart.php');
    exit;
}

$user_id = $_SESSION['user_id'];

// Verwerk actie om item te verwijderen
if (isset($_GET['remove'])) {
    $cart_item_id = $_GET['remove'];
    
    // KWETSBAAR: Geen controle of het item wel van deze gebruiker is (IDOR)
    $query = "DELETE FROM cart_items WHERE id = $cart_item_id";
    mysqli_query($conn, $query);
    
    // Doorsturen om te voorkomen dat vernieuwen het item nogmaals verwijdert
    header('Location: cart.php');
    exit;
}

// Verwerk update van aantal
if (isset($_POST['update_cart'])) {
    foreach ($_POST['quantity'] as $cart_item_id => $quantity) {
        $quantity = (int)$quantity;
        if ($quantity > 0) {
            // KWETSBAAR: Geen controle of het item wel van deze gebruiker is (IDOR)
            $query = "UPDATE cart_items SET quantity = $quantity WHERE id = $cart_item_id";
            mysqli_query($conn, $query);
        }
    }
    
    // Doorsturen om te voorkomen dat vernieuwen de gegevens opnieuw verstuurt
    header('Location: cart.php');
    exit;
}

// Haal winkelwagenitems op
$cart_items = get_cart_items($user_id);
$cart_total = get_cart_total($user_id);
?>

<!DOCTYPE html>
<html lang="nl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Winkelwagen - Matrix Coffee Shop</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <style>
        .cart-container {
            max-width: 1200px;
            margin: 30px auto;
            padding: 0 20px;
        }
        
        .cart-items {
            margin-bottom: 30px;
        }
        
        .cart-table {
            width: 100%;
            border-collapse: collapse;
        }
        
        .cart-table th, 
        .cart-table td {
            padding: 10px;
            border: 1px solid rgba(0, 255, 0, 0.3);
            text-align: left;
        }
        
        .cart-table th {
            background-color: rgba(0, 255, 0, 0.2);
        }
        
        .cart-table .product-name {
            min-width: 200px;
        }
        
        .cart-table .quantity input {
            width: 60px;
            text-align: center;
            padding: 5px;
            background-color: #000;
            border: 1px solid #0f0;
            color: #0f0;
        }
        
        .cart-table .remove-btn {
            color: #ff0000;
            text-decoration: none;
        }
        
        .cart-table .remove-btn:hover {
            text-decoration: underline;
        }
        
        .cart-actions {
            margin-top: 20px;
            display: flex;
            justify-content: space-between;
        }
        
        .cart-summary {
            margin-top: 30px;
            background-color: rgba(0, 50, 0, 0.2);
            border: 1px solid #0f0;
            padding: 20px;
        }
        
        .cart-summary h3 {
            margin-bottom: 15px;
            border-bottom: 1px solid rgba(0, 255, 0, 0.3);
            padding-bottom: 10px;
        }
        
        .cart-total {
            display: flex;
            justify-content: space-between;
            font-size: 1.2rem;
            margin-bottom: 20px;
        }
        
        .checkout-btn {
            display: block;
            width: 100%;
            padding: 12px;
            font-size: 1.1rem;
            text-align: center;
        }
        
        .empty-cart {
            background-color: rgba(0, 50, 0, 0.2);
            border: 1px solid #0f0;
            padding: 30px;
            text-align: center;
        }
    </style>
</head>
<body class="matrix-theme">
    <header>
        <div class="logo">
            <h1>MATRIX COFFEE</h1>
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
                <li><a href="cart.php" class="cart-link active">Winkelwagen</a></li>
            </ul>
        </nav>
    </header>
    
    <main>
        <div class="cart-container">
            <h2>Jouw Winkelwagen</h2>
            
            <?php if (empty($cart_items)): ?>
                <div class="empty-cart">
                    <p>Je winkelwagen is leeg.</p>
                    <a href="products.php" class="btn" style="margin-top: 20px;">Bekijk onze producten</a>
                </div>
            <?php else: ?>
                <form method="POST" action="">
                    <div class="cart-items">
                        <table class="cart-table">
                            <thead>
                                <tr>
                                    <th>Product</th>
                                    <th>Prijs</th>
                                    <th>Aantal</th>
                                    <th>Subtotaal</th>
                                    <th>Actie</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($cart_items as $item): ?>
                                    <tr>
                                        <td class="product-name">
                                            <a href="product.php?id=<?php echo $item['product_id']; ?>">
                                                <?php echo htmlspecialchars($item['name']); ?>
                                            </a>
                                        </td>
                                        <td>&euro;<?php echo number_format($item['price'], 2); ?></td>
                                        <td class="quantity">
                                            <input type="number" name="quantity[<?php echo $item['id']; ?>]" value="<?php echo $item['quantity']; ?>" min="1">
                                        </td>
                                        <td>&euro;<?php echo number_format($item['price'] * $item['quantity'], 2); ?></td>
                                        <td>
                                            <a href="cart.php?remove=<?php echo $item['id']; ?>" class="remove-btn">Verwijderen</a>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                    
                    <div class="cart-actions">
                        <a href="products.php" class="btn">Verder winkelen</a>
                        <button type="submit" name="update_cart" class="btn">Winkelwagen bijwerken</button>
                    </div>
                </form>
                
                <div class="cart-summary">
                    <h3>Bestelsamenvatting</h3>
                    
                    <div class="cart-total">
                        <span>Totaal:</span>
                        <span>&euro;<?php echo number_format($cart_total, 2); ?></span>
                    </div>
                    
                    <a href="checkout.php" class="btn checkout-btn">Afrekenen</a>
                </div>
            <?php endif; ?>
        </div>
    </main>
    
    <footer>
        <div class="copyright">
            &copy; <?php echo date('Y'); ?> Matrix Coffee Shop - Dit is een fictieve winkel voor educatieve doeleinden
        </div>
    </footer>
</body>
</html>
