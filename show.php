<?php
session_start();
//var_dump($_GET);

error_reporting(E_ALL);

require 'config.php';

//Mocking des données
/*
$book = [
	'ref' => 4,
	'title' => 'Mon bel oranger',
	'description' => 'Mon bel oranger est un roman de José Mauro de Vasconcelos publié en 1968, partiellement autobiographique, ayant connu un succès international. Traduit en 12 langues, il est publié dans 19 pays, et vendu en France à 3 millions d\'exemplaires.',
	'cover_url' => 'covers/mon-bel-oranger.jpg',
	'firstname' => 'José Mauro',
	'lastname' => 'de Vasconcellos',
	'return_date' => '2020-04-01',	// OU null OU ''
];
*/

$message = "";

if(!empty($_GET['ref'])) {	//Récupérer le livre
	//Se connecter au serveur de DB
	$link = mysqli_connect(HOSTNAME,USERNAME,PASSWORD,DATABASE);
	
	if(mysqli_errno($link)===0) {
		//Définir le jeu de caractères
		mysqli_set_charset($link, 'utf8');
		
		//Préparer la requête
		$ref = mysqli_real_escape_string($link,$_GET['ref']);
		
		$query = "SELECT `ref`,`title`,`description`,`cover_url`, `firstname`,`lastname`,`return_date` FROM `books` 
			JOIN `authors` ON books.author_id = authors.id LEFT JOIN `loans` ON books.ref=loans.book_id
			WHERE books.ref = $ref";
			
		//Envoyer la requête
		$result = mysqli_query($link, $query);
			//var_dump($result);
		
		if($result) {
			//Extraire les données du résultat
			$book = mysqli_fetch_assoc($result);
			$_SESSION['lastseen'] = $book;
				
			//Libérer le jeu de résultat
			mysqli_free_result($result);
		} else {
			$message = "Livre non trouvé.";
			$book = [];
		}
		
		//Fermer la connexion
		mysqli_close($link);
	} else {
		var_dump('ERROR');
	}
} else {
	//Redirection vers la liste
	header('Location: liste.php', 302);
	exit;
}
?>
<!doctype html>
<html lang="fr">
<head>
<title>DB Access :: Show</title>
	<meta charset="utf-8">
	<link rel="stylesheet" href="./css/style.css">
</head>

<body>
<?php if(!empty($book)): ?>
<article>
	<figure>
	<?php if(!empty($book['cover_url'])) : ?>
		<img src="<?= IMG_FOLDER.$book['cover_url'] ?>" alt="<?= $book['title'] ?>" width="220">
	<?php else: ?>
		<img src="<?= IMG_FOLDER.'images/default.jpg' ?>" alt="No cover" width="220">
	<?php endif; ?>
		<figcaption><?= $book['title'] ?></figcaption>
		<p class="author"><?= "{$book['firstname']} {$book['lastname']}" ?></p>
	</figure>
	<section>
		<div class="description">
		<?php if(!empty($book['description'])): ?>
			<?= $book['description'] ?>
		<?php else: ?>
			Aucune description disponible.
		<?php endif; ?>	
		</div>
	<?php if(!empty($book['return_date'])): ?>		
		<p class="due-date">Emprunté jusqu'au <?= $book['return_date'] ?></p>
	<?php endif; ?>
	</section>
</article>
<?php else: ?>
<p><?= $message; ?></p>
<?php endif; ?>

<!--DERNIER LIVRE CONSULTE-->
<?php include 'lastseen.php'; ?>

<a href="liste.php">Retour vers la liste des livres</a>
</body>
</html>
