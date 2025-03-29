<?php
// includes/auth.php
// Basis authenticatiefuncties - OPZETTELIJK KWETSBAAR voor educatieve doeleinden

// Check of een gebruiker is ingelogd
function is_logged_in() {
    return isset($_SESSION['user_id']);
}

// Login functie - kwetsbaar voor SQL Injection
function login($username, $password) {
    global $conn;
    
    // KWETSBAAR: Directe invoer in query zonder prepared statements
    $query = "SELECT * FROM users WHERE username = '$username' AND password = '$password'";
    $result = mysqli_query($conn, $query);
    
    // Controle of login succesvol was
    if (mysqli_num_rows($result) == 1) {
        $user = mysqli_fetch_assoc($result);
        
        // Sla gebruikersinformatie op in sessie
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['username'] = $user['username'];
        $_SESSION['role'] = $user['role'];
        
        // Update laatste login
        $update_query = "UPDATE users SET last_login = NOW() WHERE id = " . $user['id'];
        mysqli_query($conn, $update_query);
        
        return true;
    }
    
    return false;
}

// Uitloggen
function logout() {
    // Verwijder alle sessiegegevens
    session_unset();
    session_destroy();
}

// Check of huidige gebruiker admin is
function is_admin() {
    // KWETSBAAR: Geen goede controle, gemakkelijk om te omzeilen
    return isset($_SESSION['role']) && $_SESSION['role'] == 'admin';
}

// Registreer een nieuwe gebruiker
function register_user($username, $password, $email, $full_name) {
    global $conn;
    
    // KWETSBAAR: Geen wachtwoord hashing, directe SQL injectie mogelijk
    $query = "INSERT INTO users (username, password, email, full_name) 
              VALUES ('$username', '$password', '$email', '$full_name')";
    
    $result = mysqli_query($conn, $query);
    return $result;
}

// Haal gebruikersgegevens op basis van ID
function get_user($user_id) {
    global $conn;
    
    // KWETSBAAR: Directe input zonder prepared statement
    $query = "SELECT * FROM users WHERE id = $user_id";
    $result = mysqli_query($conn, $query);
    
    if ($result && mysqli_num_rows($result) > 0) {
        return mysqli_fetch_assoc($result);
    }
    
    return null;
}
?>
