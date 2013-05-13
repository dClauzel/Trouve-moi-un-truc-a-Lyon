<?php

/* Calcule la distance à vol d'oiseau entre 2 positions géographiques
 * latitude1,longitude1,latitude2,longitude2 : coordonnées décimales
 * retourne : distance
 */
function distance($latitude1, $longitude1, $latitude2, $longitude2) {
	return sqrt( pow($latitude2-$latitude1,2) + pow($longitude2-$longitude1,2) );
}

/* fonction de comparaison de 2 stations, pour usort()
 * la comparaison se fait par rapport à la distance de l'utilisateur
 */
function CompareStationDistance($station1, $station2) {

	global $latitude, $longitude;
	$distance1 = distance($latitude, $longitude, $station1["position"]["lat"], $station1["position"]["lng"]);
	$distance2 = distance($latitude, $longitude, $station2["position"]["lat"], $station2["position"]["lng"]);

	if ($distance1 == $distance2)
		return 0;

	return ($distance1 < $distance2) ? -1 : 1;
}

// affiche en liste les données sur une station
function StationAffiche($station) {
	global $latitude, $longitude;
	$res = "<ul>";
	$res .= "<li>number : " . $station["number"];
	$res .= "<li>name : " . $station["name"];
	$res .= "<li>address : " . $station["address"];
	$res .= "<li>latitude : " . $station["position"]["lat"];
	$res .= "<li>longitude : " . $station["position"]["lng"];
	$res .= "<li>banking : " . $station["banking"];
	$res .= "<li>bonus : " . $station["bonus"];
	$res .= "<li>status : " . $station["status"];
	$res .= "<li>bike_stands : " . $station["bike_stands"];
	$res .= "<li>available_bike_stands : " . $station["available_bike_stands"];
	$res .= "<li>available_bikes : " . $station["available_bikes"];
	$res .= "<li>last_update : " . $station["last_update"];
	$res .= "<ul>";
	$res .= "\t<li>distance : " . distance($latitude,$longitude,$station["position"]["lat"],$station["position"]["lng"]);
	$res .= "</ul>";
	$res .= "</ul>";

	return $res;
}

/* retour vrai si la station est intéressante
 * une station intéressante est 1) ouverte 2) avec au moins 1 vélo de libre
 */
function StationValide($station) {

	// on saute la station fermée
	if($station["status"] != "OPEN")
		return false;
	
	// on saute la station vide
	if($station["available_bikes"] <= 0)
		return false;

	return true;
}

?>