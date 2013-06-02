<?php
require_once '../config.php';
require_once '../Ressources/fonctionsGénériques.php';
?>
<!doctype html>
<html>
<head>
	<title>Traitement sur les Vélo'v</title>
	<meta charset='utf-8'>
	<meta name='viewport' content='initial-scale=1.0, user-scalable=no'>
	<script type='text/javascript' src='//maps.googleapis.com/maps/api/js?key=<?php echo $Google; ?>&sensor=true'></script>
	<link rel=stylesheet href='../Ressources/style général.css'>
</head>
<body>

<?php 


// Configure
$BD_table = 'velovEtatReseau';

$dbconn = pg_connect("host=$BD_host dbname=$BD_base user=$BD_user password=$BD_passwd")
	or die('Impossible de se connecter à la base : ' . pg_last_error());

$query = "SELECT
	number,
	name,
	address,
	banking,
	bonus,
	status,
	bike_stands,
	available_bike_stands,
	available_bikes,
	last_update,
	ST_Y(ST_Transform(geom,4326)),
	ST_X(ST_Transform(geom,4326)),
	ST_Distance( ST_Transform(ST_GeomFromText('POINT($longitude $latitude)',4326),3857), ST_GeomFromEWKB(geom) )
FROM $BD_table

WHERE status = 'OPEN' AND available_bikes > 0

	ORDER BY ST_Distance
	LIMIT 3 ;
";

$resultat = pg_query($dbconn, $query);

?>

<table>
<caption><?php echo "Les ".pg_num_rows($resultat)." stations les plus proches avec au moins 1 vélo disponible"; ?></caption>
<tr>
	<th>numéro</th>
	<th>nom</th>
	<th>adresse</th>
	<th>latitude, longitude</th>
	<th>distance</th>
	<th>carte bleue</th>
	<th>bonus temps</th>
	<th>nombre de bornes totales</th>
	<th>nombre de bornes libres</th>
	<th>nombre de vélos disponibles</th>
	<th>Date de dernière mise à jour</th>
</tr>

<?php
while ($ligne = pg_fetch_array($resultat)) {
	echo "<tr>";
	echo "<td>" .$ligne['number']. "</td>";
	echo "<td>" .securise($ligne['name']). "</td>";
	echo "<td>" .securise($ligne['address']). "</td>";
	echo "<td>" .$ligne['st_y']. ", " .$ligne['st_x']. "</td>";
	echo "<td>" .$ligne['st_distance']. "</td>";
	if ($ligne['banking'] == "t")
		echo "<td>oui</td>";
	else
		echo "<td>non</td>";
	if ($ligne['bonus'] == "t")
		echo "<td>oui</td>";
	else
		echo "<td>non</td>";
	echo "<td>" .$ligne['bike_stands']. "</td>";
	echo "<td>" .$ligne['available_bike_stands']. "</td>";
	echo "<td>" .$ligne['available_bikes']. "</td>";
	echo "<td>" .$ligne['last_update']. "</td>";
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
	echo "var marker = new google.maps.Marker({
		position: new google.maps.LatLng(" .$ligne['st_y']. "," .$ligne['st_x']. "),
		map: map,
		title:'" .securise($ligne['name']). "'
		});\n";
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
