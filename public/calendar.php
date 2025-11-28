<?php
session_start();

if (!isset($_SESSION['user_id'])) {
    exit("Kirjaudu ensin sisään varataksesi aikoja.");
}

$dsn  = "pgsql:host=localhost;dbname=p33576";
$user = "p33576";
$pass = "uusi_salasana";
$pdo = new PDO($dsn, $user, $pass, [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);

// jos varauspainiketta painettu
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['slot_id'])) {
    $slotId = $_POST['slot_id'];
    $userId = $_SESSION['user_id'];

    // lisää varaus
    $stmt = $pdo->prepare("INSERT INTO reservations (slot_id, user_id) VALUES (?, ?)");
    $stmt->execute([$slotId, $userId]);

    // merkitse slot varatuksi
    $pdo->prepare("UPDATE slots SET is_booked = true WHERE id = ?")
        ->execute([$slotId]);

    // ohjaa takaisin kalenteriin
    header("Location: calendar.php");
    exit;
}

// hae vapaat slotit
$sql = "SELECT * FROM slots WHERE is_booked = false ORDER BY start_time";
$slots = $pdo->query($sql)->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="fi">
<head>
  <meta charset="UTF-8">
  <title>Varauskalenteri</title>
  <link rel="stylesheet" href="css/style.css">
  <style>
    .calendar-table {
      width: 100%;
      border-collapse: collapse;
      margin-top: 20px;
    }
    .calendar-table th, .calendar-table td {
      border: 1px solid #ddd;
      padding: 8px;
      text-align: center;
    }
    .calendar-table th {
      background: #eee;
    }
    .time {
      font-weight: bold;
      margin-left: 12px; /* lisää väliä päivämäärän ja kellonajan väliin */
    }
    form { display:inline; }
  </style>
</head>
<body>
  <header>
    <a href="reservations.php"><span class="nav-icon">🧾</span> Omat varaukset</a>
    <a href="logout.php"><span class="nav-icon">🚪</span> Kirjaudu ulos</a>
    <a href="calendar.php"><span class="nav-icon">📅</span> Varauskalenteri</a>
  </header>

  <main>
    <h2>Vapaat ajat</h2>
    <table class="calendar-table">
      <tr><th>Aloitus</th><th>Lopetus</th><th>Toiminto</th></tr>
      <?php foreach ($slots as $slot): ?>
        <?php
          $start = new DateTime($slot['start_time']);
          $end   = new DateTime($slot['end_time']);
        ?>
        <tr>
          <td><?= $start->format('d.m.Y') ?><span class="time"><?= $start->format('H:i') ?></span></td>
          <td><?= $end->format('d.m.Y') ?><span class="time"><?= $end->format('H:i') ?></span></td>
          <td>
            <form method="post" action="calendar.php">
              <input type="hidden" name="slot_id" value="<?= $slot['id'] ?>">
              <button type="submit">Varaa</button>
            </form>
          </td>
        </tr>
      <?php endforeach; ?>
    </table>
  </main>

  <footer>© 2025 TinData</footer>
</body>
</html>
