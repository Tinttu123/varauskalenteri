<?php
session_start();
require 'db.php'; // sisältää $pdo-yhteyden

// Token tulee linkistä (reset_password.php?token=...)
$token = $_GET['token'] ?? '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $newPassword = $_POST['password'] ?? '';

    // Tarkistetaan, että token löytyy
    $stmt = $pdo->prepare("SELECT id FROM users WHERE verification_code = :token");
    $stmt->execute([':token' => $token]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($user) {
        // Hajautetaan uusi salasana
        $hashed = password_hash($newPassword, PASSWORD_BCRYPT);

        // Päivitetään salasana ja tyhjennetään token
        $update = $pdo->prepare("UPDATE users 
                                 SET password = :password, verification_code = NULL 
                                 WHERE id = :id");
        $update->execute([
            ':password' => $hashed,
            ':id'       => $user['id']
        ]);

        $_SESSION['success'] = "Salasana vaihdettu onnistuneesti!";
        header("Location: login.php");
        exit;
    } else {
        $_SESSION['error'] = "Virheellinen tai vanhentunut linkki.";
    }
}
?>

<!DOCTYPE html>
<html lang="fi">
<head>
  <meta charset="UTF-8">
  <title>Vaihda salasana</title>
</head>
<body>
  <?php
  if (isset($_SESSION['error'])) {
      echo "<p style='color:red'>" . $_SESSION['error'] . "</p>";
      unset($_SESSION['error']);
  }
  if (isset($_SESSION['success'])) {
      echo "<p style='color:green'>" . $_SESSION['success'] . "</p>";
      unset($_SESSION['success']);
  }
  ?>
  <form method="post">
    <label>Uusi salasana:</label><br>
    <input type="password" name="password" required><br><br>
    <button type="submit">Vaihda salasana</button>
  </form>
</body>
</html>
