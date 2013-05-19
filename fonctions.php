<?php

bcscale(32); // définition de la précision pour ces 'loperies de floats…

/* Calcule la distance à vol d'oiseau entre 2 positions géographiques
 * latitude1,longitude1,latitude2,longitude2 : coordonnées décimales
 * retourne : distance
 */
function distance($latitude1, $longitude1, $latitude2, $longitude2) {
	$earth_radius = 6378137;   // Terre = sphère de 6378km de rayon
	$rla1 = deg2rad($latitude1);
	$rlo1 = deg2rad($longitude1);
	$rla2 = deg2rad($latitude2);
	$rlo2 = deg2rad($longitude2);
	$dla = ($rla2 - $rla1) / 2;
	$dlo = ($rlo2 - $rlo1) / 2;
	$a = (sin($dla) * sin($dla)) + cos($rla1) * cos($rla2) * (sin($dlo) * sin($dlo));
	$d = 2 * atan2(sqrt($a), sqrt(1 - $a));
	return ($earth_radius * $d);
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
