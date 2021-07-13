<?php
session_start();				//echo '<pre>';	var_dump($_SESSION);	echo'</pre>';
require ('config.php');			//Incorporation des données

$message = "";
$books = [];

if(isset($_GET['btSearch'])) {		//Validation 0
	if(!empty($_GET['author'])) {	//Validation 1
		$author = $_GET['author'];
		
		//Enregistrer la recherche dans la session
		$_SESSION['lastSearches'][] = $author;
		
		if(sizeof($_SESSION['lastSearches'])>3) {		//S'il ya + de 3 recherches...
			array_shift($_SESSION['lastSearches']);		//... suppression du premier
		}
		echo '<pre>'; var_dump($_SESSION); echo '</pre>';
		
		//Connexion DB
		$link = mysqli_connect(HOSTNAME, USERNAME, PASSWORD, DATABASE);
		mysqli_set_charset($link, 'utf8');		//Définit le jeu de caractères

		if($link) {
			//Nettoyer les données entrantes
			$author = mysqli_real_escape_string($link, $author);
			
			//Préparer la requête
			$query = "SELECT * FROM `books` JOIN authors ON author_id=authors.id WHERE authors.lastname='$author' ORDER BY author_id";
			
			//Envoyer la requête
			$result = mysqli_query($link, $query);		
			
			//Vérifier si elle a réussi
			if($result) {
				//Traitement des commandes
				$books = mysqli_fetch_all($result, MYSQLI_ASSOC);		//var_dump($authors);
				
				//Libérer le jeu de résultats
				mysqli_free_result($result);

				//Formatage de la liste des livres
				foreach($books as $book) {
					$final[$book['firstname']][] = $book;				//Ajouter le livre
				}
				$books = $final;
				unset($final);
			} else {
				$message = "Erreur de requête.";
			}
			//Déconnexion DB
			mysqli_close($link);			
		} else {
			$message = "Erreur de connexion.";
		}	
	} else {
		$message = "Veuillez entrer un nom d'auteur svp.";
	}
}
?>
<!doctype html>
<html lang="fr">
<head>
	<meta charset="utf-8">
	<title>Search PHP</title>
</head>
<body>
<form name="frmSearch" action="<?= $_SERVER['PHP_SELF']; ?>" method="GET">
	<input type="text" name="author"/>
	<button name="btSearch">Rechercher</button>
</form>
<section>
<?php if(isset($author)) : ?>
	<h3>Résultat pour <?= htmlentities($author) ?> :</h3>

	<?php foreach($books as $prenom => $livres) : ?>
	<div>
		<h4><?= "{$prenom} {$livres[0]['lastname']}" ?></h4>
		<ul>
		<?php foreach($livres as $livre) : ?>		
			<li><?= $livre['title'] ?></li>
		<?php endforeach; ?>	
		</ul>
	</div>
	<?php endforeach; ?>
<?php endif; ?>
</section>
	<hr/>
	<p><?= $message ?></p>
	<a href="liste.php">Retour vers la liste des livres</a>
</body>
</html>