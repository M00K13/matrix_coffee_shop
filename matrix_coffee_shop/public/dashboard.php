<?php
// public/dashboard.php
session_start();
require_once('../config/database.php');
require_once('../includes/auth.php');
require_once('../includes/functions.php');

// Controleer of gebruiker is ingelogd
if (!is_logged_in()) {
    header('Location: login.php');
    exit;
}

// Haal gebruikersgegevens op
$user_id = $_SESSION['user_id'];
$user = get_user($user_id);

// Haal recente bestellingen op
$orders_query = "SELECT * FROM orders WHERE user_id = $user_id ORDER BY created_at DESC LIMIT 5";
$orders_result = mysqli_query($conn, $orders_query);
$orders = [];
if ($orders_result) {
    while ($row = mysqli_fetch_assoc($orders_result)) {
        $orders[] = $row;
    }
}

// Log deze activiteit
log_activity($user_id, 'view_dashboard', 'User viewed their dashboard');
?>

<!DOCTYPE html>
<html lang="nl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - Matrix Coffee Shop</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <style>
        .dashboard-container {
            display: grid;
            grid-template-columns: 250px 1fr;
            gap: 30px;
            max-width: 1200px;
            margin: 30px auto;
            padding: 0 20px;
        }
        
        .dashboard-sidebar {
            background-color: rgba(0, 50, 0, 0.2);
            border: 1px solid #0f0;
            padding: 20px;
        }
        
        .dashboard-sidebar ul {
            list-style: none;
            padding: 0;
        }
        
        .dashboard-sidebar li {
            margin-bottom: 10px;
        }
        
        .dashboard-sidebar a {
            display: block;
            padding: 10px;
            color: #0f0;
            text-decoration: none;
            border: 1px solid transparent;
            transition: all 0.3s ease;
        }
        
        .dashboard-sidebar a:hover, 
        .dashboard-sidebar a.active {
            background-color: rgba(0, 255, 0, 0.1);
            border-color: #0f0;
        }
        
        .dashboard-content {
            padding: 20px;
            background-color: rgba(0, 50, 0, 0.2);
            border: 1px solid #0f0;
        }
        
        .user-stats {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }
        
        .stat-card {
            padding: 15px;
            background-color: rgba(0, 50, 0, 0.3);
            border: 1px solid #0f0;
            text-align: center;
        }
        
        .stat-card .stat-value {
            font-size: 2rem;
            margin: 10px 0;
        }
        
        .orders-table {
            width: 100%;
            border-collapse: collapse;
        }
        
        .orders-table th, 
        .orders-table td {
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
                <li><a href="dashboard.php" class="active">Dashboard</a></li>
                <li><a href="logout.php">Uitloggen</a></li>
                <li><a href="cart.php" class="cart-link">Winkelwagen</a></li>
            </ul>
        </nav>
    </header>
    
    <main>
        <div class="dashboard-container">
            <div class="dashboard-sidebar">
                <h3>Menu</h3>
                <ul>
                    <li><a href="dashboard.php" class="active">Dashboard</a></li>
                    <li><a href="profile.php">Mijn Profiel</a></li>
                    <li><a href="orders.php">Bestellingen</a></li>
                    <li><a href="payment_info.php">Betalingsgegevens</a></li>
                    <li><a href="preferences.php">Voorkeuren</a></li>
                </ul>
            </div>
            
            <div class="dashboard-content">
                <h2>Welkom terug, <?php echo htmlspecialchars($user['username']); ?></h2>
                
                <div class="user-stats">
                    <div class="stat-card">
                        <div class="stat-title">Loyalty Punten</div>
                        <div class="stat-value"><?php echo $user['loyalty_points']; ?></div>
                    </div>
                    <div class="stat-card">
                        <div class="stat-title">Credits</div>
                        <div class="stat-value">&euro;<?php echo number_format($user['credits'], 2); ?></div>
                    </div>
                    <div class="stat-card">
                        <div class="stat-title">Lid sinds</div>
                        <div class="stat-value"><?php echo date('d/m/Y', strtotime($user['created_at'])); ?></div>
                    </div>
                </div>
                
                <div class="recent-orders">
                    <h3>Recente Bestellingen</h3>
                    
                    <?php if (empty($orders)): ?>
                        <p>Je hebt nog geen bestellingen geplaatst.</p>
                    <?php else: ?>
                        <table class="orders-table">
                            <thead>
                                <tr>
                                    <th>Bestelnr.</th>
                                    <th>Datum</th>
                                    <th>Bedrag</th>
                                    <th>Status</th>
                                    <th>Actie</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($orders as $order): ?>
                                    <tr>
                                        <td>#<?php echo $order['id']; ?></td>
                                        <td><?php echo date('d/m/Y', strtotime($order['created_at'])); ?></td>
                                        <td>&euro;<?php echo number_format($order['total_amount'], 2); ?></td>
                                        <td><?php echo ucfirst($order['status']); ?></td>
                                        <td>
                                            <!-- KWETSBAAR: IDOR - Geen controle of de gebruiker deze bestelling mag zien -->
                                            <a href="order_detail.php?id=<?php echo $order['id']; ?>" class="btn">Details</a>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </main>
    
    <footer>
        <div class="copyright">
            &copy; <?php echo date('Y'); ?> Matrix Coffee Shop - Dit is een fictieve winkel voor educatieve doeleinden
        </div>
    </footer>
</body>
</html>
