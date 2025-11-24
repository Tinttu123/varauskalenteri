
<?php
$dsn  = "pgsql:host=localhost;dbname=p33576";
$user = "p33576";
$pass = "uusi_salasana";
$pdo = new PDO($dsn, $user, $pass, [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);

$code = $_GET['code'];

$sql = "UPDATE users SET is_verified = true WHERE verification_code = ?";
$stmt = $pdo->prepare($sql);
$stmt->execute([$code]);

//echo "Sähköposti vahvistettu. Voit nyt kirjautua.";
?>
<!DOCTYPE html>
<html lang="fi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sähköposti vahvistettu</title>
    <!-- Linkitetään ulkoinen CSS -->
    <link rel="stylesheet" href="css/style.css">
</head>
<body>

    <!-- Yläpalkki kolmella painikkeella -->
    <header>
        <a href="register.php">
            <span class="nav-icon">📝</span>
            Rekisteröidy
        </a>
        <a href="#login">
            <span class="nav-icon">🔑</span>
            Kirjaudu
        </a>
        <a href="#calendar">
            <span class="nav-icon">📅</span>
            Varauskalenteri
        </a>
    </header>

    <!-- Pääsisältö -->
    <main>
        <div class="content">
            <h1>Sähköposti vahvistettu. Voit nyt kirjautua.</h1>
            
        </div>
    </main>

    <!-- Footer -->
    <footer>
        © 2025 TinData 
    </footer>

</body>
</html>
