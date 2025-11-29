<?php
session_start();

// Vain adminilla oikeus
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    exit("Ei oikeuksia.");
}

$dsn  = "pgsql:host=localhost;dbname=p33576";
$user = "p33576";
$pass = "uusi_salasana";
$pdo = new PDO($dsn, $user, $pass, [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);

// käsittele varaus
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['user_id'], $_POST['slot_id'])) {
    $userId = $_POST['user_id'];
    $slotId = $_POST['slot_id'];

    // lisää varaus
    $stmt = $pdo->prepare("INSERT INTO reservations (slot_id, user_id) VALUES (?, ?)");
    $stmt->execute([$slotId, $userId]);

    // merkitse slot varatuksi
    $pdo->prepare("UPDATE slots SET is_booked = true WHERE id = ?")
        ->execute([$slotId]);

    echo "<p>Varaus tehty käyttäjälle ID $userId.</p>";
}

// hae käyttäjät
$users = $pdo->query("SELECT id, name, email FROM users ORDER BY name")->fetchAll(PDO::FETCH_ASSOC);

// hae vapaat slotit
$slots = $pdo->query("SELECT id, start_time, end_time FROM slots WHERE is_booked = false ORDER BY start_time")->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="fi">
<head>
  <meta charset="UTF-8">
  <title>Admin: Varaa aika</title>
  <link rel="stylesheet" href="css/style.css">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
  
  <style>
    form {
      border: 1px solid #ccc;
      padding: 20px;
      border-radius: 6px;
      background: #f9f9f9;
      max-width: 500px;
      margin: 20px auto;
    }
    label, select {
      display: block;
      margin-bottom: 10px;
    }
    button {
      padding: 6px 12px;
      background: #53b7ff;
      color: white;
      border: none;
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
    <a href="reserved.php"><span class="nav-icon">🧾</span> Varatut ajat</a>
     <a href="logout.php"><span class="nav-icon">🚪</span> Kirjaudu ulos</a>
    <a href="add_slot.php"><span class="nav-icon">🛠️</span>Admin</a>
   
  </header>

  <main>
    <h2>Varaa aika kirjautuneelle käyttäjälle</h2>
    <form method="post" action="varaa.php">
      <label for="user_id">Valitse käyttäjä:</label>
      <select name="user_id" required>
        <option value="">-- Valitse käyttäjä --</option>
        <?php foreach ($users as $u): ?>
          <option value="<?= $u['id'] ?>">
            <?= htmlspecialchars($u['name']) ?> (<?= htmlspecialchars($u['email']) ?>)
          </option>
        <?php endforeach; ?>
      </select>

      <label for="slot_id">Valitse vapaa aika:</label>
      <select name="slot_id" required>
        <option value="">-- Valitse aika --</option>
        <?php foreach ($slots as $s): ?>
          <?php
            $start = new DateTime($s['start_time']);
            $end   = new DateTime($s['end_time']);
          ?>
          <option value="<?= $s['id'] ?>">
            <?= $start->format('d.m.Y H:i') ?> – <?= $end->format('H:i') ?>
          </option>
        <?php endforeach; ?>
      </select>

      <button type="submit">Varaa</button>
    </form>
  </main>

  <footer>© 2025 TinData</footer>
</body>
</html>
