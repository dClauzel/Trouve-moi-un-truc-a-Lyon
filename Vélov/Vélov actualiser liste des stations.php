<?php 
require_once '../config.php';

$BD_table = 'velovStations';
	
// récupération des données de JCDecaux
$DonneesVelov = json_decode(file_get_contents("https://api.jcdecaux.com/vls/v1/stations?contract=Lyon&apiKey=$JCDecaux"), true);

if( $DonneesVelov === FALSE )
	die('Impossible de récupérer la liste des stations Vélov depuis JCDecaux.');
else
	$dbconn = pg_connect("host=$BD_host dbname=$BD_base user=$BD_user password=$BD_passwd")
		or die('Impossible de se connecter à la base : ' . pg_last_error());

echo "<p>OK, j'ai les données\n";

// suppression des anciennes données
$query = "DROP TABLE IF EXISTS $BD_table";
pg_query($dbconn, $query);

$query = "DROP INDEX IF EXISTS ".$BD_table."_index;";
pg_query($dbconn, $query);

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

	CONSTRAINT enforce_dims_geom CHECK (ndims(geom) = 2),
	CONSTRAINT enforce_geotype_geom CHECK (geometrytype(geom) = 'POINT'::text OR geom IS NULL),
	CONSTRAINT enforce_srid_geom CHECK (srid(geom) = 3857)
);";
pg_query($dbconn, $query);

$query = "create index ".$BD_table."_index on $BD_table using gist (geom);";
pg_query($dbconn, $query);


echo "<p>insertion en cours…";
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
		VALUES ('$number', '$name', '$address', '$banking', '$bonus', '$bike_stands', TO_TIMESTAMP($last_update / 1000), ST_Transform(ST_GeomFromText('POINT($longitude $latitude)',4326),3857))")
			or die("Erreur durant l'insertion de la station dans la base : ".pg_last_error());
}

echo " fini !\n";
?>
