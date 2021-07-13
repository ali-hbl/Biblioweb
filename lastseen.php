<?php
/* Script qui récupère le dernier livre consulté et le stocke dans une div
 * afin de générer cette div sur toutes les pages de l'application.
*/
?>

<?php if(!empty($_SESSION['lastseen'])) : ?>
	<hr/>
	<div>
		<h3><u>Dernier livre consulté :</u></h3>
		<p><strong>Titre:</strong> "<em><?= $_SESSION['lastseen']['title']; ?></em>"</p>
	</div>
	<hr/>
<?php else : ?>
	<div>
		<h3><u>Dernier livre consulté :</u></h3>
		<p>Aucun livre consulté.</p>
	</div>
	<hr/>
<?php endif; ?>
