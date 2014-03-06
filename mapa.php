<!DOCTYPE html>
<html>
<head>
<script
src="http://maps.googleapis.com/maps/api/js?key=AIzaSyBNBAN_ZshiLELgs_YFQ-PGXGiDi82gVxc&sensor=false">
</script>

<?php 
	$points = array(	array('pname'=> 'point1','lat'=>18.176,'lon'=>-67.1401),
						array('pname'=> 'point2','lat'=>18.1928,'lon'=>-67.1251),
						array('pname'=> 'point3','lat'=>18.2094,'lon'=>-67.1067),
						array('pname'=> 'point4','lat'=>18.2261,'lon'=>-67.0917),
						array('pname'=> 'point5','lat'=>18.2428,'lon'=>-67.2751),
						array('pname'=> 'point6','lat'=>18.2594,'lon'=>-67.1567)
						
						);
	
	$points_js = json_encode(json_encode($points));
?>


<script>
	var myCenter=new google.maps.LatLng(18.176,-67.1401);

	function initialize()
	{
	
		var pointlist = JSON.parse( <?php echo $points_js?>, function (key, val) {
			if ( typeof val === 'string' ) {
				// regular expression to remove extra white space
				if ( val.indexOf('\n') !== -1 ) {
					var re = /\s\s+/g;
					return val.replace(re, ' ');
				} else {
					return val;
				}
			}
			return val;
		} );
	
		var mapProp = {center:myCenter,zoom:1,mapTypeId:google.maps.MapTypeId.HYBRID};

		var map=new google.maps.Map(document.getElementById("googleMap"),mapProp);
		
		var infowindow = new google.maps.InfoWindow();

		var bounds = new google.maps.LatLngBounds();
		
		var marker, i, latlong;
		
		if(pointlist.length != 0){

		for (i = 0; i < pointlist.length; i++) {
			latlong = new google.maps.LatLng(pointlist[i].lat, pointlist[i].lon);
			bounds.extend(latlong);
			marker = new google.maps.Marker({
				position: latlong,
				map: map
			});

		  	google.maps.event.addListener(marker, 'click', (function(marker, i) {
				return function() {
			  		infowindow.setContent(pointlist[i].pname + "<br>" + "Latitude : " + pointlist[i].lat + "<br>" + "Longitude : " + pointlist[i].lon);
			  		infowindow.open(map, marker);
				}
		  	})(marker, i));
		
		}
		
		
			map.fitBounds(bounds);
		}
	}
	
	google.maps.event.addDomListener(window, 'load', initialize);
</script>
</head>

<body>
<div id="googleMap" style="width:500px;height:380px;"></div>

</body>
</html>
