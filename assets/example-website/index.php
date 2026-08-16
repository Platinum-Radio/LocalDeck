<?php
$phpVersion = PHP_VERSION;
$server = $_SERVER['SERVER_SOFTWARE'] ?? 'Apache';
?><!doctype html>
<html lang="nl">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width,initial-scale=1">
  <title>Voorbeeldwebsite · LocalDeck</title>
  <style>
    *{box-sizing:border-box}body{margin:0;min-height:100vh;display:grid;place-items:center;background:#080a0f;color:#edf0f7;font:16px system-ui,sans-serif;padding:24px}main{width:min(780px,100%);padding:48px;border:1px solid #313748;border-radius:26px;background:linear-gradient(145deg,#181c27,#10131a);box-shadow:0 30px 90px #0009}.eyebrow{color:#8f83ff;font-weight:850;letter-spacing:.14em}h1{font-size:clamp(42px,8vw,76px);line-height:.98;margin:18px 0}h1 em{color:#69dbca;font-style:normal}p{color:#adb4c3;line-height:1.7;max-width:660px}.cards{display:grid;grid-template-columns:repeat(3,1fr);gap:12px;margin-top:30px}.card{padding:18px;border:1px solid #292f3d;border-radius:15px;background:#0b0e14}.card b{display:block;color:#fff;margin-bottom:6px}code{color:#69dbca}@media(max-width:650px){main{padding:30px}.cards{grid-template-columns:1fr}}
  </style>
</head>
<body>
  <main>
    <div class="eyebrow">LOCALDECK VOORBEELDWEBSITE</div>
    <h1>De mapmodus<br><em>werkt.</em></h1>
    <p>Deze website staat in <code>websites\voorbeeld-website</code>. Bewerk dit bestand, ververs je browser en je wijziging is direct zichtbaar.</p>
    <div class="cards">
      <div class="card"><b>PHP</b><?=htmlspecialchars($phpVersion)?></div>
      <div class="card"><b>Webserver</b><?=htmlspecialchars($server)?></div>
      <div class="card"><b>Adres</b>localhost/voorbeeld-website</div>
    </div>
  </main>
</body>
</html>
