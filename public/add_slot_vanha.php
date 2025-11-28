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

// käsitellään lomakkeen lähetys
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
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
?>
<!DOCTYPE html>
<html lang="fi">
<head>
  <meta charset="UTF-8">
  <title>Lisää aika</title>
    <link rel="stylesheet" href="css/style.css">
  <link rel="stylesheet" href="css/calendar.css">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
</head>
<body>
  <header>
    <a href="#register.php"><span class="nav-icon">📝</span> Rekisteröidy</a>
    <?php if (isset($_SESSION['user_id'])): ?>
      <a href="logout.php"><span class="nav-icon">🚪</span> Kirjaudu ulos</a>
    <?php else: ?>
      <a href="#login.php"><span class="nav-icon">🔑</span> Kirjaudu sisään</a>
    <?php endif; ?>
    <a href="calendar.php"><span class="nav-icon">📅</span> Varauskalenteri</a>
  </header>
  <h2>Admin</h2>


  <form action="add_slot.php" method="post">
    <label>Aloitusaika:</label><br>
    <input type="datetime-local" name="start_time" required><br><br>

    <label>Lopetusaika:</label><br>
    <input type="datetime-local" name="end_time" required><br><br>

    <button type="submit">Lisää aika</button>
  </form>


  
</body>
</html>
