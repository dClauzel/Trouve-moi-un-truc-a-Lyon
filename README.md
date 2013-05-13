trouve-moi-un-velov
===================

Démonstrateur utilisant les données ouvertes de JCDecaux pour les vélos en libre service.

Le démonstrateur utilise :
* les [API de JCDecaux](https://developer.jcdecaux.com/#/opendata/) pour les vélos en libre service
* les [API javaScript de Google Maps](https://developers.google.com/maps/documentation/javascript/reference), pour l'affichage

Développé avec apache 2.2.22, php 5.4.4, chromium 26.0.1410.43

Pour cloner le démonstrateur, vous **DEVEZ** créer un fichier de configuration « clés-api.php », contenant vos clés des API :

```php
<?php
// clés-api.php
   $JCDecaux = "XXXXXXX";
   $Google = "YYYYY";
?>
```
Il existe une [instance d'exemple](https://serveur.clauzel.eu/~ltp/trouve-moi-un-velov/) (sur IPv6).
