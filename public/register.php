<?php
require __DIR__ . '/vendor/autoload.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

$dsn  = "pgsql:host=localhost;dbname=p33576";
$user = "p33576";
$pass = "uusi_salasana";

try {
    $pdo = new PDO($dsn, $user, $pass, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
    ]);
} catch (PDOException $e) {
    die("Tietokantayhteys epäonnistui: " . $e->getMessage());
}

$name = $_POST['name'];
$email = $_POST['email'];
$password = $_POST['password'];
$password_confirm = $_POST['password_confirm'];

/* 🔎 Tarkistetaan ensin onko sähköposti jo olemassa */
$stmt = $pdo->prepare("SELECT id FROM users WHERE email = ?");
$stmt->execute([$email]);
if ($stmt->fetch()) {
    die("Sähköposti $email on jo rekisteröity. Kirjaudu sisään tai käytä toista osoitetta.");
}


/* 🔒 Tarkistetaan että salasanat täsmäävät */
if ($password !== $password_confirm) {
    die("Salasanat eivät täsmää. Yritä uudelleen.");
}

/* Hashataan salasana vasta kun varmistettu */
$hashedPassword = password_hash($password, PASSWORD_DEFAULT);
$code = bin2hex(random_bytes(16));


/* ✅ Lisätään uusi käyttäjä */
$sql = "INSERT INTO users (name, email, password, role, is_verified, verification_code) 
        VALUES (?, ?, ?, 'student', false, ?)";
$stmt = $pdo->prepare($sql);
$stmt->execute([$name, $email, $password, $code]);

/* 📧 Lähetetään vahvistusviesti */
$mail = new PHPMailer(true);
$mail->CharSet = "UTF-8";
$mail->isSMTP();
$mail->Host = 'smtp.example.com';
$mail->SMTPAuth = true;
$mail->Username = 'your@email.com';
$mail->Password = 'yourpassword';
$mail->SMTPSecure = 'tls';
$mail->Port = 587;

$mail->setFrom('your@email.com', 'Varauskalenteri');
$mail->addAddress($email);
$mail->Subject = 'Vahvista sähköpostisi';
$mail->Body = "Klikkaa linkkiä: https://yourdomain.com/verify.php?code=$code";
$mail->send();

echo "Rekisteröinti onnistui. Tarkista sähköpostisi vahvistusta varten.";
?>
