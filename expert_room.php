<?php
require 'secure.php';
?>
<html>
<body>
<h3><strong>The Expert Room</strong></h3>
<p>Bonjour <?= ucfirst($_SESSION['login']);?>, bienvenue dans l'Expert Room.</p>
</body>
</html>