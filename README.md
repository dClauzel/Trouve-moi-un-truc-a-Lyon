Trouve-moi un truc à Lyon
=========================

Démonstrateur utilisant les données ouvertes :
* de JCDecaux, pour les vélos en libre service ;
* du Grand Lyon, pour les données urbaines.

Le démonstrateur s'appuie sur :
* les [API de JCDecaux](https://developer.JCDecaux.com/#/opendata/), pour les vélos en libre service ;
* les [API du Grand Lyon](http://catalogue.data.GrandLyon.com/), pour les données urbaines ;
* les [API javaScript de Google Maps](https://developers.google.com/maps/documentation/javascript/reference), pour l'affichage.

Il existe une [instance d'exemple](https://serveur.clauzel.eu/~ltp/Trouve-moi un truc à Lyon/) (sur IPv6).

Prérequis
=========

Prérequis côté serveur :
* serveur web ;
* [ogr2ogr de GDAL](http://www.gdal.org/) ([paquet gdal-bin sur Debian](apt://gdal-bin)) ;
* php >= 5.4, avec possibilité d'exécuter des commandes externes ;
* postgis >= 1.5, avec postgresql >= 9.1 ([paquet postgresql-9.1-postgis sur Debian](apt://postgresql-9.1-postgis)).

Prérequis côté client :
* navigateur web à jour ;
* avoir activé javascript ;
* avoir activé la géolocalisation.

Développé avec :
* apache 2.2.22 ;
* php 5.4.4 ;
* gdal 1.9.0 ;
* postgresql 9.1 et postgis 1.5 ;
* chromium 26.0.1410.43, Firefox 22.0a2.

Déployer les outils sur son serveur
===================================

Pour cloner le démonstrateur, vous **DEVEZ** renseigner un fichier de configuration « config.php », contenant vos clés des API et les accès à la base de données.

Certains outils utilisent des requêtes sql qui peuvent prendre un certain temps à se terminer. Il est alors possible que les PHP timeout. Si c'est le cas, ajustez les configurations d'apache et de PHP :

* dans php.ini : max_execution_time = 600
* dans apache :
    * Timeout 300
    * FcgidProcessLifeTime 7200
    * FcgidIOTimeout  7200
    * FcgidConnectTimeout 600
    * FcgidIdleTimeout 600

Licence
=======

Par [Damien Clauzel](http://Damien.Clauzel.eu), [@dClauzel](https://Twitter.com/dClauzel), sous licence GPLv3.
