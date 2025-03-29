<?php
// public/profile.php
session_start();
require_once('../config/database.php');
require_once('../includes/auth.php');
require_once('../includes/functions.php');

// Controleer of gebruiker is ingelogd
if (!is_logged_in()) {
    header('Location: login.php');
    exit;
}

// KWETSBAAR: Direct accepteren van user_id parameter (IDOR kwetsbaarheid)
$user_id = isset($_GET['id']) ? $_GET['id'] : $_SESSION['user_id'];
$user = get_user($user_id);

// Als de gebruiker niet bestaat, gebruik de ingelogde gebruiker
if (!$user) {
    $user_id = $_SESSION['user_id'];
    $user = get_user($user_id);
}

$success = '';
$error = '';

// Verwerk profielupdate
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $full_name = isset($_POST['full_name']) ? $_POST['full_name'] : $user['full_name'];
    $email = isset($_POST['email']) ? $_POST['email'] : $user['email'];
    
    // KWETSBAAR: SQL Injection mogelijk door directe invoer in query
    $query = "UPDATE users SET 
                full_name = '$full_name',
                email = '$email'
              WHERE id = $user_id";
    
    if (mysqli_query($conn, $query)) {
        $success = 'Profiel succesvol bijgewerkt!';
        $user = get_user($user_id); // Haal bijgewerkte gegevens op
    } else {
        $error = 'Fout bij bijwerken profiel: ' . mysqli_error($conn);
    }
}
?>

<!DOCTYPE html>
<html lang="nl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mijn Profiel - Matrix Coffee Shop</title>
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
        
        .profile-header {
            display: flex;
            align-items: center;
            margin-bottom: 30px;
        }
        
        .profile-avatar {
            width: 100px;
            height: 100px;
            border-radius: 50%;
            background-color: rgba(0, 255, 0, 0.2);
            display: flex;
            align-items: center;
            justify-content: center;
            margin-right: 20px;
            border: 1px solid #0f0;
            font-size: 2rem;
        }
        
        .profile-meta h2 {
            margin-bottom: 5px;
        }
        
        .profile-meta .user-role {
            color: rgba(0, 255, 0, 0.7);
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
                <li><a href="dashboard.php">Dashboard</a></li>
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
                    <li><a href="dashboard.php">Dashboard</a></li>
                    <li><a href="profile.php" class="active">Mijn Profiel</a></li>
                    <li><a href="orders.php">Bestellingen</a></li>
                    <li><a href="payment_info.php">Betalingsgegevens</a></li>
                    <li><a href="preferences.php">Voorkeuren</a></li>
                </ul>
            </div>
            
            <div class="dashboard-content">
                <div class="profile-header">
                    <div class="profile-avatar">
                        <?php echo strtoupper(substr($user['username'], 0, 1)); ?>
                    </div>
                    <div class="profile-meta">
                        <h2><?php echo htmlspecialchars($user['username']); ?></h2>
                        <div class="user-role"><?php echo ucfirst($user['role']); ?></div>
                    </div>
                </div>
                
                <?php if ($success): ?>
                    <div class="success-message"><?php echo $success; ?></div>
                <?php endif; ?>
                
                <?php if ($error): ?>
                    <div class="error-message"><?php echo $error; ?></div>
                <?php endif; ?>
                
                <div class="profile-form">
                    <h3>Profielgegevens bijwerken</h3>
                    
                    <form method="POST" action="">
                        <div class="form-group">
                            <label for="username">Gebruikersnaam:</label>
                            <input type="text" id="username" value="<?php echo htmlspecialchars($user['username']); ?>" disabled>
                            <small>Gebruikersnaam kan niet worden gewijzigd.</small>
                        </div>
                        
                        <div class="form-group">
                            <label for="full_name">Volledige Naam:</label>
                            <input type="text" id="full_name" name="full_name" value="<?php echo htmlspecialchars($user['full_name']); ?>">
                        </div>
                        
                        <div class="form-group">
                            <label for="email">E-mail:</label>
                            <input type="email" id="email" name="email" value="<?php echo htmlspecialchars($user['email']); ?>">
                        </div>
                        
                        <div class="form-group">
                            <button type="submit" class="btn">Profiel Bijwerken</button>
                        </div>
                    </form>
                </div>
                
                <div class="change-password">
                    <h3>Wachtwoord wijzigen</h3>
                    
                    <form method="POST" action="update_password.php">
                        <div class="form-group">
                            <label for="current_password">Huidig Wachtwoord:</label>
                            <input type="password" id="current_password" name="current_password" required>
                        </div>
                        
                        <div class="form-group">
                            <label for="new_password">Nieuw Wachtwoord:</label>
                            <input type="password" id="new_password" name="new_password" required>
                        </div>
                        
                        <div class="form-group">
                            <label for="confirm_new_password">Bevestig Nieuw Wachtwoord:</label>
                            <input type="password" id="confirm_new_password" name="confirm_new_password" required>
                        </div>
                        
                        <div class="form-group">
                            <button type="submit" class="btn">Wachtwoord Wijzigen</button>
                        </div>
                    </form>
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
