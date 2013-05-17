Trouve-moi un truc à Lyon
=========================

Démonstrateur utilisant les données ouvertes :
* de JCDecaux, pour les vélos en libre service ;
* du Grand Lyon, pour les données urbaines.

Le démonstrateur s'appuie sur :
* les [API de JCDecaux](https://developer.JCDecaux.com/#/opendata/), pour les vélos en libre service ;
* les [API du Grand Lyon](http://catalogue.data.GrandLyon.com/), pour les données urbaines ;
* les [API javaScript de Google Maps](https://developers.google.com/maps/documentation/javascript/reference), pour l'affichage.

Prérequis côté serveur :
* [ogr2ogr de GDAL](http://www.gdal.org/) ([paquet gdal-bin sur Debian](apt://gdal-bin)) ;
* php >= 5.4, avec possibilité d'exécuter des commandes externes.

Prérequis côté client :
* navigateur web à jour ;
* avoir activé javascript ;
* avoir activé la géolocalisation.

Développé avec :
* apache 2.2.22 ;
* php 5.4.4 ;
* gdal 1.9.0 ;
* chromium 26.0.1410.43, Firefox 22.0a2.

Pour cloner le démonstrateur, vous **DEVEZ** créer un fichier de configuration « clés-api.php », contenant vos clés des API :

```php
<?php
// clés-api.php
   $JCDecaux = "XXXXXXX";
   $Google = "YYYYY";
?>
```
Il existe une [instance d'exemple](https://serveur.clauzel.eu/~ltp/Trouve-moi un truc à Lyon/) (sur IPv6).

Par [Damien Clauzel](http://Damien.Clauzel.eu), [@dClauzel](https://Twitter.com/dClauzel), sous licence GPLv3.
