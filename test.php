<?php 
// $dateRetour = date('d/m/Y', mktime(00,00,00,date('m'), date('j')+14));
$today = date('Y/m/d');
$date = new DateTime("2021-05-25");
$date = $date->modify("+14 days");

echo $date;
?>
