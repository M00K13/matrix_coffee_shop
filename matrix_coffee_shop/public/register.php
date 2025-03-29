<?php
// public/register.php
session_start();
require_once('../config/database.php');
require_once('../includes/auth.php');

$error = '';
$success = '';

// Als gebruiker al is ingelogd, doorsturen naar dashboard
if (is_logged_in()) {
    header('Location: dashboard.php');
    exit;
}

// Verwerk het registratieformulier
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = $_POST['username'];
    $password = $_POST['password']; // Kwetsbaar: geen hashing
    $confirm_password = $_POST['confirm_password'];
    $email = $_POST['email'];
    $full_name = $_POST['full_name'];
    
    // Eenvoudige validatie (onvoldoende)
    if ($password != $confirm_password) {
        $error = 'Wachtwoorden komen niet overeen';
    } else {
        // Kwetsbare registratie: geen prepared statements, geen wachtwoord hashing
        if (register_user($username, $password, $email, $full_name)) {
            $success = 'Account succesvol aangemaakt. Je kunt nu inloggen.';
        } else {
            $error = 'Registratie mislukt. Gebruikersnaam of e-mail mogelijk al in gebruik.';
        }
    }
}
?>

<!DOCTYPE html>
<html lang="nl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registreren - Matrix Coffee Shop</title>
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
                <li><a href="login.php">Inloggen</a></li>
                <li><a href="register.php" class="active">Registreren</a></li>
            </ul>
        </nav>
    </header>
    
    <main>
        <div class="form-container">
            <h2>Registreer om de Matrix te betreden</h2>
            
            <?php if ($error): ?>
                <div class="error-message"><?php echo $error; ?></div>
            <?php endif; ?>
            
            <?php if ($success): ?>
                <div class="success-message"><?php echo $success; ?></div>
            <?php endif; ?>
            
            <form method="POST" action="">
                <div class="form-group">
                    <label for="username">Gebruikersnaam:</label>
                    <input type="text" id="username" name="username" required>
                </div>
                
                <div class="form-group">
                    <label for="full_name">Volledige Naam:</label>
                    <input type="text" id="full_name" name="full_name" required>
                </div>
                
                <div class="form-group">
                    <label for="email">E-mail:</label>
                    <input type="email" id="email" name="email" required>
                </div>
                
                <div class="form-group">
                    <label for="password">Wachtwoord:</label>
                    <input type="password" id="password" name="password" required>
                </div>
                
                <div class="form-group">
                    <label for="confirm_password">Bevestig Wachtwoord:</label>
                    <input type="password" id="confirm_password" name="confirm_password" required>
                </div>
                
                <div class="form-group">
                    <button type="submit" class="btn">Registreren</button>
                </div>
            </form>
            
            <p>
                Al een account? <a href="login.php">Log hier in</a>
            </p>
        </div>
    </main>
    
    <footer>
        <div class="copyright">
            &copy; <?php echo date('Y'); ?> Matrix Coffee Shop - Dit is een fictieve winkel voor educatieve doeleinden
        </div>
    </footer>
</body>
</html>
