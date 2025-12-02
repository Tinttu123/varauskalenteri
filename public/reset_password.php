<?php
session_start();
require 'db.php'; // PDO-yhteys

// Tarkistetaan, että token on annettu URL-parametrina
$token = $_GET['token'] ?? '';

if (!$token) {
    $_SESSION['error'] = "Virheellinen tai puuttuva token.";
    header("Location: forgot_password.php");
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Lomakkeelta uusien salasanojen vastaanotto
    $password = $_POST['password'] ?? '';
    $password2 = $_POST['password2'] ?? '';

    if ($password !== $password2) {
        $_SESSION['error'] = "Salasanat eivät täsmää.";
        header("Location: reset_password.php?token=$token");
        exit;
    }

    if (strlen($password) < 8) {
        $_SESSION['error'] = "Salasanan on oltava vähintään 8 merkkiä pitkä.";
        header("Location: reset_password.php?token=$token");
        exit;
    }

    // Etsitään käyttäjä tokenin perusteella
    $stmt = $pdo->prepare("SELECT id FROM users WHERE verification_code = ?");
    $stmt->execute([$token]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$user) {
        $_SESSION['error'] = "Virheellinen tai vanhentunut token.";
        header("Location: forgot_password.php");
        exit;
    }

    // Salasanan hash-laskenta (bcrypt)
    $passwordHash = password_hash($password, PASSWORD_DEFAULT);

    // Päivitetään salasana ja nollataan token
    $stmt = $pdo->prepare("UPDATE users SET password = ?, verification_code = NULL WHERE id = ?");
    $stmt->execute([$passwordHash, $user['id']]);

    $_SESSION['success'] = "Salasana vaihdettu onnistuneesti. Voit nyt kirjautua sisään.";
    header("Location: login.php");
    exit;
}
?>

<!DOCTYPE html>
<html lang="fi">
<head>
    <meta charset="UTF-8">
    <title>Salasanan resetointi</title>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <link rel="stylesheet" href="css/style.css">

</head>
</head>
<body>
<header>
    <a href="register.php"><span class="nav-icon">📝</span> Rekisteröidy</a>
    <a href="login.php"><span class="nav-icon">🔑</span> Kirjaudu</a>
    
    <a href="#calendar.php"><span class="nav-icon">📅</span> Varauskalenteri</a>
</header>

    <?php
    // Näytetään istunto-viestit
    if (!empty($_SESSION['error'])) {
        echo '<p style="color:red;">' . htmlspecialchars($_SESSION['error']) . '</p>';
        unset($_SESSION['error']);
    }
    if (!empty($_SESSION['success'])) {
        echo '<p style="color:green;">' . htmlspecialchars($_SESSION['success']) . '</p>';
        unset($_SESSION['success']);
    }
    ?>
<main>
    <div class="content">
    <h2>Anna uusi salasana</h2>
    <form method="post">
        <label>Uusi salasana:</label><br>
        <input type="password" name="password" required><br>

        <label>Toista uusi salasana:</label><br>
        <input type="password" name="password2" required><br>

        <button type="submit">Vaihda salasana</button>
    </form>
 </div>
  </main>

  <footer>
    © 2025 TinData
  </footer>
</body>
</html> 
	
