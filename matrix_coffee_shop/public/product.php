<?php
// public/product.php
session_start();
require_once('../config/database.php');
require_once('../includes/auth.php');
require_once('../includes/functions.php');

// Controleer of product_id is opgegeven
if (!isset($_GET['id'])) {
    header('Location: products.php');
    exit;
}

$product_id = $_GET['id'];
$product = get_product($product_id);

// Als product niet bestaat, doorsturen naar producten pagina
if (!$product) {
    header('Location: products.php');
    exit;
}

// Reviews verwerken indien formulier is verzonden
$review_success = false;
$review_error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && is_logged_in()) {
    $rating = isset($_POST['rating']) ? (int)$_POST['rating'] : 0;
    $comment = isset($_POST['comment']) ? $_POST['comment'] : '';
    
    // Basisvalidatie
    if ($rating < 1 || $rating > 5) {
        $review_error = 'Kies een geldige waardering (1-5)';
    } elseif (empty($comment)) {
        $review_error = 'Vul een recensie in';
    } else {
        // KWETSBAAR: Geen validatie of escaping van comment (XSS mogelijk)
        $user_id = $_SESSION['user_id'];
        
        if (add_review($product_id, $user_id, $rating, $comment)) {
            $review_success = true;
        } else {
            $review_error = 'Fout bij het opslaan van je recensie. Probeer het later opnieuw.';
        }
    }
}

// Haal alle reviews op voor dit product
$reviews = get_product_reviews($product_id);
?>

