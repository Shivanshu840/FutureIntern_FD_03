<?php
function registerUser($username, $email, $password) {
    global $pdo;

    // Check if the user already exists
    $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ?");
    $stmt->execute([$email]);
    if ($stmt->rowCount() > 0) {
        return false; // User already exists
    }

    // Hash the password
    $hashedPassword = password_hash($password, PASSWORD_BCRYPT);

    // Insert user into database
    $stmt = $pdo->prepare("INSERT INTO users (username, email, password) VALUES (?, ?, ?)");
    return $stmt->execute([$username, $email, $hashedPassword]);
}

function loginUser($email, $password) {
    global $pdo;

    // Check if the user exists
    $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ?");
    $stmt->execute([$email]);
    $user = $stmt->fetch();

    if ($user && password_verify($password, $user['password'])) {
        // Start session and store user info
        session_start();
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['username'] = $user['username'];
        $_SESSION['role'] = $user['role'];
        return true;
    }

    return false;
}

function isAuthenticated() {
    session_start();
    return isset($_SESSION['user_id']);
}

function isAdmin() {
    return isset($_SESSION['role']) && $_SESSION['role'] === 'admin';
}

function redirectIfAuthenticated() {
    if (isAuthenticated()) {
        header('Location: dashboard.php');
        exit();
    }
}

function redirectIfNotAuthenticated() {
    if (!isAuthenticated()) {
        header('Location: login.php');
        exit();
    }
}

function logoutUser() {
    session_start();
    session_unset();
    session_destroy();
    header('Location: index.php');
}
?>
