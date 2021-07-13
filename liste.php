<?php
session_start();
// echo '<pre>';	var_dump($rentedBooks);	echo'</pre>';
require 'config.php';

//Déclarations diverses
$message = "";
$books = [];
$title = "";
$rentedTitles = [];

//Sécurisation
if(!empty($_GET['title'])) {
	$title = $_GET['title'];
} elseif(!empty($_POST['title'])) {
	$title = $_POST['title'];
}

//Afficher les livres
//Connexion DB
$link = mysqli_connect(HOSTNAME,USERNAME,PASSWORD,DATABASE);

if($link) {
	mysqli_set_charset($link, 'utf8');
	
	if(!empty($title)) {
		$title = mysqli_real_escape_string($link, $title);
		$query = "SELECT * FROM books WHERE title='$title'";
	} else {
		$query = "SELECT * FROM books";
	}
	$result = mysqli_query($link, $query);	//var_dump($result);

	if($result) {
		while(($book = mysqli_fetch_assoc($result))) {
			$books[] = $book;
		}		//var_dump($books);
		
		$fields = mysqli_fetch_fields($result);		//var_dump($fields);
	}
	mysqli_free_result($result);

	//Récupérer livres empruntés
	// Préparer
	$query = "SELECT book_id FROM loans";	
	// Envoyer
	$result = mysqli_query($link, $query);	
	// Vérifier
	if($result) {
		// Traitement
		while(($rentedBook = mysqli_fetch_assoc($result)) !== null) {	//Tant qu'il y a des livres loués...
			$rentedBooks[] = $rentedBook['book_id'];					//... injecte les dans le tableau
		}	//var_dump($rentedBooks);
	}
	// Libérer la mémoire
	mysqli_free_result($result);
	
	//Déconnexion DB
	mysqli_close($link);
} else {
	$message = "Erreur de connexion.";
}

//Emprunter un livre
if(isset($_POST['btBorrow'])) {		//Validation 0
	if(isset($_POST['ref'])) {		//Validation 1
		$bookId = $_POST['ref'];
        $userId = $_SESSION['id'];
		$dateRetour = date('Y-m-d', mktime(23, 59, 59, date('m'), date('j') + 14));	//Dans 2 semaines

		//Connexion DB
		$link = mysqli_connect(HOSTNAME,USERNAME,PASSWORD,DATABASE);
		
		if($link) {
			mysqli_set_charset($link, 'utf8');
			
			//Nettoyer les données entrantes
			$bookId = mysqli_real_escape_string($link, $_POST['ref']);
			
			//Vérifier si le livre n'est pas déjà emprunté
			if(!in_array($bookId,$rentedBooks))	{	//S'il n'est pas emprunté
				
				//Requête 1 : insertion dans la table loans
                $query = "INSERT INTO loans(user_id,book_id,return_date) "
                    ."VALUES('$userId','$bookId','$dateRetour')";
				
				//Envoyer la requête
				$result = mysqli_query($link, $query);				//var_dump($result);
				$rentedBooks = [];
				
				//Vérifier si elle a réussi
				if($result && mysqli_affected_rows($link)>0) {
					$rentedBooks[] = $bookId;	//var_dump($rentedBooks);
					
					$message = '<p class="success">Livre emprunté avec succès.</p>';
				} else {
					$message = '<p class="error">Problème survenu lors de l\'emprunt!';
				}
				//Déconnexion DB
				mysqli_close($link);
			} else {
				$message = "Livre déjà emprunté!";
			}		
		} else {
			$message = "Erreur lors de la connexion au serveur.";
		}
	} else {
		$message = "Référence du livre manquante.";
	}
}

//Récupérer le titre des livres empruntés
//Connexion DB
$link = mysqli_connect(HOSTNAME,USERNAME,PASSWORD,DATABASE);

if($link) {
	mysqli_set_charset($link, 'utf8');
	//Préparer la requête
	$query = "SELECT books.title FROM books, loans WHERE books.ref = loans.book_id";

	//Envoyer la requête
	$result = mysqli_query($link, $query);

	//Vérifier si elle a réussi
	if($result) {
		//Traitement des commandes
		$rentedTitles = mysqli_fetch_all($result, MYSQLI_ASSOC);	
		
		while(($rentedTitle = mysqli_fetch_assoc($result)) !== null) {
			$rentedTitles[] = $rentedTitle['title'];			
		}
		//Afficher les 5 derniers livres empruntés uniquement
		if(sizeof($rentedTitles)>5) {
			array_shift($rentedTitles);
		}	
		//Libérer la mémoire
		mysqli_free_result($result);
	} else {
		$message = "Erreur de requête.";
	}
	//Déconnexion DB
	mysqli_close($link);
} else {
	$message = "Erreur de connexion.";
}

