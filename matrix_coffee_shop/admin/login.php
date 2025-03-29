<?php
// admin/login.php
session_start();
require_once('../../config/database.php');
require_once('../../includes/auth.php');
require_once('../../includes/functions.php');

$error = '';

// Als gebruiker al is ingelogd en admin is, doorsturen naar admin dashboard
if (is_logged_in() && is_admin()) {
    header('Location: index.php');
    exit;
}

// Verwerk het inlogformulier
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = $_POST['username'];
    $password = $_POST['password'];
    
    // KWETSBAAR: Hardcoded admin credentials - slecht beveiligingspraktijk
    if ($username === 'admin' && $password === 'admin123') {
        // Admin sessie opzetten
        $_SESSION['user_id'] = 1; // Admin gebruiker-ID
        $_SESSION['username'] = 'admin';
        $_SESSION['role'] = 'admin';
        
        // Log deze actie
        log_activity(1, 'admin_login', 'Admin logged in');
        
        // Doorsturen naar admin dashboard
        header('Location: index.php');
        exit;
    } else {
        // Probeer normale inlog
        if (login($username, $password)) {
            // Check of gebruiker admin is
            if ($_SESSION['role'] === 'admin') {
                // Log deze actie
                log_activity($_SESSION['user_id'], 'admin_login', 'Admin logged in');
                
                // Doorsturen naar admin dashboard
                header('Location: index.php');
                exit;
            } else {
                // Geen admin rechten
                logout();
                $error = 'Je hebt geen toegangsrechten voor het admin paneel.';
            }
        } else {
            $error = 'Ongeldige gebruikersnaam of wachtwoord';
        }
    }
}

// KWETSBAAR: Directory listing mogelijk - security misconfiguration
// De directory kan worden bekeken als er geen .htaccess bestand is
?>

<!DOCTYPE html>
<html lang="nl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Login - Matrix Coffee Shop</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <style>
        .admin-container {
            max-width: 500px;
            margin: 100px auto;
            padding: 30px;
            background-color: rgba(0, 50, 0, 0.2);
            border: 1px solid #0f0;
        }
        
        .admin-header {
            text-align: center;
            margin-bottom: 30px;
        }
        
        .admin-header h1 {
            font-size: 2rem;
            margin-bottom: 10px;
        }
        
        .back-link {
            display: block;
            text-align: center;
            margin-top: 20px;
            color: #0f0;
            text-decoration: none;
        }
        
        .back-link:hover {
            text-decoration: underline;
        }
        
        .debug-info {
            margin-top: 30px;
            padding: 10px;
            border: 1px dashed #0f0;
            font-size: 0.8rem;
            color: rgba(0, 255, 0, 0.7);
        }
    </style>
</head>
<body class="matrix-theme">
    <div class="admin-container">
        <div class="admin-header">
            <h1>Matrix Coffee Admin</h1>
            <p>Beheerderportaal</p>
        </div>
        
        <?php if ($error): ?>
            <div class="error-message"><?php echo $error; ?></div>
        <?php endif; ?>
        
        <form method="POST" action="">
            <div class="form-group">
                <label for="username">Gebruikersnaam:</label>
                <input type="text" id="username" name="username" required>
            </div>
            
            <div class="form-group">
                <label for="password">Wachtwoord:</label>
                <input type="password" id="password" name="password" required>
            </div>
            
            <div class="form-group">
                <button type="submit" class="btn">Inloggen</button>
            </div>
        </form>
        
        <a href="../index.php" class="back-link">Terug naar winkel</a>
        
        <!-- Security Misconfiguration: Debug informatie in productie -->
        <div class="debug-info">
            <p>Systeem: XAMPP v3.3.0</p>
            <p>PHP Versie: <?php echo phpversion(); ?></p>
            <p>Auth Module: Version 1.0</p>
            <p>Server: <?php echo $_SERVER['SERVER_SOFTWARE']; ?></p>
            <p>User Agent: <?php echo $_SERVER['HTTP_USER_AGENT']; ?></p>
            <!-- Kwetsbare informatie: Serverpaden -->
            <p>Document Root: <?php echo $_SERVER['DOCUMENT_ROOT']; ?></p>
        </div>
    </div>
</body>
</html>
