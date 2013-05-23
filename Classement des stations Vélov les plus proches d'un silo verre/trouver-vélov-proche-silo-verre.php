<?php

require_once '../fonctions.php'; // fonctions génériques (calcul de distance, etc)
require_once '../clés-api.php'; // clés pour authentification sur les API
require_once '../GLfonctions.php'; // fonctions spécifiques aux données du GL
require_once '../Vélov/JCDecauxFonctions.php'; // fonctions spécifiques aux données du GL

/* récupération des données du GL
Idéalement devrait utiliser https://github.com/geonef/php5-gdal/ (ne fonctionne pas) ou swig de la lib gdal pour créer un module php (erreur de compilation), mais faute de mieux on passe par un appel à un outil externe.
*/
$tmpfile = uniqid(sys_get_temp_dir()."/SILO-VERRE_");
`ogr2ogr -f GeoJSON $tmpfile WFS:http://ogc.data.grandlyon.com/gdlyon?SERVICE=WFS gic_collecte.gicsiloverre`;
$DonneesSiloVerre = json_decode(file_get_contents($tmpfile), true);
unlink($tmpfile);

// récupération des données de JCDecaux
$DonneesVelov = json_decode(file_get_contents("https://api.jcdecaux.com/vls/v1/stations?contract=Lyon&apiKey=$JCDecaux"), true);

// init (crade) des variables qui contiendront notre résultat
$PlusPetiteDistante=66666;
$PlusPetiteStationVelov;
$PlusPetitSiloVerre;

/* fixme
complexité O(n.m) trop grande, le script php timetout
du coup, changer les config :
	dans php.ini : max_execution_time = 600
	dans apache :
		Timeout 300
		FcgidProcessLifeTime 7200
		FcgidIOTimeout  7200
		FcgidConnectTimeout 600
		FcgidIdleTimeout 600
*/

// pour chaque station Vélov
foreach ($DonneesVelov as $station) {

	// pour chaque silo verre
	foreach ($DonneesSiloVerre["features"] as $silo) {
		$DistanceTmp = distance($station['position']['lat'], $station['position']['lng'],$silo["geometry"]["coordinates"][0],$silo["geometry"]["coordinates"][1]);
		
		// on conserve le couple station-silo qui a la plus petite distante
		if( bccomp($DistanceTmp,$PlusPetiteDistante) == -1) {
			$PlusPetiteDistante = $DistanceTmp;
			$PlusPetiteStationVelov = $station;
			$PlusPetitSiloVerre = $silo;
		}
	}

}

// insertion du fragment HTML pour former le haut de la page de résultat
require_once '../Patrons html/haut.php';

	echo "document.getElementById('resultat0').innerHTML = '". JCDecauxStationAffiche($PlusPetiteStationVelov). "';\n";
	echo "var marker = new google.maps.Marker({
			position: new google.maps.LatLng(" .$PlusPetiteStationVelov['position']['lat']. "," .$PlusPetiteStationVelov['position']['lng']. "),
			map: map,
			title: '" .addslashes($PlusPetiteStationVelov['name']). "'
		});\n";

	echo "document.getElementById('resultat1').innerHTML = '". GL_SiloVerre_Affiche($PlusPetitSiloVerre). "';\n";
	echo "var marker = new google.maps.Marker({
		position: new google.maps.LatLng(" .$PlusPetitSiloVerre["geometry"]["coordinates"][0]. "," .$PlusPetitSiloVerre["geometry"]["coordinates"][1]. "),
		map: map,
		title: '" .addslashes($PlusPetitSiloVerre["properties"]["voie"]). "'
		});\n";

	echo "document.getElementById('resultat2').innerHTML = 'Distance entre la station Vélov et le silo verre : $PlusPetiteDistante';\n";

// insertion du fragment HTML pour former le bas de la page de résultat
require_once '../Patrons html/bas.php';

?>
