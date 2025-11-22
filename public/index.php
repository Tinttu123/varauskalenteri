<?php
// Suoritetaan projektin alustusskripti.
require_once '../src/init.php';

  // Siistitään polku urlin alusta ja mahdolliset parametrit urlin lopusta.
  // Siistimisen jälkeen osoite /~koodaaja/lanify/tapahtuma?id=1 on 
  // lyhentynyt muotoon /tapahtuma.
 // $request = str_replace('/~p33576/varauskalenteri','',$_SERVER['REQUEST_URI']);
  $request = str_replace($config['urls']['baseUrl'],'',$_SERVER['REQUEST_URI']);

  $request = strtok($request, '?');

  // Selvitetään mitä sivua on kutsuttu ja suoritetaan sivua vastaava 
  // käsittelijä.
  if ($request === '/' || $request === '/tapahtumat') {
    echo '<h1>Kaikki tapahtumat</h1>';
  } else if ($request === '/tapahtuma') {
    echo '<h1>Yksittäisen tapahtuman tiedot</h1>';
  } else {
    echo '<h1>Pyydettyä sivua ei löytynyt :(</h1>';
  }

?> 



<!DOCTYPE html>
<html lang="fi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Varauskalenteri</title>
    <!-- Linkitetään ulkoinen CSS -->
    <link rel="stylesheet" href="css/style.css">
</head>
<body>

    <!-- Yläpalkki kolmella painikkeella -->
    <header>
        <a href="../register.html">
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
            <h1>VARAUSKALENTERI</h1>
            <p>Kirjaudu tai reksiteröidy. Tervetuloa!</p>
            <hr>
            Pääkäyttäjä voi kirjautua 
            <a href="#" class="btn">tästä.</a>
        </div>
    </main>

    <!-- Footer -->
    <footer>
        © 2025 TinData 
    </footer>

</body>
</html>
