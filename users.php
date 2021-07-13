<?php
session_start();
include ('includes/header.php');

require('config.php');	//Incorporation des données

//Déclaration des variables
$message = "";
$suers = [];

//Connexion DB
$link = mysqli_connect(HOSTNAME, USERNAME, PASSWORD, DATABASE);

if($link) {
	mysqli_set_charset($link, 'utf8');
	
	//Traiter l'envoi du formulaire
	if(isset($_POST['btChangeStatut']) && isset($_POST['userId']) && isset($_POST['statut'])) {
		$userId = $_POST["userId"];
		
		//Vérifications si nb de modif max atteint pour userId
		if(isset($_SESSION['StatusChanges'][$userId]) && $_SESSION['StatusChanges'][$userId]>=2) {
			$message = "Vous avez déjà modifié l'état de cet utilisateur plus de 2 fois!";
		} else {
			if(isset($_SESSION['StatusChanges'][$userId])) {
				$_SESSION['StatusChanges'][$userId] += 1;
			} else {
				$_SESSION['StatusChanges'][$userId] = 1;
			}
			
			$oldStatut = $_POST['statut'];
			$action = $_POST['btChangeStatut'];
			
			//Traitement des commandes
			$roles = ['novice', 'habitué', 'expert'];
			$changes = true;
			
			if($action == 'promo') {
				$oldIndex = array_search($oldStatut, $roles);
				
				if($oldIndex == -1 || $oldIndex == sizeof($roles)) {
					//On ne peut pas incrémenter au dela de la fin du tableau
					//et on traite aussi le cas où array_search retourne -1
					$changes = false;
				} else {
					$newStatus = $roles[$oldIndex + 1];
				}
			} elseif ($action == 'retro') {
				$oldIndex = array_search($oldStatut, $roles);
				
				if($oldIndex <= 0) {
					//On ne peut pas décrémenter 0
					//et on traite aussi le cas où array_Search retourne -1
					$changes = false;
				} else {
					$newStatus = $roles[$oldIndex - 1];
				}
			} else {
				$changes = false;
			}
			
			if($changes) {
				//Nettoyer les données
				$userId = mysqli_real_escape_string($link, $userId);
				
				//Préparer la requête
				$query = "UPDATE users SET statut='$newStatus' WHERE id=$userId";
				
				//Envoyer la requête
				$result = mysqli_query($link, $query);
				
				//Vérifier si elle a réussi
				if($result && mysqli_affected_rows($link)>0) {
					//Traitement des commandes
					if($action == 'promo') {
						$message = "L'utilisateur a bien été promu!";
					} else {
						$message = "L'utilisateur a bien été rétrogradé.";
					}
				} else {
					$message = "Erreur de requête.";
				}
			} else {
				$message = "Ce changement de statut n'est pas possible.";
			}
		}
	} else {
		$message = "Erreur lors de l'envoi du formulaire!";
	}
	
	//RÉCUPÉRER LES UTILISATEURS
	
	//Préparer la requête
	$query = "SELECT id, login, statut FROM users";
	
	//Envoyer la requête
	$result = mysqli_query($link, $query);
	
	//Vérifier si elle a réussi
	if($result) {
		//Traitement des commandes
		$users = mysqli_fetch_all($result, MYSQLI_ASSOC);
		
		//Libérer le jeu de résultats
		mysqli_free_result($result);
	}
	//Déconnexion DB
	mysqli_close($link);	
} else {
	$message = "Erreur de connexion.";
}
?>

<table>
<?php foreach($users as $user) : ?>
	<?php if($user['statut']=='admin') : ?>
	<tr>
		<td style="padding:5px 20px;"><?= $user['login'] ?></td>
		<td style="padding:5px 20px;"><?= $user['statut'] ?></td>
		
		<form action="<?= $_SERVER['PHP_SELF'] ?>" method="POST">
			<input type="hidden" name="userId" value="<?= $user['id'] ?>"/>
			<input type="hidden" name="statut" value="<?= $user['statut'] ?>"/>
			
			<td style="padding:5px 20px;">
			<?php if($user['statut']!='expert') : ?>
				<button name="btChangeStatut" value="promo">Promouvoir</buton>
			<?php endif; ?></td>
			
			<td style="padding:5px 20px;">
			<?php if($user['statut']!='novice') : ?>
				<button name="btChangeStatut" value="retro">Rétrograder</buton>
			<?php endif; ?></td>
		</form>
	</tr>
	<?php endif; ?>
<?php endforeach; ?>
</table>
<hr/>
<p><?= $message ?></p>
<hr/>

<?php include ('includes/footer.php'); ?>