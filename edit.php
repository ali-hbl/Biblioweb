<?php
session_start();
//Incorporation des données
require ('config.php');	

//Déclaration des variables
$message = "";

//Connexion DB
$link = mysqli_connect(HOSTNAME,USERNAME,PASSWORD,DATABASE);
mysqli_set_charset($link, 'utf8');	//Définit le jeu de caractères

if(!empty($_GET['ref'])){
	$ref = $_GET['ref'];	//var_dump($ref);
	
	if($link){		
		//Nettoyer les données entrantes
		$ref = mysqli_real_escape_string($link,$ref);
		
		//Requête 1 : récupérer les livres
		$query = "SELECT * FROM `books` WHERE books.ref=$ref";
		
		//Envoyer la requête
		$result = mysqli_query($link,$query);
		
		//Vérifier si elle a réussi
		if($result){
			//Traitement des commandes
			$book = mysqli_fetch_assoc($result);
			
			//Libérer la mémoire
			mysqli_free_result($result);			
		} else {
			//Erreur lors de la requête
			$message= "Aucun résultat obtenu.";
		}
		
		//Requête 2 : récupérer les auteurs
		$query = "SELECT id, lastname, firstname FROM authors";
		
		//Envoyer la requête
		$result = mysqli_query($link,$query);
		$authors = [];
		
		//Vérifier si elle a réussi
		if($result){
			//Traitement des commandes
			while($author = mysqli_fetch_assoc($result)){
				$authors[]=$author;
			}			
			//Libérer la mémoire
			mysqli_free_result($result);		
		} else {
			$message= "Aucun auteur trouvé.";
		}
		//Déconnexion DB
		mysqli_close($link);
	} else {
		$message = "Erreur de connexion.";
	}
} elseif(isset($_POST['btEdit'])) {
	if(!empty($_POST['title']) && !empty($_POST['ref']) && !empty($_POST['author_id'])){	//Validation 0
		// var_dump($_POST);
		if(is_numeric($_POST['ref']) && strlen($_POST['title'])<=50 &&  is_numeric($_POST['author_id'])){	//Validation 1

			//Nettoyer les données 
			$ref = mysqli_real_escape_string($link,$_POST['ref']);
			$title = mysqli_real_escape_string($link,$_POST['title']);
			$description = mysqli_real_escape_string($link,$_POST['description']);
			$author_id = mysqli_real_escape_string($link,$_POST['author_id']);
			
			//Préparer la requête (modification)
			$query = "UPDATE books SET title='$title', description='$description', author_id='$author_id' WHERE ref='$ref'";
			
			//Envoyer la requête
			$result = mysqli_query($link,$query);
			
			//Analyser
			if($result && mysqli_affected_rows($link)>0){
				$message = 'Modification réussie.';				
			} else {
				$message = "Problème lors de la modification!";
			}
		} else {
			$message = 'Format de données incorrect.';
		}
	} else {
		$message = "Veuillez remplir tous les champs obligatoires.";
	}
} else {
	//Redirection
	header('Location: liste.php');
	header('Status: 302');
	exit;
}
?>

<!doctype HTML>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Edit PHP</title>
</head>
<body>
<?php if(!empty($_SESSION['login'])) : ?>
		<p><a href="login.php">Se déconnecter</a></p>
<?php endif; ?>

<?php if(!empty($book)) : ?>
<form action="edit.php" method="POST">
	<!-- Champ caché -->
	<input type="hidden" name="method" value="EDIT">
	<input type="hidden" name="ref" value="<?= $book['ref']?>">
	
	<div>
		<label for="title">Titre</label>
		<input type="text" name="title" id="title" value="<?= $book['title']?>">
	</div>
	<div>
		<label for="description">Description</label>
		<textarea name="description" id="description"><?=$book['description']?></textarea>
	</div>
	<div>
		<label for="author_id">Auteur</label>
		<select name="author_id" id="author_id">
			<option>Veuillez choisir un auteur</option>
			<?php foreach($authors as $author) : ?>
			<option value="<?= $author['id']?>" <?php if($book['author_id']== $author['id']) {echo 'selected';} ?>><?= "{$author['firstname']} {$author['lastname']}" ?></option>
			<?php endforeach; ?>
		</select>
	</div>
	
	<button name="btEdit" class="ico-edit">Modifier</button>
</form>
<?php endif; ?>

<p><?= $message ?></p>
<hr/>
<a href="liste.php">Retour vers la liste de livres</a>
</body>
</html>