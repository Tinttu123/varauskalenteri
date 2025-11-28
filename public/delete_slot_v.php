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

if ($_SERVER['REQUEST_METHOD'] === 'POST' && !empty($_POST['slot_id'])) {
    $slotId = $_POST['slot_id'];
    $sql = "DELETE FROM slots WHERE id = ?";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$slotId]);
    echo "<p>Slot ID $slotId poistettu.</p>";
    echo '<a href="add_slot.php">Takaisin</a>';
} else {
    echo "Virhe: slot_id puuttuu.";
}
?>
