<?php
// public/contact.php
session_start();
require_once('../config/database.php');
require_once('../includes/auth.php');
require_once('../includes/functions.php');

$success = '';
$error = '';

// Verwerk contactformulier
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = isset($_POST['name']) ? $_POST['name'] : '';
    $email = isset($_POST['email']) ? $_POST['email'] : '';
    $subject = isset($_POST['subject']) ? $_POST['subject'] : '';
    $message = isset($_POST['message']) ? $_POST['message'] : '';
    
    // Basisvalidatie
    if (empty($name) || empty($email) || empty($subject) || empty($message)) {
        $error = 'Alle velden zijn verplicht.';
    } else {
        // KWETSBAAR: Command Injection via shell_exec
        // Dit is opzettelijk kwetsbaar voor educatieve doeleinden
        $to = "admin@matrixcoffee.nl";
        $mail_subject = "Contact Form: $subject";
        $mail_message = "Name: $name\nEmail: $email\n\nMessage:\n$message";
        
        // KRITISCH KWETSBAAR: Command Injection mogelijk
        $mail_command = "echo '$mail_message' | mail -s '$mail_subject' $to";
        $result = shell_exec($mail_command);
        
        // Log het verzenden
        if (is_logged_in()) {
            log_activity($_SESSION['user_id'], 'contact_form', "Sent contact form with subject: $subject");
        } else {
            log_activity(0, 'contact_form', "Anonymous user sent contact form with subject: $subject");
        }
        
        $success = 'Je bericht is verzonden. We nemen zo snel mogelijk contact met je op.';
    }
}
?>

<!DOCTYPE html>
<html lang="nl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Contact - Matrix Coffee Shop</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <style>
        .contact-container {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 30px;
            max-width: 1200px;
            margin: 30px auto;
            padding: 0 20px;
        }
        
        .contact-info, .contact-form {
            background-color: rgba(0, 50, 0, 0.2);
            border: 1px solid #0f0;
            padding: 20px;
        }
        
        .contact-info h2, .contact-form h2 {
            margin-bottom: 20px;
            border-bottom: 1px solid rgba(0, 255, 0, 0.3);
            padding-bottom: 10px;
        }
        
        .info-item {
            margin-bottom: 15px;
        }
        
        .info-item h3 {
            margin-bottom: 5px;
        }
        
        @media (max-width: 768px) {
            .contact-container {
                grid-template-columns: 1fr;
            }
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
                <li><a href="contact.php" class="active">Contact</a></li>
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
        <section class="page-banner">
            <h2>Neem Contact Op</h2>
            <p>We zijn er om je te helpen met al je Matrix Coffee vragen.</p>
        </section>
        
        <div class="contact-container">
            <div class="contact-info">
                <h2>Contactgegevens</h2>
                
                <div class="info-item">
                    <h3>Adres</h3>
                    <p>Konijnenhol 101<br>1999 ZN Zion<br>Nederland</p>
                </div>
                
                <div class="info-item">
                    <h3>E-mail</h3>
                    <p>info@matrixcoffee.nl</p>
                </div>
                
                <div class="info-item">
                    <h3>Telefoon</h3>
                    <p>+31 (0)123 456 789</p>
                </div>
                
                <div class="info-item">
                    <h3>Openingstijden</h3>
                    <p>Maandag - Vrijdag: 9:00 - 18:00<br>
                    Zaterdag: 10:00 - 17:00<br>
                    Zondag: Gesloten</p>
                </div>
            </div>
            
            <div class="contact-form">
                <h2>Stuur ons een bericht</h2>
                
                <?php if ($success): ?>
                    <div class="success-message"><?php echo $success; ?></div>
                <?php endif; ?>
                
                <?php if ($error): ?>
                    <div class="error-message"><?php echo $error; ?></div>
                <?php endif; ?>
                
                <form method="POST" action="">
                    <div class="form-group">
                        <label for="name">Naam:</label>
                        <input type="text" id="name" name="name" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="email">E-mail:</label>
                        <input type="email" id="email" name="email" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="subject">Onderwerp:</label>
                        <input type="text" id="subject" name="subject" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="message">Bericht:</label>
                        <textarea id="message" name="message" rows="5" required></textarea>
                    </div>
                    
                    <div class="form-group">
                        <button type="submit" class="btn">Verstuur Bericht</button>
                    </div>
                </form>
            </div>
        </div>
    </main>
    
    <footer>
        <div class="footer-content">
            <div class="footer-section">
                <h3>Over Matrix Coffee</h3>
                <p>Onze koffie helpt je de echte werkelijkheid te zien. Drink verantwoordelijk.</p>
            </div>
            <div class="footer-section">
                <h3>Links</h3>
                <ul>
                    <li><a href="privacy.php">Privacy Policy</a></li>
                    <li><a href="terms.php">Gebruiksvoorwaarden</a></li>
                    <li><a href="admin/login.php">Admin</a></li>
                </ul>
            </div>
            <div class="footer-section">
                <h3>Contact</h3>
                <p>Konijnenhol 101<br>1999 ZN<br>Email: info@matrixcoffee.nl</p>
            </div>
        </div>
        <div class="copyright">
            &copy; <?php echo date('Y'); ?> Matrix Coffee Shop - Dit is een fictieve winkel voor educatieve doeleinden
        </div>
    </footer>
</body>
</html>