<!DOCTYPE html>
<html lang="nl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($product['name']); ?> - Matrix Coffee Shop</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <style>
        .product-detail {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 30px;
            max-width: 1200px;
            margin: 30px auto;
            padding: 0 20px;
        }
        
        .product-image {
            border: 1px solid #0f0;
            height: 300px;
            background-color: rgba(0, 50, 0, 0.2);
            display: flex;
            align-items: center;
            justify-content: center;
        }
        
        .product-info h1 {
            margin-bottom: 15px;
        }
        
        .product-description {
            margin-bottom: 20px;
        }
        
        .product-price {
            font-size: 1.5rem;
            margin: 15px 0;
        }
        
        .product-actions {
            margin-top: 30px;
        }
        
        .quantity-control {
            display: flex;
            align-items: center;
            margin-bottom: 20px;
        }
        
        .quantity-control input {
            width: 60px;
            text-align: center;
            margin: 0 10px;
            padding: 8px;
            background-color: #000;
            border: 1px solid #0f0;
            color: #0f0;
        }
        
        .review-section {
            margin-top: 50px;
            max-width: 1200px;
            margin: 50px auto;
            padding: 0 20px;
        }
        
        .reviews-list {
            margin-top: 30px;
        }
        
        .review-card {
            border: 1px solid #0f0;
            padding: 15px;
            margin-bottom: 20px;
            background-color: rgba(0, 50, 0, 0.2);
        }
        
        .review-card .review-header {
            display: flex;
            justify-content: space-between;
            margin-bottom: 10px;
        }
        
        .review-card .rating {
            color: #0f0;
        }
        
        .review-card .review-date {
            color: rgba(0, 255, 0, 0.7);
            font-size: 0.9rem;
        }
        
        .review-form {
            margin-top: 30px;
            border: 1px solid #0f0;
            padding: 20px;
            background-color: rgba(0, 50, 0, 0.2);
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
        <div class="product-detail">
            <div class="product-image">
                <?php if (!empty($product['image_url'])): ?>
                    <img src="<?php echo htmlspecialchars($product['image_url']); ?>" alt="<?php echo htmlspecialchars($product['name']); ?>">
                <?php else: ?>
                    <div style="color: #0f0; font-size: 1.5em;">[ Matrix-koffie Afbeelding ]</div>
                <?php endif; ?>
            </div>
            
            <div class="product-info">
                <h1><?php echo htmlspecialchars($product['name']); ?></h1>
                <div class="product-category">Categorie: <?php echo htmlspecialchars($product['category']); ?></div>
                <div class="product-price">&euro;<?php echo number_format($product['price'], 2); ?></div>
                
                <div class="product-description">
                    <?php echo htmlspecialchars($product['description']); ?>
                </div>
                
                <div class="product-actions">
                    <div class="quantity-control">
                        <button class="btn quantity-btn" onclick="decrementQuantity()">-</button>
                        <input type="number" id="quantity" name="quantity" value="1" min="1">
                        <button class="btn quantity-btn" onclick="incrementQuantity()">+</button>
                    </div>
                    
                    <button class="btn add-to-cart" data-product-id="<?php echo $product['id']; ?>" onclick="addToCartWithQuantity()">Toevoegen aan winkelwagen</button>
                </div>
            </div>
        </div>
        
        <div class="review-section">
            <h2>Klantbeoordelingen</h2>
            
            <?php if ($review_success): ?>
                <div class="success-message">Je recensie is succesvol toegevoegd!</div>
            <?php endif; ?>
            
           <?php if ($review_error): ?>
                <div class="error-message"><?php echo $review_error; ?></div>
            <?php endif; ?>
            
            <!-- Recensies weergeven -->
            <div class="reviews-list">
                <?php if (empty($reviews)): ?>
                    <p>Er zijn nog geen recensies voor dit product. Wees de eerste!</p>
                <?php else: ?>
                    <?php foreach ($reviews as $review): ?>
                        <div class="review-card">
                            <div class="review-header">
                                <div class="reviewer-name">
                                    <strong><?php echo htmlspecialchars($review['username']); ?></strong>
                                </div>
                                <div class="rating">
                                    <?php 
                                    // Toon sterren
                                    for ($i = 1; $i <= 5; $i++) {
                                        echo $i <= $review['rating'] ? '★' : '☆';
                                    }
                                    ?>
                                </div>
                                <div class="review-date">
                                    <?php echo date('d/m/Y', strtotime($review['created_at'])); ?>
                                </div>
                            </div>
                            
                            <div class="review-content">
                                <!-- KWETSBAAR: Geen escaping van commentaar inhoud (XSS mogelijk) -->
                                <?php echo $review['comment']; ?>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
            
            <!-- Recensie formulier -->
            <?php if (is_logged_in()): ?>
                <div class="review-form">
                    <h3>Schrijf een recensie</h3>
                    <form method="POST" action="">
                        <div class="form-group">
                            <label for="rating">Waardering:</label>
                            <select name="rating" id="rating" required>
                                <option value="">-- Selecteer --</option>
                                <option value="5">5 sterren - Uitstekend</option>
                                <option value="4">4 sterren - Zeer goed</option>
                                <option value="3">3 sterren - Goed</option>
                                <option value="2">2 sterren - Matig</option>
                                <option value="1">1 ster - Slecht</option>
                            </select>
                        </div>
                        
                        <div class="form-group">
                            <label for="comment">Je recensie:</label>
                            <textarea name="comment" id="comment" rows="5" required></textarea>
                        </div>
                        
                        <div class="form-group">
                            <button type="submit" class="btn">Plaats recensie</button>
                        </div>
                    </form>
                </div>
            <?php else: ?>
                <div class="login-prompt">
                    <p><a href="login.php">Log in</a> of <a href="register.php">registreer</a> om een recensie te schrijven.</p>
                </div>
            <?php endif; ?>
        </div>
    </main>
    
    <footer>
        <div class="copyright">
            &copy; <?php echo date('Y'); ?> Matrix Coffee Shop - Dit is een fictieve winkel voor educatieve doeleinden
        </div>
    </footer>
    
    <script>
    function incrementQuantity() {
        const quantityInput = document.getElementById('quantity');
        quantityInput.value = parseInt(quantityInput.value) + 1;
    }
    
    function decrementQuantity() {
        const quantityInput = document.getElementById('quantity');
        if (parseInt(quantityInput.value) > 1) {
            quantityInput.value = parseInt(quantityInput.value) - 1;
        }
    }
    
    function addToCartWithQuantity() {
        const productId = document.querySelector('.add-to-cart').getAttribute('data-product-id');
        const quantity = document.getElementById('quantity').value;
        
        // AJAX-verzoek om product toe te voegen aan winkelwagen
        // KWETSBAAR: Geen CSRF-bescherming, directe URL parameters
        fetch(`add_to_cart.php?product_id=${productId}&quantity=${quantity}`, {
            method: 'GET',
            credentials: 'same-origin'
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                showNotification('Product toegevoegd aan winkelwagen', 'success');
                
                // Update winkelwagenaantal
                if (data.cart_count) {
                    const cartLink = document.querySelector('.cart-link');
                    if (cartLink) {
                        cartLink.textContent = `Winkelwagen (${data.cart_count})`;
                    }
                }
            } else {
                showNotification('Fout bij toevoegen aan winkelwagen', 'error');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            showNotification('Er is een fout opgetreden', 'error');
        });
    }
    
    // Notificatie weergave (voor als main.js niet geladen is)
    function showNotification(message, type = 'info') {
        // Maak een notificatie-element
        const notification = document.createElement('div');
        notification.className = `notification ${type}`;
        notification.textContent = message;
        
        // Voeg toe aan body
        document.body.appendChild(notification);
        
        // Stijl toevoegen via JavaScript
        Object.assign(notification.style, {
            position: 'fixed',
            bottom: '20px',
            right: '20px',
            padding: '10px 20px',
            zIndex: '9999',
            borderRadius: '4px',
            color: '#000',
            backgroundColor: type === 'error' ? '#ff6666' : '#00ff00',
            boxShadow: '0 0 10px rgba(0, 255, 0, 0.5)',
            opacity: '0',
            transition: 'opacity 0.3s ease'
        });
        
        // Animatie
        setTimeout(() => {
            notification.style.opacity = '1';
        }, 10);
        
        // Verwijder na 3 seconden
        setTimeout(() => {
            notification.style.opacity = '0';
            setTimeout(() => {
                document.body.removeChild(notification);
            }, 300);
        }, 3000);
    }
    </script>
</body>
</html>
