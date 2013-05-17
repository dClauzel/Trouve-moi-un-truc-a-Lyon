<?php

echo "<!DOCTYPE html>
<html>
	<head>
		<title>Trouve-moi un truc à Lyon</title>
		<meta name='viewport' content='initial-scale=1.0, user-scalable=no'>
		<style type='text/css'>
			html { height: 100% }
			body { height: 100% }
			#map-canvas {
				width: 50%;
				height: 50%;
				display: block;
				margin-left: auto;
				margin-right: auto 
			}
		</style>
		<script type='text/javascript' src='https://maps.googleapis.com/maps/api/js?key=$Google&sensor=true'></script>
		<script type='text/javascript'>
			function initialize() {

				// init de la carte centrée sur ma position
				var mapOptions = {
					center: new google.maps.LatLng($latitude, $longitude),
					zoom: 15,
					mapTypeId: google.maps.MapTypeId.HYBRID
				}

				var map = new google.maps.Map(document.getElementById('map-canvas'), mapOptions);

				// Marqueur bleu pour dire où on est
				blueIcon = 'http://www.google.com/intl/en_us/mapfiles/ms/micons/blue-dot.png';

				var marker = new google.maps.Marker({
					position: new google.maps.LatLng($latitude, $longitude),
					map: map,
					icon: blueIcon,
					title: 'Je suis ici'
				});\n";
?>
