<?php
define('HOSTNAME','localhost');
define('USERNAME','root');
define('PASSWORD','root');
define('DATABASE','biblioweb');

$books = [];
$title = "";

if(!empty($_GET['title'])) {
	$title = $_GET['title'];
} elseif(!empty($_POST['title'])) {
	$title = $_POST['title'];
}


$link = mysqli_connect(HOSTNAME,USERNAME,PASSWORD,DATABASE);
//var_dump($link);
mysqli_query($link, "SET NAMES utf8");

if(!empty($title)) {
	$title = mysqli_real_escape_string($link, $title);
	$query = "SELECT * FROM books WHERE title='$title'";
} else {
	$query = "SELECT * FROM books";
}

$result = mysqli_query($link, $query);
//var_dump($result);

if($result) {
	while(($book = mysqli_fetch_assoc($result))) {
		$books[] = $book;
	}
	//var_dump($books);
	
	$fields = mysqli_fetch_fields($result);
	//var_dump($fields);
	
	mysqli_free_result($result);
}

mysqli_close($link);
?>
<!doctype html>
<html lang="fr">
<head>
<title>DB Access</title>
<meta charset="utf-8">
<style>
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
<ul>
	<li><a href="<?= $_SERVER['PHP_SELF']; ?>">Tous</a></li>
	<li><a href="?title=Ubik">Ubik</a></li>
	<li><a href="?title=Germinal">Germinal</a></li>
</ul>

<form action="<?= $_SERVER['PHP_SELF'] ?>" method="post">
	<div>
		<label>Titre</label>
		<input type="text" name="title">
	</div>
	<button>Rechercher</button>
</form>


<table>
	<caption>Liste des livres</caption>
	<thead>
		<tr>
		<?php foreach($fields as $field) : ?>
			<th><?= ucfirst($field->name); ?></th>
		<?php endforeach; ?>	
		</tr>
	</thead>
	<tbody>
	<?php foreach($books as $book) : ?>
		<tr>
		<?php foreach($book as $data) : ?>
			<td><?= $data ?></td>
		<?php endforeach; ?>
		</tr>
	<?php endforeach; ?>	
	</tbody>
	<tfoot>
		<tr><td colspan="5">&copy; EPFC &dot; 2021</td></tr>
	</tfoot>
</table>

</body>
</html>





