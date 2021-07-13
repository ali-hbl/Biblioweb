<?php
require ('config.php');

$message = "";

//Emprunter un livre
if(isset($_POST['btBorrow'])) {		//Validation 0
	if(isset($_POST['ref'])) {		//Validation 1
		$bookId = $_POST['ref'];
		$userId = $_SESSION['id'];
		$dateRetour = date('Y-m-d',mktime(23,59,59,date('m'),date('j')+14));	//Dans 2 semaines
		
		//Connexion DB
		$link = mysqli_connect(HOSTNAME,USERNAME,PASSWORD,DATABASE);
		
		if($link) {
			//Nettoyer les données entrantes
			$bookId = mysqli_real_escape_string($link, $_POST['ref']);
			
			//Vérifier si le livre n'est pas déjà emprunté
			if(!in_array($bookId,$rentedBooks))	{	//S'il n'est pas emprunté
				
				//Préparer la requête : insertion dans la table loans
				$query = "INSERT INTO loans (user_id, book_id, return_date)
				VALUES('$userId', '$bookId', '$dateRetour')";
				
				//Envoyer la requête
				$result = mysqli_query($link, $query);
				
				//Vérifier si elle a réussi
				if($result && mysqli_affected_rows>0) {
					$rentedBooks[] = $bookId;
					
					$message = "Livre emprunté avec succès.";
				} else {
					$message = "Problème survenu lors de l'emprunt!";
				}
			} else {
				$message = "Livre déjà emprunté!";
			}
		} else {
			$message = "Erreur lors de la connexion au serveur.";
		}
		
	}
}
?>