//S1.	Gestion des styles dynamiques
$filename = "presets.json";
$styles = [];

if(file_exists($filename)) {	
	$contents = file_get_contents($filename);
	$json = json_decode($contents, true);
	$styles = $json['homestyles'];
?>
<!doctype html>
<html lang="fr">
<head>
	<meta charset="utf-8">
	<link rel="stylesheet" href="./css/style.css">
	<title>DB Access</title>
<style>
<?php
	foreach($styles as $selector => $rules) {
		echo "$selector {\n";
		
		foreach($rules as $rule) {
			echo "\t$rule;\n";
		}
		
		echo "}\n";
	}
}
?>
table { 
	margin: 15px 20px;
	border: 1px solid black;
	/* border-collapse: collapse; */
}

td, th {
	border: 1px solid silver; 
}

thead tr {
	background-color: silver;
}

tfoot tr {
	background-color: lightblue;
}

tr:nth-child(2n) {
	background-color: silver;
}

tfoot {
	text-align: center;
}
</style>
</head>
<body>
<?php if(empty($_SESSION['login'])) : ?>
	<p><a href="signin.php">Se connecter</a></p>
<?php else : ?>
	<p>Bievenue sur notre BiblioWeb !</p>
<?php endif; ?>

<p><?= $message ?></p>
<ul>
	<li><a href="<?= $_SERVER['PHP_SELF']; ?>">Tous</a></li>
	<li><a href="?title=Ubik">Ubik</a></li>
	<li><a href="?title=Germinal">Germinal</a></li>
</ul>

<form action="<?= $_SERVER['PHP_SELF'] ?>" method="post">
	<div>
		<label>Titre</label>
		<input type="text" name="title">
		<button>Rechercher</button>
	</div>
</form>

<!--DERNIER LIVRE CONSULTE-->
<?php include 'lastseen.php'; ?>

<!--DERNIERS LIVRES EMPRUNTE-->
<h3><u>5 derniers livres empruntés :</u></h3>
<?php if(!empty($rentedTitles)) : ?>
<ol>
	<?php foreach($rentedTitles as $rentedTitle) : ?>
		<?php foreach($rentedTitle as $livre) : ?>		
			<li><strong>Titre:</strong> "<em><?= $livre ?></em>".</li>
		<?php endforeach; ?>
	<?php endforeach; ?>
</ol>
<?php else : ?>
		<p>Aucun livre emprunté.</p>
<?php endif; ?>
<hr/>

<table>
	<caption>Liste des livres</caption>
	<thead>
		<tr>
		<?php foreach($fields as $field) : ?>
			<th><?= ucfirst($field->name); ?></th>
		<?php endforeach; ?>
			<th>Actions</th>
		</tr>
	</thead>
	<tbody>
	<?php foreach($books as $book) : ?>
		<tr>
			<td><?= $book['ref'] ?></td>
			<td><a class="book-title" href="show.php?ref=<?= $book['ref'] ?>"><?= $book['title'] ?></a></td>
			<td class="author"><?= $book['author_id'] ?></td>
			<td><?= substr($book['description'],0,20)."..." ?></td>
			<td><?php if(!empty($book['cover_url'])) : ?>
				<img src="<?= IMG_FOLDER.$book['cover_url'] ?>" alt="<?= $book['title'] ?>" height="80">
				<?php endif; ?>
			</td>
			<td>
			<!--SUPPRIMER LIVRE-->
				<form action="delete.php" method="post">
					<input type="hidden" name="method" value="DELETE">
					<input type="hidden" name="ref" value="<?= $book['ref'] ?>">
					<button class="ico-delete">&#9986;</button>
				</form>
				
			<!--EDITER LIVRE-->
				<button><a href="edit.php?ref=<?=$book['ref']?>"><span class="ico-edit">✎</span></a></button>
				
			<!--EMPRUNTER LIVRE-->
			<?php if(!in_array($book['ref'],$rentedBooks)) : ?>
			<form action="<?= $_SERVER['PHP_SELF']?>" method="post">
				<input type="hidden" name="title" value="<?= $book['title'] ?>"/>
				<input type="hidden" name="ref" value="<?= $book['ref'] ?>"/>
				<button name="btBorrow">Emprunter</button>
			<?php else : ?>
				<p><button name="loaned">Déjà emprunté!</button></p>
			<?php endif; ?>
			</form>
			</td>
		</tr>
	<?php endforeach; ?>	
	</tbody>
	<tfoot>
		<tr><td colspan="5">&copy; EPFC &dot; 2021</td></tr>
	</tfoot>
</table>
</body>
</html>