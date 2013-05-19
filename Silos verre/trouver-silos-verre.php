<?php

require_once '../fonctions.php'; // fonctions génériques (calcul de distance, etc)
require_once '../clés-api.php'; // clés pour authentification sur les API
require_once '../GLfonctions.php'; // fonctions spécifiques aux données du GL

/* récupération des données du GL
Idéalement devrait utiliser https://github.com/geonef/php5-gdal/ (ne fonctionne pas) ou swig de la lib gdal pour créer un module php (erreur de compilation), mais faute de mieux on passe par un appel à un outil externe.
*/
$tmpfile = uniqid(sys_get_temp_dir()."/SILO-VERRE_");
`ogr2ogr -f GeoJSON $tmpfile WFS:http://ogc.data.grandlyon.com/gdlyon?SERVICE=WFS gic_collecte.gicsiloverre`;
$Donnees = json_decode(file_get_contents($tmpfile), true);
unlink($tmpfile);

// insertion du fragment HTML pour former le haut de la page de résultat
require_once '../Patrons html/haut.php';

// tri des silos verre selon la plus petite distance par rapport à la position de l'utilisateur
usort($Donnees["features"], "GL_CompareDistance");

// liste les 3 résultats les plus proches et les affiche avec un marqueur

$NbResultats=0;
$i=0;
while($NbResultats<3) {
	echo "document.getElementById('resultat$NbResultats').innerHTML = '". GL_SiloVerre_Affiche($Donnees["features"][$i]). "';\n";
	echo "var marker = new google.maps.Marker({
		position: new google.maps.LatLng(" .$Donnees["features"][$i]["geometry"]["coordinates"][0]. "," .$Donnees["features"][$i]["geometry"]["coordinates"][1]. "),
		map: map,
		title: \"" .$Donnees["features"][$i]["properties"]["voie"]. "\"
		});\n";
	$NbResultats++;
	$i++;
}

// insertion du fragment HTML pour former le bas de la page de résultat
require_once '../Patrons html/bas.php';

?>
