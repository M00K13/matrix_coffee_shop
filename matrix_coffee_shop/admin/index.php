<?php
// admin/index.php
session_start();
require_once('../../config/database.php');
require_once('../../includes/auth.php');
require_once('../../includes/functions.php');

// Controleer of gebruiker is ingelogd en admin is
if (!is_logged_in() || !is_admin()) {
    header('Location: login.php');
    exit;
}

// Statistieken ophalen
$users_query = "SELECT COUNT(*) as total FROM users";
$users_result = mysqli_query($conn, $users_query);
$users_count = mysqli_fetch_assoc($users_result)['total'];

$products_query = "SELECT COUNT(*) as total FROM products";
$products_result = mysqli_query($conn, $products_query);
$products_count = mysqli_fetch_assoc($products_result)['total'];

$orders_query = "SELECT COUNT(*) as total FROM orders";
$orders_result = mysqli_query($conn, $orders_query);
$orders_count = mysqli_fetch_assoc($orders_result)['total'];

$revenue_query = "SELECT SUM(total_amount) as total FROM orders";
$revenue_result = mysqli_query($conn, $revenue_query);
$revenue = mysqli_fetch_assoc($revenue_result)['total'];

// Recente bestellingen ophalen
$recent_orders_query = "SELECT o.*, u.username FROM orders o JOIN users u ON o.user_id = u.id ORDER BY o.created_at DESC LIMIT 5";
$recent_orders_result = mysqli_query($conn, $recent_orders_query);
$recent_orders = [];
while ($row = mysqli_fetch_assoc($recent_orders_result)) {
    $recent_orders[] = $row;
}
?>

<!DOCTYPE html>
<html lang="nl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - Matrix Coffee Shop</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <style>
        .admin-dashboard {
            display: grid;
            grid-template-columns: 250px 1fr;
            min-height: 100vh;
        }
        
        .admin-sidebar {
            background-color: rgba(0, 50, 0, 0.3);
            padding: 20px;
            border-right: 1px solid #0f0;
        }
        
        .admin-logo {
            text-align: center;
            border-bottom: 1px solid rgba(0, 255, 0, 0.3);
            padding-bottom: 15px;
            margin-bottom: 20px;
        }
        
        .admin-logo h2 {
            font-size: 1.5rem;
        }
        
        .admin-menu {
            list-style: none;
            padding: 0;
        }
        
        .admin-menu li {
            margin-bottom: 10px;
        }
        
        .admin-menu a {
            display: block;
            padding: 10px;
            color: #0f0;
            text-decoration: none;
            border: 1px solid transparent;
            transition: all 0.3s ease;
        }
        
        .admin-menu a:hover,
        .admin-menu a.active {
            background-color: rgba(0, 255, 0, 0.1);
            border-color: #0f0;
        }
        
        .admin-content {
            padding: 20px;
        }
        
        .admin-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 0 0 20px 0;
            border-bottom: 1px solid #0f0;
            margin-bottom: 20px;
        }
        
        .admin-header h1 {
            font-size: 2rem;
        }
        
        .admin-user {
            display: flex;
            align-items: center;
        }
        
        .admin-user .logout-link {
            margin-left: 20px;
        }
        
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }
        
        .stat-card {
            background-color: rgba(0, 50, 0, 0.2);
            border: 1px solid #0f0;
            padding: 20px;
            text-align: center;
        }
        
        .stat-card h3 {
            margin-bottom: 10px;
        }
        
        .stat-card .stat-value {
            font-size: 2rem;
            margin-bottom: 10px;
        }
        
        .recent-orders h2 {
            margin-bottom: 20px;
        }
        
        .orders-table {
            width: 100%;
            border-collapse: collapse;
        }
        
        .orders-table th, .orders-table td {
            padding: 10px;
            border: 1px solid rgba(0, 255, 0, 0.3);
            text-align: left;
        }
        
        .orders-table th {
            background-color: rgba(0, 255, 0, 0.2);
        }
    </style>
</head>
<body class="matrix-theme">
    <div class="admin-dashboard">
        <div class="admin-sidebar">
            <div class="admin-logo">
                <h2>Matrix Admin</h2>
                <p>Beheerderportaal</p>
            </div>
            
            <ul class="admin-menu">
                <li><a href="index.php" class="active">Dashboard</a></li>
                <li><a href="products.php">Producten</a></li>
                <li><a href="users.php">Gebruikers</a></li>
                <li><a href="orders.php">Bestellingen</a></li>
                <li><a href="reports.php">Rapporten</a></li>
                <li><a href="settings.php">Instellingen</a></li>
                <li><a href="../index.php">Naar winkel</a></li>
            </ul>
        </div>
        
        <div class="admin-content">
            <div class="admin-header">
                <h1>Admin Dashboard</h1>
                
                <div class="admin-user">
                    <span>Ingelogd als: <?php echo htmlspecialchars($_SESSION['username']); ?></span>
                    <a href="logout.php" class="logout-link">Uitloggen</a>
                </div>
            </div>
            
            <div class="stats-grid">
                <div class="stat-card">
                    <h3>Gebruikers</h3>
                    <div class="stat-value"><?php echo $users_count; ?></div>
                </div>
                
                <div class="stat-card">
                    <h3>Producten</h3>
                    <div class="stat-value"><?php echo $products_count; ?></div>
                </div>
                
                <div class="stat-card">
                    <h3>Bestellingen</h3>
                    <div class="stat-value"><?php echo $orders_count; ?></div>
                </div>
                
                <div class="stat-card">
                    <h3>Omzet</h3>
                    <div class="stat-value">&euro;<?php echo number_format($revenue, 2); ?></div>
                </div>
            </div>
            
            <div class="recent-orders">
                <h2>Recente Bestellingen</h2>
                
                <?php if (empty($recent_orders)): ?>
                    <p>Geen recente bestellingen gevonden.</p>
                <?php else: ?>
                    <table class="orders-table">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Gebruiker</th>
                                <th>Bedrag</th>
                                <th>Status</th>
                                <th>Datum</th>
                                <th>Actie</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($recent_orders as $order): ?>
                                <tr>
                                    <td>#<?php echo $order['id']; ?></td>
                                    <td><?php echo htmlspecialchars($order['username']); ?></td>
                                    <td>&euro;<?php echo number_format($order['total_amount'], 2); ?></td>
                                    <td><?php echo ucfirst($order['status']); ?></td>
                                    <td><?php echo date('d/m/Y H:i', strtotime($order['created_at'])); ?></td>
                                    <td>
                                        <a href="order.php?id=<?php echo $order['id']; ?>" class="btn">Details</a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                <?php endif; ?>
            </div>
        </div>
    </div>
</body>
</html>
