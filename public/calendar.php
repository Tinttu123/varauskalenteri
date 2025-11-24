<?php
session_start();
require __DIR__ . '/../vendor/autoload.php';

/* 🔌 Tietokantayhteys PostgreSQL:lla */
$dsn  = "pgsql:host=localhost;dbname=p33576";
$user = "p33576";
$pass = "uusi_salasana";

try {
    $pdo = new PDO($dsn, $user, $pass, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
    ]);
} catch (PDOException $e) {
    die("Tietokantayhteys epäonnistui: " . htmlspecialchars($e->getMessage()));
}

/* 📝 Näytetään vapaat ajat */
$sql = "SELECT * FROM slots WHERE is_booked = false ORDER BY start_time";
$slots = $pdo->query($sql)->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="fi">
<head>
  <meta charset="UTF-8">
  <title>Varauskalenteri</title>
  <link rel="stylesheet" href="css/style.css">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
</head>
<body>
  <header>
    <a href="register.php"><span class="nav-icon">📝</span> Rekisteröidy</a>
    <a href="login.php"><span class="nav-icon">🔑</span> Kirjaudu</a>
    <a href="calendar.php"><span class="nav-icon">📅</span> Varauskalenteri</a>
  </header>

  <main>
    <div class="content">
      <h2>Vapaat ajat</h2>
      <table border="1">
        <tr><th>Aloitus</th><th>Lopetus</th><th>Toiminto</th></tr>
        <?php foreach ($slots as $slot): ?>
          <tr>
            <td><?= htmlspecialchars($slot['start_time']) ?></td>
            <td><?= htmlspecialchars($slot['end_time']) ?></td>
            <td>
              <form action="book.php" method="post">
                <input type="hidden" name="slot_id" value="<?= $slot['id'] ?>">
                <button type="submit">Varaa</button>
              </form>
            </td>
          </tr>
        <?php endforeach; ?>
      </table>
    </div>
  </main>

  <footer>
    © 2025 TinData
  </footer>
</body>
</html>
