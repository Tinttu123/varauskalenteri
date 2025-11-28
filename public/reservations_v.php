<?php
session_start();

// varmista että käyttäjä on kirjautunut
if (!isset($_SESSION['user_id'])) {
    exit("Ei oikeuksia.");
}

/* 🔌 Tietokantayhteys */
$dsn  = "pgsql:host=localhost;dbname=p33576";
$user = "p33576";
$pass = "uusi_salasana";

try {
    $pdo = new PDO($dsn, $user, $pass, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
    ]);
} catch (PDOException $e) {
    exit("Tietokantayhteys epäonnistui: " . $e->getMessage());
}

// haetaan reservations + slots
$sql = "SELECT r.id AS reservation_id,
               r.user_id,
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
  <title>Omat varaukset</title>
  <link rel="stylesheet" href="css/style.css">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <style>
    table {
      width: 100%;
      border-collapse: collapse;
      margin-top: 20px;
    }
    th, td {
      border: 1px solid #ddd;
      padding: 8px;
      text-align: center;
    }
    th {
      background: #eee;
    }
  </style>
</head>
<body>
  <header>
    <a href="reservations.php"><span class="nav-icon">📝</span> Omat varaukset</a>
    
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
          <th>Slot ID</th>
          <th>Aloitusaika</th>
          <th>Lopetusaika</th>
        </tr>
        <?php foreach ($reservations as $res): ?>
          <?php
            $start = new DateTime($res['start_time']);
            $end   = new DateTime($res['end_time']);
          ?>
          <tr>
            <td><?= htmlspecialchars($res['reservation_id']) ?></td>
            <td><?= htmlspecialchars($res['slot_id']) ?></td>
            <td><?= htmlspecialchars($start->format('d.m.Y H:i')) ?></td>
            <td><?= htmlspecialchars($end->format('d.m.Y H:i')) ?></td>
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
