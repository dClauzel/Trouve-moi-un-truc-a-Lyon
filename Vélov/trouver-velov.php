<?php

require_once '../fonctions.php'; // fonctions génériques (calcul de distance, etc)
require_once '../clés-api.php'; // clés pour authentification sur les API
require_once 'JCDecauxFonctions.php'; // fonctions spécifiques aux données de JCDecaux

// récupération des données de JCDecaux
$DonneesVelov = json_decode(file_get_contents("https://api.jcdecaux.com/vls/v1/stations?contract=Lyon&apiKey=$JCDecaux"), true);

// insertion du fragment HTML pour former le haut de la page de résultat
require_once '../Patrons html/haut.php';

// tri des stations selon la plus petite distance par rapport à la position de l'utilisateur
usort($DonneesVelov, "JCDecauxCompareStationDistance");

// liste les 3 stations intéressantes les plus proches et les affiche avec un marqueur
$NbResultats=0;
$i=0;
while($NbResultats<3) {
	if(JCDecauxStationValide($DonneesVelov[$i]) == true) {
		echo "document.getElementById('resultat$NbResultats').innerHTML = '". JCDecauxStationAffiche($DonneesVelov[$i]). "';\n";
		echo "var marker = new google.maps.Marker({
			position: new google.maps.LatLng(" .$DonneesVelov[$i]['position']['lat']. "," .$DonneesVelov[$i]['position']['lng']. "),
			map: map,
			title:'" .$DonneesVelov[$i]['name']. " : ".$DonneesVelov[$i]['available_bikes']. " vélo(s) libre(s)'
			});\n";
		$NbResultats++;
	}
	$i++;
}

// insertion du fragment HTML pour former le bas de la page de résultat
require_once '../Patrons html/bas.php';

?>
