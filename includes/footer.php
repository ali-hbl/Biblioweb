</main>
<footer>
<?php if(!empty($_SESSION['login']) && $_SESSION['statut'] == 'admin') : ?>
<nav>
	<ul>
		<li><a href="#">Gérer les autres</a>
		<li><a href="#">Gérer les emprunts</a>
		<li><a href="users.php">Promouvoir un membre</a>
	</ul>
</nav>
<?php endif; ?>
	<p>&copy; EPFC &dot; 2021</p>
</footer>
</body>
</html>