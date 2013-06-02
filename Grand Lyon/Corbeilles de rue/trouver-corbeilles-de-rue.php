<?php
require_once '../../config.php';
require_once '../../Ressources/fonctionsGénériques.php';
?>
<!doctype html>
<html>
<head>
	<title>Trouver les corbeilles de rue</title>
	<meta charset='utf-8'>
	<meta name='viewport' content='initial-scale=1.0, user-scalable=no'>
	<script type='text/javascript' src='//maps.googleapis.com/maps/api/js?key=<?php echo $Google; ?>&sensor=true'></script>
	<link rel=stylesheet href='../../Ressources/style général.css'>
</head>
<body>

<?php 


# Configure
$BD_table = 'GLcorbeillesRue';

$dbconn = pg_connect("host=$BD_host dbname=$BD_base user=$BD_user password=$BD_passwd")
	or die('Impossible de se connecter à la base : ' . pg_last_error());

$query = "SELECT
	type,
	gml_id,
	commune,
	voie,
	numerodansvoie,
	gestionnaire,
	observation,
	gid,
	miseajourattributs,
	miseajourgeometrie,
	ST_Y(ST_Transform(geom,4326)),
	ST_X(ST_Transform(geom,4326)),
	ST_Distance( ST_Transform(ST_GeomFromText('POINT($longitude $latitude)',4326),3857), ST_GeomFromEWKB(geom) )
FROM $BD_table

ORDER BY ST_Distance
LIMIT 3 ;
";

$resultat = pg_query($dbconn, $query);

?>

<table>
<caption><?php echo "Les ".pg_num_rows($resultat)." corbeilles de rue les plus proche"; ?></caption>
<tr>
	<th>type</th>
	<th>gml_id</th>
	<th>commune</th>
	<th>voie</th>
	<th>numéro dans voie</th>
	<th>latitude, longitude</th>
	<th>distance</th>
	<th>gestionnaire</th>
	<th>observation</th>
	<th>gid</th>
	<th>mise à jour des attributs</th>
	<th>mise à jour de la géométrie</th>
</tr>

<?php
while ($ligne = pg_fetch_array($resultat)) {
	echo "<tr>";
	echo "<td>" .securise($ligne['type']). "</td>";
	echo "<td>" .securise($ligne['gml_id']). "</td>";
	echo "<td>" .securise($ligne['commune']). "</td>";
	echo "<td>" .securise($ligne['voie']). "</td>";
	echo "<td>" .securise($ligne['numerodansvoie']). "</td>";
	echo "<td>" .securise($ligne['st_y']. ", " .$ligne['st_x']). "</td>";
	echo "<td>" .securise($ligne['st_distance']). "</td>";
	echo "<td>" .securise($ligne['gestionnaire']). "</td>";
	echo "<td>" .securise($ligne['observation']). "</td>";
	echo "<td>" .securise($ligne['gid']). "</td>";
	echo "<td>" .securise($ligne['miseajourattributs']). "</td>";
	echo "<td>" .securise($ligne['miseajourgeometrie']). "</td>";
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
		title:'" .securise($ligne['voie']). ", " .securise($ligne['numerodansvoie']). "'
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
