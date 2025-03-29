<?php
// public/login.php
session_start();
require_once('../config/database.php');
require_once('../includes/auth.php');

$error = '';

// Als gebruiker al is ingelogd, doorsturen naar dashboard
if (is_logged_in()) {
    header('Location: dashboard.php');
    exit;
}

// Verwerk het inlogformulier
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = $_POST['username'];
    $password = $_POST['password'];
    
    // Kwetsbare login, geen wachtwoord hashing
    if (login($username, $password)) {
        // Gebruiker met succes ingelogd, doorsturen
        header('Location: dashboard.php');
        exit;
    } else {
        $error = 'Ongeldige gebruikersnaam of wachtwoord';
    }
}
?>

<!DOCTYPE html>
<html lang="nl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Inloggen - Matrix Coffee Shop</title>
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
                <li><a href="login.php" class="active">Inloggen</a></li>
                <li><a href="register.php">Registreren</a></li>
            </ul>
        </nav>
    </header>
    
    <main>
        <div class="form-container">
            <h2>Login om tot de Matrix toe te treden</h2>
            
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
            
            <p>
                Nog geen account? <a href="register.php">Registreer hier</a>
            </p>
            
            <p>
                <a href="forgot_password.php">Wachtwoord vergeten?</a>
            </p>
            
            <!-- Hint voor educatieve doeleinden -->
            <div style="margin-top: 30px; font-size: 0.8em; color: #666;">
                Hint: Probeer eens verschillende inlogmethoden... de controle is niet zo streng.
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
