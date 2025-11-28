<?php
session_start();

$dsn  = "pgsql:host=localhost;dbname=p33576";
$user = "p33576";
$pass = "uusi_salasana";
$pdo = new PDO($dsn, $user, $pass, [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);

$slot_id = $_POST['slot_id'];
$user_id = $_SESSION['user_id'];

$sql = "INSERT INTO reservations (slot_id, user_id) VALUES (?, ?)";
$stmt = $pdo->prepare($sql);
$stmt->execute([$slot_id, $user_id]);

$pdo->prepare("UPDATE slots SET is_booked = true WHERE id = ?")->execute([$slot_id]);

// ohjataan reservations.php-sivulle
header("Location: reservations.php");
exit;

?>
