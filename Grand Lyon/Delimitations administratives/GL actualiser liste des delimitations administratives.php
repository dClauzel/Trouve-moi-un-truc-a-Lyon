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

/* récupération des données du GL
Idéalement devrait utiliser https://github.com/geonef/php5-gdal/ (ne fonctionne pas) ou swig de la lib gdal pour créer un module php (erreur de compilation), mais faute de mieux on passe par un appel à un outil externe.
*/
//$tmpfile = uniqid(sys_get_temp_dir()."/LIMITES-ADMINISTRATIVES_");
//`ogr2ogr -f GeoJSON $tmpfile WFS:http://ogc.data.grandlyon.com/gdlyon?SERVICE=WFS adr_voie_lieu.adrlimiteadm.json`;
//$Donnees = json_decode(file_get_contents($tmpfile), true);
//unlink($tmpfile);
$Donnees = json_decode(file_get_contents("adr_voie_lieu.adrlimiteadm.json"), true);

# accès à la base

$BD_table = 'GLlimitesAdministratives';
	

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
	genre character varying(32),
	insee1 character varying(16),
	insee2 character varying(16),
	gid integer,
	geom geometry,

	CONSTRAINT ".$BD_table."_pkey PRIMARY KEY (gid),
	CONSTRAINT enforce_dims_geom CHECK (ndims(geom) = 2),
	CONSTRAINT enforce_geotype_geom CHECK (geometrytype(geom) = 'POLYGON'::text OR geom IS NULL),
	CONSTRAINT enforce_srid_geom CHECK (srid(geom) = 3857)
);

CREATE INDEX ".$BD_table."_index ON $BD_table using gist (geom);
";
pg_query($dbconn, $query);

echo "<p>insertion en cours…";
ob_flush(); flush();

// insertion
foreach($Donnees["features"] as $s) {

	//	nettoyage des données du GL
	$type		= pg_escape_string($s["type"]);
	$gml_id	= pg_escape_string($s["properties"]["gml_id"]);
	$genre	= pg_escape_string($s["properties"]['genre']);
	$insee1	= pg_escape_string($s["properties"]['insee1']);
	$insee2	= pg_escape_string($s["properties"]['insee2']);
	$gid	= $s["properties"]['gid'];

	$queryInsert = "INSERT INTO $BD_table
		(type, gml_id, genre, insee1, insee2, gid, geom)
		VALUES ('$type', '$gml_id', '$genre', '$insee1', '$insee2', '$gid', ST_SetSRID(ST_MakePolygon(ST_GeomFromText('LINESTRING(";
//		VALUES ('$type', '$gml_id', '$genre', '$insee1', '$insee2', '$gid', ST_Transform(ST_SetSRID(ST_MakePolygon(ST_GeomFromText('LINESTRING(";

	/* Pour chaque point du polygone, on ajoute ses coordonnées suivi du séparateur «,».
	 * Après avoir ajouté le dernier point, on réinsert le premier afin de fermer le polygone.
	 */
	$taillePolygone = count($s["geometry"]['coordinates']);	
	$i = 1;
	foreach($s["geometry"]['coordinates'] as $g) {
		$queryInsert .= $g[1] ." ". $g[0];
		if($i < $taillePolygone)
			$queryInsert .= ",";
		else
			$queryInsert .= "," .$s["geometry"]['coordinates'][0][1]. " " .$s["geometry"]['coordinates'][0][0];
		$i++;
	}

	/* fixme : mégacrade
	 * on ajoute encore une fois en fin de ligne le 1e élément, car certaines définition de zone ne comportent que 3 points; ce qui est insuffisant car un polygone nécessite au minimum 4 points.
	 * Du coup, on a un polygone correct, mais avec possiblement plusieurs points confondus… pas gênant, mais pas propre.
	 */
	$queryInsert .= "," .$s["geometry"]['coordinates'][0][1]. " " .$s["geometry"]['coordinates'][0][0];

	$queryInsert .= ")')), 3857) );\n";
//	$queryInsert .= ")')), 4326), 3857) );\n";

	pg_query($dbconn, $queryInsert)
			or die("Erreur durant l'insertion de la station dans la base : ".pg_last_error());
}

echo " fini !\n";
?>

<h1>Technique</h1>
<p>La base a été mise à jour avec la requête suivante :
<pre><code><?php echo securise($query); ?></code></pre>
</body>
</html>
