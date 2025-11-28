<?php
session_start();
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
    exit("Tietokantayhteys epäonnistui: " . $e->getMessage());
}

// käsitellään lisäys
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'add') {
    if (!empty($_POST['start_time']) && !empty($_POST['end_time'])) {
        $start = $_POST['start_time'];
        $end   = $_POST['end_time'];
        $created_by = $_SESSION['user_id'] ?? null;

        $sql = "INSERT INTO slots (start_time, end_time, created_by) VALUES (?, ?, ?)";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$start, $end, $created_by]);

        echo "<p>Aika lisätty.</p>";
    } else {
        echo "<p>Kaikki kentät ovat pakollisia.</p>";
    }
}

// käsitellään poisto
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'delete') {
    $slotId = $_POST['slot_id'] ?? null;
    if ($slotId) {
        $sql = "DELETE FROM slots WHERE id = ?";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$slotId]);
        echo "<p>Aika poistettu.</p>";
    }
}

// haetaan kaikki slotit
$sql = "SELECT * FROM slots ORDER BY start_time";
$allSlots = $pdo->query($sql)->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="fi">
<head>
  <meta charset="UTF-8">
  <title>Lisää aika</title>
  <link rel="stylesheet" href="css/style.css">
  <link rel="stylesheet" href="css/calendar.css">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <style>
    .layout {
      display: grid;
      grid-template-columns: 1fr 2fr;
      gap: 30px;
      margin-top: 20px;
      align-items: start;
    }
    .add-slot {
      border: 1px solid #ccc;
      padding: 20px;
      border-radius: 6px;
      background: #f9f9f9;
      display: flex;
      flex-direction: column;
      justify-content: center; /* keskitys pystysuunnassa */
      align-items: center;     /* keskitys vaakasuunnassa */
    }
    .slot-table {
      border: 1px solid #ccc;
      padding: 20px;
      border-radius: 6px;
      background: #f9f9f9;
    }
    table {
      width: 100%;
      border-collapse: collapse;
    }
    th, td {
      border: 1px solid #ddd;
      padding: 8px;
      text-align: center;
    }
    th {
      background: #eee;
    }
    button {
      padding: 6px 12px;
      border: none;
      background: #53b7ff;
      color: #fff;
      border-radius: 4px;
      cursor: pointer;
    }
    button:hover {
      background: #1e90ff;
    }
  </style>
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

  <h2>Admin</h2>

  <div class="layout">
    <!-- Lomake vasemmalle -->
    <div class="add-slot">
      <form action="add_slot.php" method="post">
        <input type="hidden" name="action" value="add">
        <label>Aloitusaika:</label><br>
        <input type="datetime-local" name="start_time" required><br><br>

        <label>Lopetusaika:</label><br>
        <input type="datetime-local" name="end_time" required><br><br>

        <button type="submit">Lisää aika</button>
      </form>
    </div>

    <!-- Taulukko oikealle -->
    <div class="slot-table">
      <h3>Kaikki slotit</h3>
      <table>
        <tr>
          <th>ID</th>
          <th>Aloitus</th>
          <th>Lopetus</th>
          <th>Varattu?</th>
          <th>Toiminnot</th>
        </tr>
        <?php foreach ($allSlots as $slot): ?>
          <tr>
            <td><?= htmlspecialchars($slot['id']) ?></td>
            <td><?= htmlspecialchars($slot['start_time']) ?></td>
            <td><?= htmlspecialchars($slot['end_time']) ?></td>
            <td><?= $slot['is_booked'] ? 'Kyllä' : 'Ei' ?></td>
            <td>
              <form action="add_slot.php" method="post" style="display:inline;">
                <input type="hidden" name="action" value="delete">
                <input type="hidden" name="slot_id" value="<?= $slot['id'] ?>">
                <button type="submit">Poista</button>
              </form>
            </td>
          </tr>
        <?php endforeach; ?>
      </table>
    </div>
  </div>
</body>
</html>
