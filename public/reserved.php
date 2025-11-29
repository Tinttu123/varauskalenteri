<?php
session_start();
session_start();

// Vain adminilla oikeus
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    exit("Ei oikeuksia.");
}

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

// hae kaikki varatut slotit ja varaajien tiedot
$sql = "SELECT r.id AS reservation_id,
               s.start_time,
               s.end_time,
               u.name,
               u.email
        FROM reservations r
        JOIN slots s ON r.slot_id = s.id
        JOIN users u ON r.user_id = u.id
        ORDER BY s.start_time";
$reservations = $pdo->query($sql)->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="fi">
<head>
  <meta charset="UTF-8">
  <title>Varatut ajat</title>
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <link rel="stylesheet" href="css/style.css">
 
  <link rel="stylesheet" href="css/calendar.css">
  <link rel="stylesheet" href="css/slots.css">
  <style>
    /* Taulukon ympärille scrollaava kontti */
.responsive-table {
  overflow-x: auto;
  width: 100%;
}

/* Taulukko skaalautuu ja rivit katkeavat tarvittaessa */
.calendar {
  width: 100%;
  border-collapse: collapse;
  table-layout: auto;
  word-wrap: break-word;
}

.calendar th, .calendar td {
  border: 1px solid #ddd;
  padding: 8px;
  text-align: center;
  min-width: 100px; /* pakottaa solut pysymään kapeina */
}

/* Kellonajan tyyli */
.time {
  font-weight: bold;
  margin-left: 10px;
}

/* Mobiilissa pienennetään fonttia ja paddingia */
@media (max-width: 600px) {
  .calendar th, .calendar td {
    font-size: 14px;
    padding: 6px;
  }
}
</style>
</head>
<body>
  <header>


  <a href="add_slot.php"><span class="nav-icon">🛠️</span>Admin</a>
    
    <?php if (isset($_SESSION['user_id'])): ?>
      <a href="logout.php"><span class="nav-icon">🚪</span> Kirjaudu ulos</a>
    <?php else: ?>
      <a href="login.php"><span class="nav-icon">🔑</span> Kirjaudu sisään</a>
    <?php endif; ?>
     <a href="varaa.php"><span class="nav-icon">✏️</span> Varaa aika</a>
  </header>

  <main>
    <h2>Kaikki varatut ajat</h2>
    <?php if (empty($reservations)): ?>
      <p>Ei varauksia.</p>
    <?php else: ?>


<div class="responsive-table">



      <table class ="calendar">
        <tr>
          <th>Varaus ID</th>
          <th>Aloitus</th>
          <th>Lopetus</th>
          <th>Nimi</th>
          <th>Sähköposti</th>
        </tr>
        <?php foreach ($reservations as $res): ?>
          <?php
            $start = new DateTime($res['start_time']);
            $end   = new DateTime($res['end_time']);
          ?>
          <tr>
            <td><?= htmlspecialchars($res['reservation_id']) ?></td>
            <td><?= $start->format('d.m.Y') ?><span class="time"><?= $start->format('H:i') ?></span></td>
            <td><?= $end->format('d.m.Y') ?><span class="time"><?= $end->format('H:i') ?></span></td>
            <td><?= htmlspecialchars($res['name']) ?></td>
            <td><?= htmlspecialchars($res['email']) ?></td>
          </tr>
        <?php endforeach; ?>
      </table>
        </div>
    <?php endif; ?>
  </main>

  <footer>© 2025 TinData</footer>
</body>
</html>
