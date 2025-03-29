<?php
// public/add_to_cart.php
session_start();
require_once('../config/database.php');
require_once('../includes/auth.php');
require_once('../includes/functions.php');

// Zet headers voor JSON respons
header('Content-Type: application/json');

// Controleer of gebruiker is ingelogd
if (!is_logged_in()) {
    echo json_encode(['success' => false, 'message' => 'Je moet ingelogd zijn om producten toe te voegen.']);
    exit;
}

// Controleer of product_id is opgegeven
if (!isset($_GET['product_id'])) {
    echo json_encode(['success' => false, 'message' => 'Geen product opgegeven.']);
    exit;
}

$user_id = $_SESSION['user_id'];
$product_id = $_GET['product_id'];
$quantity = isset($_GET['quantity']) ? (int)$_GET['quantity'] : 1;

// KWETSBAAR: Geen verdere validatie van product_id of quantity
// CSRF kwetsbaar: Geen CSRF-token controle

// Voeg toe aan winkelwagen
if (add_to_cart($user_id, $product_id, $quantity)) {
    // Log de actie
    log_activity($user_id, 'add_to_cart', "Added product ID: $product_id, quantity: $quantity");
    
    // Haal winkelwagen aantal op
    $cart_items = get_cart_items($user_id);
    $cart_count = 0;
    foreach ($cart_items as $item) {
        $cart_count += $item['quantity'];
    }
    
    echo json_encode([
        'success' => true, 
        'message' => 'Product toegevoegd aan winkelwagen.',
        'cart_count' => $cart_count
    ]);
} else {
    echo json_encode(['success' => false, 'message' => 'Fout bij toevoegen aan winkelwagen.']);
}
?>
