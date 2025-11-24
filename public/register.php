<?php
session_start();
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;
require __DIR__ . '/../vendor/autoload.php';

/* 🔌 Tietokantayhteys */
$dsn  = "pgsql:host=localhost;dbname=p33576";
$user = "p33576";
$pass = "uusi_salasana";

try {
    $pdo = new PDO($dsn, $user, $pass, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
    ]);
} catch (PDOException $e) {
    $_SESSION['error'] = "Tietokantayhteys epäonnistui: " . $e->getMessage();
    header("Location: register.php");
    exit;
}

/* 📝 Lomakkeen käsittely */
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name             = $_POST['name'] ?? '';
    $email            = $_POST['email'] ?? '';
    $password         = $_POST['password'] ?? '';
    $password_confirm = $_POST['password_confirm'] ?? '';

    // Tarkista sähköposti
    $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ?");
    $stmt->execute([$email]);
    if ($stmt->fetch()) {
        $_SESSION['error'] = "Sähköposti $email on jo rekisteröity. Kirjaudu sisään tai käytä toista osoitetta.";
        header("Location: register.php");
        exit;
    }

    // Tarkista salasanat
    if ($password !== $password_confirm) {
        $_SESSION['error'] = "Salasanat eivät täsmää. Yritä uudelleen.";
        header("Location: register.php");
        exit;
    }

    // Hashaa salasana ja luo vahvistuskoodi
    $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
    $code = bin2hex(random_bytes(16));

    // Lisää käyttäjä
    $sql = "INSERT INTO users (name, email, password, role, is_verified, verification_code) 
            VALUES (?, ?, ?, 'student', false, ?)";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$name, $email, $hashedPassword, $code]);

    // Lähetä sähköposti
    $mail = new PHPMailer(true);
    try {
        $mail->CharSet = "UTF-8";
        $mail->isSMTP();
        $mail->Host = 'smtp.gmail.com';
        $mail->SMTPAuth = true;
        $mail->Username = 'tintun.data@gmail.com';
        $mail->Password = 'abel akhv inwr jcol'; // App password
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port = 587;

        $mail->setFrom('tintun.data@gmail.com', 'Varauskalenteri');
        $mail->addAddress($email);
        $mail->Subject = 'Vahvista sähköpostisi';
        $mail->Body    = "Hei $name,\n\nKlikkaa linkkiä vahvistaaksesi tilisi:\n".
                         "https://neutroni.hayo.fi/~p33576/varauskalenteri/public/verify.php?code=$code";

        $mail->send();
        $_SESSION['success'] = "Rekisteröinti onnistui! Tarkista sähköpostisi vahvistusta varten.";
        header("Location: register.php");
        exit;
    } catch (Exception $e) {
        $_SESSION['error'] = "Sähköpostin lähetys epäonnistui: {$mail->ErrorInfo}";
        header("Location: register.php");
        exit;
    }
}
?>

<!DOCTYPE html>
<html lang="fi">
<head>
  <meta charset="UTF-8">
  <title>Rekisteröidy</title>
  <link rel="stylesheet" href="css/style.css">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">

</head>
<body>
  <header>
    <a href="register.php"><span class="nav-icon">📝</span> Rekisteröidy</a>
    <a href="#login"><span class="nav-icon">🔑</span> Kirjaudu</a>
    <a href="#calendar"><span class="nav-icon">📅</span> Varauskalenteri</a>
  </header>

  <main>
    <div class="content">
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

      <form action="register.php" method="post">
        <label>Nimi:</label><br>
        <input type="text" name="name" required><br><br>

        <label>Sähköposti:</label><br>
        <input type="email" name="email" required><br><br>

        <label>Salasana:</label><br>
        <input type="password" name="password" required><br><br>

        <label>Salasana uudelleen:</label><br>
        <input type="password" name="password_confirm" required><br><br>

        <button type="submit">Rekisteröidy</button>
      </form>
    </div>
  </main>

  <footer>
    © 2025 TinData
  </footer>
</body>
</html>
