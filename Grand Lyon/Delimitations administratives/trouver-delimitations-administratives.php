<?php
require_once '../../config.php';
require_once '../../Ressources/fonctionsGénériques.php';
?>
<!doctype html>
<html>
<head>
	<title>Trouver les délimitations administratives</title>
	<meta charset='utf-8'>
	<meta name='viewport' content='initial-scale=1.0, user-scalable=no'>
	<script type='text/javascript' src='//maps.googleapis.com/maps/api/js?key=<?php echo $Google; ?>&sensor=true'></script>
	<link rel=stylesheet href='../../Ressources/style général.css'>
</head>
<body>

<?php 


# Configure
$BD_table = 'GLlimitesAdministratives';

$dbconn = pg_connect("host=$BD_host dbname=$BD_base user=$BD_user password=$BD_passwd")
	or die('Impossible de se connecter à la base : ' . pg_last_error());

$query = "SELECT
	type,
	gml_id,
	genre,
	insee1,
	insee2,
	gid,
	ST_AsText(
		  geom
	)
FROM $BD_table

where genre = 'Département'

ORDER BY insee1 ;
";

$resultat = pg_query($dbconn, $query);

?>

<table>
<caption><?php echo "Les ".pg_num_rows($resultat)." délimitations administratives du Grand Lyon"; ?></caption>
<tr>
	<th>type</th>
	<th>gml_id</th>
	<th>genre</th>
	<th>insee1</th>
	<th>insee2</th>
	<th>gid</th>
	<th>geom</th>
</tr>

<?php
while ($ligne = pg_fetch_array($resultat)) {
	echo "<tr>";
	echo "<td>" .securise($ligne['type']). "</td>";
	echo "<td>" .securise($ligne['gml_id']). "</td>";
	echo "<td>" .securise($ligne['genre']). "</td>";
	echo "<td>" .securise($ligne['insee1']). "</td>";
	echo "<td>" .securise($ligne['insee2']). "</td>";
	echo "<td>" .securise($ligne['gid']). "</td>";
	//echo "<td>" .securise($ligne['st_astext']). "</td>";
	echo "</tr>\n";

}
?>

</table>

	<div id='map-canvas'></div>

<script type='text/javascript'>
function initialize() {

// init de la carte centrée sur ma position
var mapOptions = {
	center: new google.maps.LatLng(<?php echo "$latitude, $longitude"; ?>),
	zoom: 15,
	mapTypeId: google.maps.MapTypeId.HYBRID
}

var map = new google.maps.Map(document.getElementById('map-canvas'), mapOptions);
<?php

pg_result_seek($resultat, 0);
while ($ligne = pg_fetch_array($resultat)) {

	/* Extraction des points de la délimitation administrative :
	 * 1) blindage de la chaîne
	 * 2) suppression à droite de "))"
	 * 3) suppression à gauche de "POLYGON(("
	 * 4) explosion en tableau pour moulinage
	 */
	$polygonePoints = explode(
		"," ,
		ltrim(
			rtrim(
					securise($ligne['st_astext']),
					"))"
			),
			"POLYGON((")
	);

	$taillePolygone = count($polygonePoints);	
	$i = 1;

	echo "var polygonePoints = [\n";
	foreach($polygonePoints as $point) {
		$p = explode(" ", $point);
		echo "new google.maps.LatLng(" .$p[1]. "," .$p[0]. ")";
		if($i < $taillePolygone)
			echo ",\n";
		else
			echo "\n";
		$i++;
	}
	echo "];\n";

	echo "var delimitationAdministrative = new google.maps.Polygon({
		paths: polygonePoints,
		strokeColor: '#FF0000',
		strokeOpacity: 0.8,
		strokeWeight: 2,
		fillColor: '#FF0000',
		fillOpacity: 0.35
	});\n";

	echo "delimitationAdministrative.setMap(map);\n";
}


?>

// Marqueur bleu pour dire où on est
blueIcon = '//www.google.com/intl/en_us/mapfiles/ms/micons/blue-dot.png';

var marker = new google.maps.Marker({
	position: new google.maps.LatLng(<?php echo "$latitude, $longitude"; ?>),
	map: map,
	icon: blueIcon,
	title: 'Je suis ici'
});

} // fin initialize()
google.maps.event.addDomListener(window, 'load', initialize);
</script>

<hr>
<h1>Technique</h1>
<p>Les résultats ont été trouvés par la requête suivante :
<pre><code><?php echo securise($query); ?></code></pre>
<p><small><a href='http://dclauzel.github.io/Trouve-moi-un-truc-a-Lyon/'>Sources sur GitHub</a> — par <a href='http://Damien.Clauzel.eu'>Damien Clauzel</a> — <a href='https://Twitter.com/dClauzel'>@dClauzel</a> — sous licence GPLv3</small>
</body>
</html>
