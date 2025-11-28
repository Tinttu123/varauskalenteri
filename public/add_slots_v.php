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

/* ➕ Admin voi lisätä uusia aikoja */
if (isset($_SESSION['role']) && $_SESSION['role'] === 'admin' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!empty($_POST['start_time']) && !empty($_POST['end_time'])) {
        $start = $_POST['start_time'];
        $end   = $_POST['end_time'];
        $created_by = $_SESSION['user_id'] ?? null;

        $sql = "INSERT INTO slots (start_time, end_time, created_by) VALUES (?, ?, ?)";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$start, $end, $created_by]);

        $message = "Aika lisätty.";
    } else {
        $message = "Kaikki kentät ovat pakollisia.";
    }
}
?>
<!DOCTYPE html>
<html lang="fi">
<head>
  <meta charset="UTF-8">
  <title>Varauskalenteri</title>
  <link rel="stylesheet" href="css/style.css">
  <link rel="stylesheet" href="css/calendar.css">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
</head>
<body>
  <header>
    <a href="register.php"><span class="nav-icon">📝</span> Rekisteröidy</a>
    <?php if (isset($_SESSION['user_id'])): ?>
      <a href="logout.php"><span class="nav-icon">🚪</span> Kirjaudu ulos</a>
    <?php else: ?>
      <a href="login.php"><span class="nav-icon">🔑</span> Kirjaudu sisään</a>
    <?php endif; ?>
    <a href="calendar.php"><span class="nav-icon">📅</span> Varauskalenteri</a>
  </header>

  <main>
    <div class="calendar-layout">
    <div class="content">
      <h2>Vapaat ajat</h2>
      <?php if (!empty($message)): ?>
        <p><?= htmlspecialchars($message) ?></p>
      <?php endif; ?>
      <table class="calendar-table">
        <tr><th>Aloitus</th><th>Lopetus</th><th>Toiminto</th></tr>
        <?php foreach ($slots as $slot): ?>
          <?php
              $start = new DateTime($slot['start_time']);
              $end   = new DateTime($slot['end_time']);
          ?>
          <tr>
            <td><?= htmlspecialchars($start->format('d.m.Y \k\l\o: H:i')) ?></td>
            <td><?= htmlspecialchars($end->format('d.m.Y \k\l\o: H:i')) ?></td>
            <td>
              <form action="book.php" method="post">
                <input type="hidden" name="slot_id" value="<?= $slot['id'] ?>">
                <button type="submit">Varaa</button>
              </form>
            </td>
          </tr>
        <?php endforeach; ?>
      </table>

      <?php if (isset($_SESSION['role']) && $_SESSION['role'] === 'admin'): ?>
        <h2>Lisää uusi aika</h2>
        <form action="add_slots.php" method="post">
          <label>Aloitusaika:</label><br>
          <input type="datetime-local" name="start_time" required><br><br>

          <label>Lopetusaika:</label><br>
          <input type="datetime-local" name="end_time" required><br><br>

          <button type="submit">Lisää aika</button>
        </form>
      <?php endif; ?>
    </div>
  </main>

  <footer>
    © 2025 TinData
  </footer>
</body>
</html>
