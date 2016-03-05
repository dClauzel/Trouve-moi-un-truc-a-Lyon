<!doctype html>
<html>
<head>
	<title>Mise à jour de la base</title>
	<meta charset='utf-8'>
	<link rel=stylesheet href='../../Ressources/style général.css'>
</head>
<body>

<?php 
require_once '../../config.php';
require_once '../../Ressources/fonctionsGénériques.php';

echo "<p>Récupération des données. Attention, c'est long.\n";
ob_flush(); flush();

// récupération des données du GL
// fiche : http://data.grandlyon.com/environnement/silos-verre/

$sourceDonnees = 'https://download.data.grandlyon.com/wfs/grandlyon?SERVICE=WFS&VERSION=2.0.0&outputformat=GEOJSON&request=GetFeature&typename=gic_collecte.gicsiloverre&SRSNAME=urn:ogc:def:crs:EPSG::4326';
$Donnees = json_decode(file_get_contents($sourceDonnees), true);

// accès à la base

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
pg_query($dbconn, $query)
	or die("Impossible de droper la base : " . pg_last_error());

$query = "DROP INDEX IF EXISTS ".$BD_table."_index;";
pg_query($dbconn, $query)
	or die("Impossible de droper l'index : " . pg_last_error());

// création de la structure
$query = "CREATE TABLE $BD_table (
	commune character varying(128),
	codepostal integer,
	voie character varying(128),
	numerodansvoie character varying(128),
	gestionnaire character varying(128),
	observation character varying(512),
	identifiant character varying(128),
	codeinsee integer,
	gid integer,
	geom geometry,

	CONSTRAINT ".$BD_table."_pkey PRIMARY KEY (gid),
	CONSTRAINT enforce_dims_geom CHECK (ST_ndims(geom) = 2),
	CONSTRAINT enforce_geotype_geom CHECK (geometrytype(geom) = 'POINT'::text OR geom IS NULL),
	CONSTRAINT enforce_srid_geom CHECK (ST_srid(geom) = 4326)
);

CREATE INDEX ".$BD_table."_index ON $BD_table using gist (geom);
";

pg_query($dbconn, $query)
	or die('Impossible de créer la table : ' . pg_last_error());

echo "<p>insertion en cours…";
ob_flush(); flush();

// insertion
foreach($Donnees["features"] as $s) {

	//	nettoyage des données du GL
	$commune	= pg_escape_string($s["properties"]['commune']);
	$codepostal	= pg_escape_string($s["properties"]['code_postal']);
	$voie	= pg_escape_string($s["properties"]['voie']);
	$numerodansvoie	= pg_escape_string($s["properties"]['numerodansvoie']);
	$gestionnaire	= pg_escape_string($s["properties"]['gestionnaire']);
	$observation	= pg_escape_string($s["properties"]['observation']);
	$identifiant	= pg_escape_string($s["properties"]['identifiant']);
	$codeinsee	= pg_escape_string($s["properties"]['code_insee']);
	$gid	= $s["properties"]['gid'];
	$longitude	= $s['geometry']['coordinates'][0];
	$latitude	= $s['geometry']['coordinates'][1];

	pg_query($dbconn, "INSERT INTO $BD_table
		(commune, codepostal, voie, numerodansvoie, gestionnaire, observation, identifiant, codeinsee, gid, geom)
		VALUES ('$commune', '$codepostal', '$voie', '$numerodansvoie', '$gestionnaire', '$observation', '$identifiant', '$codeinsee', '$gid', ST_SetSRID(ST_MakePoint($longitude, $latitude), 4326))")
			or die("Erreur durant l'insertion de la station dans la base : ".pg_last_error());
}

echo " fini !\n";
ob_flush(); flush();
?>

<h1>Technique</h1>
<p>La base a été mise à jour avec la requête suivante :
<pre><code><?php echo securise($query); ?></code></pre>
</body>
</html>
