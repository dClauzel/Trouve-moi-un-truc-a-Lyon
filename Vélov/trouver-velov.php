<?php

require_once '../fonctions.php'; // fonctions génériques (calcul de distance, etc)
require_once '../clés-api.php'; // clés pour authentification sur les API
require_once 'JCDecauxFonctions.php'; // fonctions spécifiques aux données de JCDecaux

// récupération des données du formulaire

$latitude = $_REQUEST['formLatitude'];
$longitude = $_REQUEST['formLongitude'];
$timestamp = $_REQUEST['formTimestamp'];
$altitude = $_REQUEST['formAltitude'];
$accuracy = $_REQUEST['formAccuracy'];
$altitudeAccuracy = $_REQUEST['formAltitudeAccuracy'];
$heading = $_REQUEST['formHeading'];
$speed = $_REQUEST['formSpeed'];


// récupération des données de JCDecaux
$fData = fopen("https://api.jcdecaux.com/vls/v1/stations?contract=Lyon&apiKey=$JCDecaux", "r");

while (!feof($fData))
	$data .= fgets($fData);

$DonneesVelov = json_decode($data, true);

?>

<!DOCTYPE html>
<html>
	<head>
		<title>Trouve-moi un Vélo'v</title>
		<meta name="viewport" content="initial-scale=1.0, user-scalable=no">
		<style type="text/css">
			html { height: 100% }
			body { height: 100% }
			#map-canvas {
				width: 50%;
				height: 50%;
				display: block;
				margin-left: auto;
				margin-right: auto 
			}
		</style>
		<script type="text/javascript" src="https://maps.googleapis.com/maps/api/js?key=<?php echo $Google; ?>&sensor=true"></script>
		<script type="text/javascript">
			function initialize() {

				// init de la carte centrée sur ma position
				var mapOptions = {
					center: new google.maps.LatLng(<?php echo "$latitude, $longitude"; ?>),
					zoom: 15,
					mapTypeId: google.maps.MapTypeId.HYBRID
				}

				var map = new google.maps.Map(document.getElementById("map-canvas"), mapOptions);

				// Marqueur bleu pour dire où on est
				blueIcon = "http://www.google.com/intl/en_us/mapfiles/ms/micons/blue-dot.png";

				var marker = new google.maps.Marker({
					position: new google.maps.LatLng(<?php echo "$latitude, $longitude"; ?>),
					map: map,
					icon: blueIcon,
					title: "Je suis ici"
				});


<?php


// tri des stations selon la plus petite distance par rapport à la position de l'utilisateur
usort($DonneesVelov, "JCDecauxCompareStationDistance");

// liste les 3 stations intéressantes les plus proches et les affiche avec un marqueur
$NbStationsOK=0;
$i=0;
while($NbStationsOK<3) {
	if(JCDecauxStationValide($DonneesVelov[$i]) == true) {
		echo "document.getElementById('station$NbStationsOK').innerHTML = '". JCDecauxStationAffiche($DonneesVelov[$i]). "';\n";
		echo "var marker = new google.maps.Marker({
			position: new google.maps.LatLng(" .$DonneesVelov[$i]['position']['lat']. "," .$DonneesVelov[$i]['position']['lng']. "),
			map: map,
			title:'" .$DonneesVelov[$i]['name']. " : ".$DonneesVelov[$i]['available_bikes']. " vélo(s) libre(s)'
			});\n";
		$NbStationsOK++;
	}
	$i++;
}

?>

			} // fin initialize()
			google.maps.event.addDomListener(window, 'load', initialize);
		</script>
</head>
<body>
<p>Je propose ces 3 stations les plus proches, ayant au moins 1 vélo de libre :
<ul>
	<li id=station0>
	<li id=station1>
	<li id=station2>
</ul>
	<div id="map-canvas"></div>
<br>
</body>
</html>
