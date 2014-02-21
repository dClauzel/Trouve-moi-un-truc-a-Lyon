<!doctype html>
<html>
<head>
	<title>Mise à jour de la base</title>
	<meta charset='utf-8'>
	<link rel=stylesheet href='../Ressources/style général.css'>
</head>
<body>

<?php 
require_once '../config.php';
require_once '../Ressources/fonctionsGénériques.php';

$BD_table = 'velovStations';
	
// récupération des données de JCDecaux
$DonneesVelov = json_decode(file_get_contents("https://api.jcdecaux.com/vls/v1/stations?contract=Lyon&apiKey=$JCDecaux"), true);

if( $DonneesVelov === FALSE )
	die('Impossible de récupérer la liste des stations Vélov depuis JCDecaux.');
else
	$dbconn = pg_connect("host=$BD_host dbname=$BD_base user=$BD_user password=$BD_passwd")
		or die('Impossible de se connecter à la base : ' . pg_last_error());

echo "<p>OK, j'ai les données\n";
ob_flush(); flush();

// suppression des anciennes données
$query = "DROP TABLE IF EXISTS $BD_table";
pg_query($dbconn, $query)
		or die('Impossible de droper la base : ' . pg_last_error());

$query = "DROP INDEX IF EXISTS ".$BD_table."_index;";
pg_query($dbconn, $query)
		or die('Impossible de droper l\'index : ' . pg_last_error());

// création de la structure
$query = "CREATE TABLE $BD_table (
	number integer NOT NULL,
	name character varying(128),
	address character varying(128),
	banking boolean,
	bonus boolean,
	bike_stands integer,
	last_update timestamp,
	geom geometry,

	CONSTRAINT ".$BD_table."_pkey PRIMARY KEY (number),
	CONSTRAINT enforce_dims_geom CHECK (ST_ndims(geom) = 2),
	CONSTRAINT enforce_geotype_geom CHECK (geometrytype(geom) = 'POINT'::text OR geom IS NULL),
	CONSTRAINT enforce_srid_geom CHECK (ST_srid(geom) = 3857)
);

CREATE INDEX ".$BD_table."_index ON $BD_table USING gist (geom);
";
pg_query($dbconn, $query)
		or die('Impossible de créer la table : ' . pg_last_error());

echo "<p>insertion en cours…";
ob_flush(); flush();

// insertion
foreach($DonneesVelov as $s) {

	//	nettoyage des données de JCDecaux
	$number		= $s['number'];
	$name			= trim(explode('-', pg_escape_string($s['name']), 2)[1]);	// le nom de la station est la 2e partie de la chaîne; on vire les espaces superflus
	$address	= trim(pg_escape_string($s['address']));
	if ($s['banking'] == "true")
		$banking = "true";
	else
		$banking = "false";
	if ($s['bonus'] == "true")
		$bonus = "true";
	else
		$bonus = "false";
	$bike_stands = $s['bike_stands'];
	$last_update	= $s['last_update'];
	$latitude	= $s['position']['lat'];
	$longitude	= $s['position']['lng'];

	pg_query($dbconn, "INSERT INTO $BD_table
		(number, name, address, banking, bonus, bike_stands, last_update, geom)
	VALUES ('$number', '$name', '$address', '$banking', '$bonus', '$bike_stands', TO_TIMESTAMP($last_update / 1000), ST_Transform(ST_SetSRID(ST_MakePoint($longitude , $latitude), 4326), 3857))")
			or die("Erreur durant l'insertion de la station dans la base : ".pg_last_error());
}

echo " fini !\n";
?>

<h1>Technique</h1>
<p>La base a été mise à jour avec la requête suivante :
<pre><code><?php echo securise($query); ?></code></pre>
</body>
</html>
