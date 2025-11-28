<?php
session_start();

if (!isset($_SESSION['user_id'])) {
    exit("Ei oikeuksia.");
}

$dsn  = "pgsql:host=localhost;dbname=p33576";
$user = "p33576";
$pass = "uusi_salasana";
$pdo = new PDO($dsn, $user, $pass, [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);

// jos poistopainiketta painettu
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['reservation_id'], $_POST['slot_id'])) {
    $reservationId = $_POST['reservation_id'];
    $slotId        = $_POST['slot_id'];

    // poista varaus
    $pdo->prepare("DELETE FROM reservations WHERE id = ? AND user_id = ?")
        ->execute([$reservationId, $_SESSION['user_id']]);

    // vapauta slot
    $pdo->prepare("UPDATE slots SET is_booked = false WHERE id = ?")
        ->execute([$slotId]);

    // ohjaa takaisin tälle sivulle
    header("Location: reservations.php");
    exit;
}

// hae varaukset
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
?>
<!DOCTYPE html>
<html lang="fi">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Omat varaukset</title>
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
  <h2>Omat varaukset</h2>
  <?php if (empty($reservations)): ?>
    <p>Sinulla ei ole varauksia.</p>
  <?php else: ?>
    <table>
      <tr>
        <th>Varaus ID</th>
        <!--th>Slot ID</th-->
        <th>Aloitusaika</th>
        <th>Lopetusaika</th>
        <th>Toiminnot</th>
      </tr>
      <?php foreach ($reservations as $res): ?>
        <tr>
          <td><?= htmlspecialchars($res['reservation_id']) ?></td>
          <!--td><!?= htmlspecialchars($res['slot_id']) ?></td-->
          <td><?= (new DateTime($res['start_time']))->format('d.m.Y H:i') ?></td>
          <td><?= (new DateTime($res['end_time']))->format('d.m.Y H:i') ?></td>
          <td>
            <form method="post" action="reservations.php"
                  onsubmit="return confirm('Haluatko varmasti poistaa tämän varauksen?');">
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
   <footer>
    © 2025 TinData
  </footer>
</body>
</html>
