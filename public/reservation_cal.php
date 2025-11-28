<?php
session_start();

$dsn  = "pgsql:host=localhost;dbname=p33576";
$user = "p33576";
$pass = "uusi_salasana";
$pdo = new PDO($dsn, $user, $pass, [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);

// vapaat slotit
$sql = "SELECT * FROM slots WHERE is_booked = false ORDER BY start_time";
$slots = $pdo->query($sql)->fetchAll(PDO::FETCH_ASSOC);

// omat varaukset
$reservations = [];
if (isset($_SESSION['user_id'])) {
    $sql = "SELECT r.id AS reservation_id,
                   r.slot_id,
                   s.start_time,
                   s.end_time
            FROM reservations r
            JOIN slots s ON r.slot_id = s.id
            WHERE r.user_id = ?
            ORDER BY s.start_time";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$_SESSION['user_id']]);
    $reservations = $stmt->fetchAll(PDO::FETCH_ASSOC);
}
?>
<!DOCTYPE html>
<html lang="fi">
<head>
  <meta charset="UTF-8">
  <title>Varauskalenteri</title>
  <link rel="stylesheet" href="css/style.css">
</head>
<body>
  <header>
    <a href="reservations.php"><span class="nav-icon">🧾</span> Omat varaukset</a>
    <?php if (isset($_SESSION['user_id'])): ?>
      <a href="logout.php"><span class="nav-icon">🚪</span> Kirjaudu ulos</a>
    <?php else: ?>
      <a href="login.php"><span class="nav-icon">🔑</span> Kirjaudu sisään</a>
    <?php endif; ?>
    <a href="calendar.php"><span class="nav-icon">📅</span> Varauskalenteri</a>
  </header>

  <main>
    <h2>Vapaat ajat</h2>
    <table class="calendar-table">
      <tr><th>Aloitus</th><th>Lopetus</th><th>Toiminto</th></tr>
      <?php foreach ($slots as $slot): ?>
        <tr>
          <td><?= (new DateTime($slot['start_time']))->format('d.m.Y H:i') ?></td>
          <td><?= (new DateTime($slot['end_time']))->format('d.m.Y H:i') ?></td>
          <td>
            <form action="book.php" method="post">
              <input type="hidden" name="slot_id" value="<?= $slot['id'] ?>">
              <button type="submit">Varaa</button>
            </form>
          </td>
        </tr>
      <?php endforeach; ?>
    </table>

    <?php if (!empty($reservations)): ?>
      <h2>Omat varaukset</h2>
      <table class="calendar-table">
        <tr><th>Aloitus</th><th>Lopetus</th><th>Toiminto</th></tr>
        <?php foreach ($reservations as $res): ?>
          <tr>
            <td><?= (new DateTime($res['start_time']))->format('d.m.Y H:i') ?></td>
            <td><?= (new DateTime($res['end_time']))->format('d.m.Y H:i') ?></td>
            <td>
              <form method="post" action="reservations.php"
                    onsubmit="return confirm('Poistetaanko varaus?');">
                <input type="hidden" name="reservation_id" value="<?= $res['reservation_id'] ?>">
                <input type="hidden" name="slot_id" value="<?= $res['slot_id'] ?>">
                <button type="submit">Poista</button>
              </form>
            </td>
          </tr>
        <?php endforeach; ?>
      </table>
    <?php endif; ?>
  </main>

  <footer>© 2025 TinData</footer>
</body>
</html>
