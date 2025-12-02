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
<form method="post">
  <label>Sähköposti:</label>
  <input type="email" name="email" required>
  <button type="submit">Lähetä resetointilinkki</button>
</form>
