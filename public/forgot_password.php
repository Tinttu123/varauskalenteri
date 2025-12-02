<?php
session_start();
require 'db.php'; // sisältää PDO-yhteyden

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = $_POST['email'] ?? '';

    $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ? AND is_verified = true");
    $stmt->execute([$email]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($user) {
        $token = bin2hex(random_bytes(16));
        $stmt = $pdo->prepare("UPDATE users SET verification_code = ? WHERE id = ?");
        $stmt->execute([$token, $user['id']]);

        // Lähetä sähköposti (tässä vain esimerkki)
        mail($email, "Salasanan resetointi", 
             "Klikkaa linkkiä: https://neutroni.hayo.fi/~p33576/varauskalenteri/public/reset_password.php?token=$token");

        $_SESSION['success'] = "Resetointilinkki lähetetty sähköpostiin.";
    } else {
        $_SESSION['error'] = "Sähköpostia ei löydy tai sitä ei ole vahvistettu.";
    }
    header("Location: forgot_password.php");
    exit;
}
?>
<!DOCTYPE html>
<html lang="fi">
<head>
    <meta charset="UTF-8">
    <title>Resetointilinkin lähetys</title>
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
<main>
    <div class="content">
<?php
session_start();
require 'db.php';

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
<form method="post">
  <label>Sähköposti:</label>
  <input type="email" name="email" required>
  <button type="submit">Lähetä resetointilinkki</button>
</form>

</div>
  </main>
  <footer>
    © 2025 TinData
</footer>
</body>
</html> 