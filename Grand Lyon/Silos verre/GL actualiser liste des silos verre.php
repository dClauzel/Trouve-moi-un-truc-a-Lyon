<?php 

require_once '../../config.php';

/* récupération des données du GL
Idéalement devrait utiliser https://github.com/geonef/php5-gdal/ (ne fonctionne pas) ou swig de la lib gdal pour créer un module php (erreur de compilation), mais faute de mieux on passe par un appel à un outil externe.
*/
$tmpfile = uniqid(sys_get_temp_dir()."/SILO-VERRE_");
`ogr2ogr -f GeoJSON $tmpfile WFS:http://ogc.data.grandlyon.com/gdlyon?SERVICE=WFS gic_collecte.gicsiloverre`;
$Donnees = json_decode(file_get_contents($tmpfile), true);
unlink($tmpfile);

# accès à la base

$BD_table = 'GLsilosVerre';
	

if( $Donnees === FALSE )
	die('Impossible de récupérer les données depuis le GL.');
else
	$dbconn = pg_connect("host=$BD_host dbname=$BD_base user=$BD_user password=$BD_passwd")
		or die('Impossible de se connecter à la base : ' . pg_last_error());

echo "<p>OK, j'ai les données\n";
ob_flush(); flush();

// suppression des anciennes données
$query = "DROP TABLE IF EXISTS $BD_table";
pg_query($dbconn, $query);

$query = "DROP INDEX IF EXISTS ".$BD_table."_index;";
pg_query($dbconn, $query);

// création de la structure
$query = "CREATE TABLE $BD_table (
	type character varying(16),
	gml_id character varying(128) NOT NULL,
	commune character varying(128),
	voie character varying(128),
	numerodansvoie character varying(128),
	gestionnaire character varying(128),
	observation character varying(512),
	gid integer,
	miseajourattributs timestamp,
	miseajourgeometrie timestamp,
	geom geometry,
	CONSTRAINT ".$BD_table."_pkey PRIMARY KEY (gid),

	CONSTRAINT enforce_dims_geom CHECK (ndims(geom) = 2),
	CONSTRAINT enforce_geotype_geom CHECK (geometrytype(geom) = 'POINT'::text OR geom IS NULL),
	CONSTRAINT enforce_srid_geom CHECK (srid(geom) = 3857)
);";
pg_query($dbconn, $query);

$query = "create index ".$BD_table."_index on $BD_table using gist (geom);";
pg_query($dbconn, $query);

echo "<p>insertion en cours…";
ob_flush(); flush();

// insertion
foreach($Donnees["features"] as $s) {

	//	nettoyage des données du GL
	$type		= pg_escape_string($s["type"]);
	$gml_id	= pg_escape_string($s["properties"]["gml_id"]);
	$commune	= pg_escape_string($s["properties"]['commune']);
	$voie	= pg_escape_string($s["properties"]['voie']);
	$numerodansvoie	= pg_escape_string($s["properties"]['numerodansvoie']);
	$gestionnaire	= pg_escape_string($s["properties"]['gestionnaire']);
	$observation	= pg_escape_string($s["properties"]['observation']);
	$gid	= $s["properties"]['gid'];
	$miseajourattributs	= $s["properties"]['miseajourattributs'];
	$miseajourgeometrie	= $s["properties"]['miseajourgeometrie'];
	$latitude	= $s['geometry']['coordinates'][0];
	$longitude	= $s['geometry']['coordinates'][1];

	pg_query($dbconn, "INSERT INTO $BD_table
		(type, gml_id, commune, voie, numerodansvoie, gestionnaire, observation, gid, miseajourattributs, miseajourgeometrie, geom)
		VALUES ('$type', '$gml_id', '$commune', '$voie', '$numerodansvoie', '$gestionnaire', '$observation', '$gid', TO_TIMESTAMP('$miseajourattributs', 'DD/MM/YYYY'), TO_TIMESTAMP('$miseajourgeometrie', 'DD/MM/YYYY'), ST_Transform(ST_SetSRID(ST_MakePoint($longitude , $latitude), 4326), 3857))")
			or die("Erreur durant l'insertion de la station dans la base : ".pg_last_error());
}

echo " fini !\n";
?>
