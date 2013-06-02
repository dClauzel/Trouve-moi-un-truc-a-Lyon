var latitude;
var longitude;
var timestamp;
var altitude;
var accuracy;
var altitudeAccuracy;
var heading;
var speed;

// On teste ici si le module HTML5 de géolocalisation est bien supporté
function initialize(){
	if (navigator.geolocation) {
		var options = {
			enableHighAccuracy: true,	// Permet de forcer l'utilisation du GPS si disponible (au lieu de la triangulation wifi)
			maximumAge: 60000,	// Durée de vie maximale en milliseconde de la variable coordonnées
			timeout: 10000	// Défini un temps maximal d’exécution (en milliseconde)
		}
		navigator.geolocation.getCurrentPosition(localise_moi, getError, options);
	}
	else
		alert("Dommage, nous ne pourrons vous géolocaliser");
}

// Ajoutons au document la latitude et la longitude
function localise_moi(position){
	if (position != null) {
		latitude = position.coords.latitude;
		longitude = position.coords.longitude;
		timestamp = position.coords.timestamp;
		altitude = position.coords.altitude;
		accuracy = position.coords.accuracy;
		altitudeAccuracy = position.coords.altitudeAccuracy;
		heading = position.coords.heading;
		speed = position.coords.speed;

		document.getElementById("latitude").innerHTML = latitude;
		document.getElementById("longitude").innerHTML = longitude;
		document.getElementById("timestamp").innerHTML = timestamp;
		document.getElementById("altitude").innerHTML = altitude;
		document.getElementById("accuracy").innerHTML = accuracy;
		document.getElementById("altitudeAccuracy").innerHTML = altitudeAccuracy;
		document.getElementById("heading").innerHTML = heading;
		document.getElementById("speed").innerHTML = speed;

		document.getElementById('formLatitude').value = latitude; 
		document.getElementById("formLongitude").value = longitude;
		document.getElementById("formTimestamp").value = timestamp;
		document.getElementById("formAltitude").value = altitude;
		document.getElementById("formAccuracy").value = accuracy;
		document.getElementById("formAltitudeAccuracy").value = altitudeAccuracy;
		document.getElementById("formHeading").value = heading;
		document.getElementById("formSpeed").value = speed;
	}
}

// gestion des erreurs de la géolocalisation (pas utilisé)
function getError( error ){
	switch( error.code ){

		case error.PERMISSION_DENIED :
			//L'utilisateur n'a pas accepté de partager sa position
			break;
 
		case error.POSITION_UNAVAILABLE :
			//La position n'a pas pu être définie
			break;

		case error.TIMEOUT :
			//Le service de localisation n'a pas répondu à temps
			break;
	}
}
