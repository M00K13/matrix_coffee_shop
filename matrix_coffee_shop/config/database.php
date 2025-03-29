<?php
// config/database.php
// Databaseconfiguratie voor Matrix Coffee Shop
// OPZETTELIJK KWETSBAAR voor educatieve doeleinden

// Databasecredentials - onveilig opgeslagen
$db_host = 'localhost';
$db_user = 'root';     // Opzettelijk root gebruiker, slecht praktijk
$db_password = '';     // Leeg wachtwoord, slecht praktijk
$db_name = 'matrix_coffee_shop';

// Database verbinding maken (zonder foutafhandeling, slecht praktijk)
$conn = mysqli_connect($db_host, $db_user, $db_password, $db_name);

// Geen foutafhandeling, één van de kwetsbaarheden
// Correct zou zijn:
// if (!$conn) {
//     die("Connection failed: " . mysqli_connect_error());
// }

// Geen charset instelling
// mysqli_set_charset($conn, "utf8mb4");
?>
