<?php
// includes/functions.php
// Algemene hulpfuncties voor de Matrix Coffee Shop
// OPZETTELIJK KWETSBAAR voor educatieve doeleinden

// Haal alle producten op
function get_all_products() {
    global $conn;
    
    $query = "SELECT * FROM products ORDER BY id DESC";
    $result = mysqli_query($conn, $query);
    
    $products = [];
    if ($result) {
        while ($row = mysqli_fetch_assoc($result)) {
            $products[] = $row;
        }
    }
    
    return $products;
}

// Haal een specifiek product op
function get_product($product_id) {
    global $conn;
    
    // KWETSBAAR: Directe invoer in query zonder prepared statements
    $query = "SELECT * FROM products WHERE id = $product_id";
    $result = mysqli_query($conn, $query);
    
    if ($result && mysqli_num_rows($result) > 0) {
        return mysqli_fetch_assoc($result);
    }
    
    return null;
}

// Zoek producten - KWETSBAAR voor SQL injection
function search_products($search_term) {
    global $conn;
    
    // KWETSBAAR: Directe invoer in query zonder sanitization
    $query = "SELECT * FROM products WHERE name LIKE '%$search_term%' OR description LIKE '%$search_term%'";
    $result = mysqli_query($conn, $query);
    
    $products = [];
    if ($result) {
        while ($row = mysqli_fetch_assoc($result)) {
            $products[] = $row;
        }
    }
    
    return $products;
}

// Voeg een product toe aan winkelwagen
function add_to_cart($user_id, $product_id, $quantity = 1) {
    global $conn;
    
    // Controleer of product al in winkelwagen zit
    $check_query = "SELECT * FROM cart_items WHERE user_id = $user_id AND product_id = $product_id";
    $check_result = mysqli_query($conn, $check_query);
    
    if (mysqli_num_rows($check_result) > 0) {
        // Update de hoeveelheid als product al in winkelwagen zit
        $cart_item = mysqli_fetch_assoc($check_result);
        $new_quantity = $cart_item['quantity'] + $quantity;
        
        $update_query = "UPDATE cart_items SET quantity = $new_quantity WHERE id = " . $cart_item['id'];
        return mysqli_query($conn, $update_query);
    } else {
        // Voeg nieuw item toe aan winkelwagen
        $insert_query = "INSERT INTO cart_items (user_id, product_id, quantity) VALUES ($user_id, $product_id, $quantity)";
        return mysqli_query($conn, $insert_query);
    }
}

// Haal winkelwagenitems op
function get_cart_items($user_id) {
    global $conn;
    
    $query = "SELECT c.*, p.name, p.price, p.image_url 
              FROM cart_items c 
              JOIN products p ON c.product_id = p.id 
              WHERE c.user_id = $user_id";
              
    $result = mysqli_query($conn, $query);
    
    $items = [];
    if ($result) {
        while ($row = mysqli_fetch_assoc($result)) {
            $items[] = $row;
        }
    }
    
    return $items;
}

// Bereken het totaal van de winkelwagen
function get_cart_total($user_id) {
    global $conn;
    
    $query = "SELECT SUM(c.quantity * p.price) as total 
              FROM cart_items c 
              JOIN products p ON c.product_id = p.id 
              WHERE c.user_id = $user_id";
              
    $result = mysqli_query($conn, $query);
    
    if ($result) {
        $row = mysqli_fetch_assoc($result);
        return $row['total'] ? $row['total'] : 0;
    }
    
    return 0;
}

// Maak een bestelling aan
function create_order($user_id, $shipping_address) {
    global $conn;
    
    // Haal winkelwagenitems op
    $cart_items = get_cart_items($user_id);
    if (empty($cart_items)) {
        return false;
    }
    
    // Bereken totaalbedrag
    $total = get_cart_total($user_id);
    
    // Begin transactie
    mysqli_begin_transaction($conn);
    
    try {
        // Maak bestelling aan
        $query = "INSERT INTO orders (user_id, total_amount, shipping_address, status) 
                  VALUES ($user_id, $total, '$shipping_address', 'pending')";
        $result = mysqli_query($conn, $query);
        
        if (!$result) {
            throw new Exception("Fout bij aanmaken bestelling");
        }
        
        $order_id = mysqli_insert_id($conn);
        
        // Voeg bestelitems toe
        foreach ($cart_items as $item) {
            $product_id = $item['product_id'];
            $quantity = $item['quantity'];
            $price = $item['price'];
            
            $query = "INSERT INTO order_items (order_id, product_id, quantity, price) 
                      VALUES ($order_id, $product_id, $quantity, $price)";
            $result = mysqli_query($conn, $query);
            
            if (!$result) {
                throw new Exception("Fout bij toevoegen bestelitem");
            }
            
            // Update voorraad (niet geïmplementeerd)
        }
        
        // Leeg winkelwagen
        $query = "DELETE FROM cart_items WHERE user_id = $user_id";
        $result = mysqli_query($conn, $query);
        
        if (!$result) {
            throw new Exception("Fout bij legen winkelwagen");
        }
        
        // Commit transactie
        mysqli_commit($conn);
        return $order_id;
    } catch (Exception $e) {
        // Rollback bij fout
        mysqli_rollback($conn);
        return false;
    }
}

// Log functie - KWETSBAAR: loggt te veel informatie inclusief gevoelige data
function log_activity($user_id, $action, $details = '') {
    $log_dir = '../logs/';
    $log_file = $log_dir . 'activity_log.txt';
    
    // Zorg dat de log map bestaat
    if (!file_exists($log_dir)) {
        mkdir($log_dir, 0777, true);
    }
    
    // Log bericht samenstellen
    $timestamp = date('Y-m-d H:i:s');
    $ip = $_SERVER['REMOTE_ADDR'];
    $user_agent = $_SERVER['HTTP_USER_AGENT'];
    $session_data = print_r($_SESSION, true); // KWETSBAAR: logt sessieinformatie
    
    $log_message = "[$timestamp] User ID: $user_id | IP: $ip | Action: $action | Details: $details\n";
    $log_message .= "User Agent: $user_agent\n";
    $log_message .= "Session Data: $session_data\n";
    $log_message .= "------------------------------------------------------\n";
    
    // Schrijf naar logbestand
    file_put_contents($log_file, $log_message, FILE_APPEND);
}

// XSS-kwetsbare output functie
function output($text) {
    // KWETSBAAR: geen escaping van output
    echo $text;
}

// Voeg een review toe
function add_review($product_id, $user_id, $rating, $comment) {
    global $conn;
    
    // KWETSBAAR: Directe invoer in query zonder prepared statements
    $query = "INSERT INTO reviews (product_id, user_id, rating, comment) 
              VALUES ($product_id, $user_id, $rating, '$comment')";
    
    return mysqli_query($conn, $query);
}

// Haal reviews op voor een product
function get_product_reviews($product_id) {
    global $conn;
    
    $query = "SELECT r.*, u.username 
              FROM reviews r 
              JOIN users u ON r.user_id = u.id 
              WHERE r.product_id = $product_id 
              ORDER BY r.created_at DESC";
    
    $result = mysqli_query($conn, $query);
    
    $reviews = [];
    if ($result) {
        while ($row = mysqli_fetch_assoc($result)) {
            $reviews[] = $row;
        }
    }
    
    return $reviews;
}
?>
