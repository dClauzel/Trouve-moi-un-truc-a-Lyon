<?php

/* Calcule la distance à vol d'oiseau entre 2 positions géographiques
 * latitude1,longitude1,latitude2,longitude2 : coordonnées décimales
 * retourne : distance
 */
function distance($latitude1, $longitude1, $latitude2, $longitude2) {
	return sqrt( pow($latitude2-$latitude1,2) + pow($longitude2-$longitude1,2) );
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
