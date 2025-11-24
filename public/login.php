<?php
session_start();

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
    header("Location: login.php");
    exit;
}

/* 📝 Lomakkeen käsittely */
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email    = $_POST['email'] ?? '';
    $password = $_POST['password'] ?? '';

    $sql = "SELECT * FROM users WHERE email = ?";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$email]);
    $row = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($row && password_verify($password, $row['password'])) {
        if ($row['is_verified']) {
            $_SESSION['user_id'] = $row['id'];
            $_SESSION['role']    = $row['role'];
            $_SESSION['success'] = "Kirjautuminen onnistui!";
            header("Location: calendar.php"); // ohjaa kalenteriin
            exit;
        } else {
            $_SESSION['error'] = "Vahvista ensin sähköpostisi ennen kirjautumista.";
            header("Location: login.php");
            exit;
        }
    } else {
        $_SESSION['error'] = "Virheellinen sähköposti tai salasana.";
        header("Location: login.php");
        exit;
    }
}
?>

<!DOCTYPE html>
<html lang="fi">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Kirjaudu</title>
  <link rel="stylesheet" href="css/style.css">
</head>
<body>
  <header>
    <a href="register.php"><span class="nav-icon">📝</span> Rekisteröidy</a>
    <a href="login.php"><span class="nav-icon">🔑</span> Kirjaudu</a>
    <a href="calendar.php"><span class="nav-icon">📅</span> Varauskalenteri</a>
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

      <form action="login.php" method="post">
        <label>Sähköposti:</label><br>
        <input type="email" name="email" required><br><br>

        <label>Salasana:</label><br>
        <input type="password" name="password" required><br><br>

        <button type="submit">Kirjaudu</button>
      </form>
    </div>
  </main>

  <footer>
    © 2025 TinData
  </footer>
</body>
</html>
