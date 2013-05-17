<?php

require_once '../fonctions.php'; // fonctions génériques (calcul de distance, etc)
require_once '../clés-api.php'; // clés pour authentification sur les API
require_once 'GLfonctions.php'; // fonctions spécifiques aux données du GL

// récupération des données du formulaire

$latitude = $_REQUEST['formLatitude'];
$longitude = $_REQUEST['formLongitude'];
$timestamp = $_REQUEST['formTimestamp'];
$altitude = $_REQUEST['formAltitude'];
$accuracy = $_REQUEST['formAccuracy'];
$altitudeAccuracy = $_REQUEST['formAltitudeAccuracy'];
$heading = $_REQUEST['formHeading'];
$speed = $_REQUEST['formSpeed'];


/* récupération des données du GL
Idéalement devrait utiliser https://github.com/geonef/php5-gdal/ (ne fonctionne pas) ou swig de la lib gdal pour créer un module php (erreur de compilation), mais faute de mieux on passe par un appel à un outil externe.
*/
$tmpfile = uniqid(sys_get_temp_dir()."/SILO-VERRE_");
`ogr2ogr -f GeoJSON $tmpfile WFS:http://ogc.data.grandlyon.com/gdlyon?SERVICE=WFS gic_collecte.gicsiloverre`;
$Donnees = json_decode(file_get_contents($tmpfile), true);
unlink($tmpfile);


?>

<!DOCTYPE html>
<html>
	<head>
		<title>Trouve-moi un silo verre</title>
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

// tri des silos verre selon la plus petite distance par rapport à la position de l'utilisateur
usort($Donnees["features"], "GLCompareDistanceSiloVerre");

// liste les 3 résultats les plus proches et les affiche avec un marqueur

$NbResultats=0;
$i=0;
while($NbResultats<3) {
	echo "document.getElementById('resultat$NbResultats').innerHTML = '". GLAfficheSiloVerre($Donnees["features"][$i]). "';\n";
	echo "var marker = new google.maps.Marker({
		position: new google.maps.LatLng(" .$Donnees["features"][$i]["geometry"]["coordinates"][0]. "," .$Donnees["features"][$i]["geometry"]["coordinates"][1]. "),
		map: map,
		title:'" .$Donnees["features"][$i]["properties"]["voie"]. "'
		});\n";
	$NbResultats++;
	$i++;
}


?>

			} // fin initialize()
			google.maps.event.addDomListener(window, 'load', initialize);
		</script>
</head>
<body>
<p>Je propose ces 3 résultats les plus proches :
<ul>
	<li id=resultat0>
	<li id=resultat1>
	<li id=resultat2>
</ul>
	<div id="map-canvas"></div>
<br>

</body>
</html>
