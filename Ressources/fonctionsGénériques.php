<?php

bcscale(32); // définition de la précision pour ces 'loperies de floats…

/* Blinde la chaîne de caractère, pour affichage html
 * texte : chaîne
 * retourne : chaîne
 */
function securise($texte) {
	return htmlspecialchars($texte, ENT_QUOTES|ENT_HTML5);
}

// récupération des données du formulaire pour en faire des variables globales

$latitude = $_REQUEST['formLatitude'];
$longitude = $_REQUEST['formLongitude'];
$timestamp = $_REQUEST['formTimestamp'];
$altitude = $_REQUEST['formAltitude'];
$accuracy = $_REQUEST['formAccuracy'];
$altitudeAccuracy = $_REQUEST['formAltitudeAccuracy'];
$heading = $_REQUEST['formHeading'];
$speed = $_REQUEST['formSpeed'];

?>
