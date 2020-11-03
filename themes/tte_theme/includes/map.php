

				<script type="text/javascript" src="https://maps.googleapis.com/maps/api/js?key=AIzaSyAjxIO-rG4dFfj9UCIlbX18JzTbyVJ3YEQ"></script>
				<script type="text/javascript">
/*
* 5 ways to customize the Google Maps infowindow
* 2015 - en.marnoto.com
* http://en.marnoto.com/2014/09/5-formas-de-personalizar-infowindow.html
*/

// map center
var center = new google.maps.LatLng(25.181173,55.3344663);

// marker position
var factory = new google.maps.LatLng(25.181173,55.3344663);

function initialize() {


var mapStyle = [
	{
			"featureType": "water",
			"elementType": "geometry",
			"stylers": [
					{
							"color": "#eeeeee"
					},
					{
							"lightness": 17
					}
			]
	},
	{
			"featureType": "landscape",
			"elementType": "geometry",
			"stylers": [
					{
							"color": "#e1e1e1"
					},
					{
							"lightness": 20
					}
			]
	},
	{
			"featureType": "road.highway",
			"elementType": "geometry.fill",
			"stylers": [
					{
							"color": "#ffffff"
					},
					{
							"lightness": 17
					}
			]
	},
	{
			"featureType": "road.highway",
			"elementType": "geometry.stroke",
			"stylers": [
					{
							"color": "#ffffff"
					},
					{
							"lightness": 29
					},
					{
							"weight": 0.2
					}
			]
	},
	{
			"featureType": "road.arterial",
			"elementType": "geometry",
			"stylers": [
					{
							"color": "#ffffff"
					},
					{
							"lightness": 18
					}
			]
	},
	{
			"featureType": "road.local",
			"elementType": "geometry",
			"stylers": [
					{
							"color": "#ffffff"
					},
					{
							"lightness": 16
					}
			]
	},
	{
			"featureType": "poi",
			"elementType": "geometry",
			"stylers": [
					{
							"color": "#f5f5f5"
					},
					{
							"lightness": 21
					}
			]
	},
	{
			"featureType": "poi.park",
			"elementType": "geometry",
			"stylers": [
					{
							"color": "#dedede"
					},
					{
							"lightness": 21
					}
			]
	},
	{
			"elementType": "labels.text.stroke",
			"stylers": [
					{
							"visibility": "on"
					},
					{
							"color": "#ffffff"
					},
					{
							"lightness": 16
					}
			]
	},
	{
			"elementType": "labels.text.fill",
			"stylers": [
					{
							"saturation": 0
					},
					{
							"color": "#51327d"
					},
					{
							"lightness": 0
					}
			]
	},
	{
			"elementType": "labels.icon",
			"stylers": [
					{
							"visibility": "off"
					}
			]
	},
	{
			"featureType": "transit",
			"elementType": "geometry",
			"stylers": [
					{
							"color": "#f2f2f2"
					},
					{
							"lightness": 19
					}
			]
	},
	{
			"featureType": "administrative",
			"elementType": "geometry.fill",
			"stylers": [
					{
							"color": "#fefefe"
					},
					{
							"lightness": 20
					}
			]
	},
	{
			"featureType": "administrative",
			"elementType": "geometry.stroke",
			"stylers": [
					{
							"color": "#fefefe"
					},
					{
							"lightness": 17
					},
					{
							"weight": 1.2
					}
			]
	}
];

var mapOptions = {
center: center,
zoom: 16,
mapTypeId: google.maps.MapTypeId.ROADMAP,
styles: mapStyle
};

var mapIconUrl = CCM_REL + "/themes/tte_theme/images/map-icon.png";

var map = new google.maps.Map(document.getElementById("map_canvas"),mapOptions);

// InfoWindow content
var content = '<div id="iw-container">' +
						'<div class="iw-content">' +
						'<p>Al Aweer, Ras Al Khor,<br/> Dubai, United Arab Emirates<br/>+971 4 3168000<br/><a href="mailto:website@tte.ae">website@tte.ae</a></p>'
						'</div>' +
						'<div class="iw-bottom-gradient"></div>' +
					'</div>';

// A new Info Window is created and set content
var infowindow = new google.maps.InfoWindow({
content: content,

// Assign a maximum value for the width of the infowindow allows
// greater control over the various content elements
maxWidth: 350
});

// marker options
var marker = new google.maps.Marker({
position: factory,
map: map,
title:"TTE",
icon: mapIconUrl
});

// This event expects a click on a marker
// When this event is fired the Info Window is opened.
google.maps.event.addListener(marker, 'click', function() {
infowindow.open(map,marker);
});
infowindow.open(map,marker);

// Event that closes the Info Window with a click on the map
google.maps.event.addListener(map, 'click', function() {
infowindow.close();
});

// *
// START INFOWINDOW CUSTOMIZE.
// The google.maps.event.addListener() event expects
// the creation of the infowindow HTML structure 'domready'
// and before the opening of the infowindow, defined styles are applied.
// *
google.maps.event.addListener(infowindow, 'domready', function() {

// Reference to the DIV that wraps the bottom of infowindow
var iwOuter = $('.gm-style-iw');

/* Since this div is in a position prior to .gm-div style-iw.
* We use jQuery and create a iwBackground variable,
* and took advantage of the existing reference .gm-style-iw for the previous div with .prev().
*/
var iwBackground = iwOuter.prev();

// Removes background shadow DIV
iwBackground.children(':nth-child(2)').css({'display' : 'none'});

// Removes white background DIV
iwBackground.children(':nth-child(4)').css({'display' : 'none'});

// Moves the infowindow 115px to the right.
// iwOuter.parent().parent().css({left: '115px'});

// Moves the shadow of the arrow 76px to the left margin.
// iwBackground.children(':nth-child(1)').attr('style', function(i,s){ return s + 'left: 76px !important;'});

// Moves the arrow 76px to the left margin.
// iwBackground.children(':nth-child(3)').attr('style', function(i,s){ return s + 'left: 76px !important;'});

// Changes the desired tail shadow color.
iwBackground.children(':nth-child(3)').find('div').children().css({'background': '#51327d', 'box-shadow': '', 'z-index' : '1'});

// Reference to the div that groups the close button elements.
var iwCloseBtn = iwOuter.next();

// Apply the desired effect to the close button
iwCloseBtn.css({opacity: '1', right: '0', top: '3px', border: '0', 'border-radius': '100%', 'box-shadow': '0 0 0', 'background-color': '#51327d', 'display': 'none'});

// If the content of infowindow not exceed the set maximum height, then the gradient is removed.
if($('.iw-content').height() < 140){
$('.iw-bottom-gradient').css({display: 'none'});
}

// The API automatically applies 0.7 opacity to the button after the mouseout event. This function reverses this event to the desired value.
iwCloseBtn.mouseout(function(){
$(this).css({opacity: '1'});
});
});
}
google.maps.event.addDomListener(window, 'load', initialize);

		</script>

		<div class="map_canvas-wrapper">
			<div id="map_canvas"></div><!-- /#map_canvas -->
			<div class="position-helper">
				<div class="container relative">
					<a href="https://goo.gl/CVjh9P" target="_blank" class="map-button map-directions-button">Get directions to our office</a>
					<a href="<?php echo $this->getThemePath(); ?>/images/location_map.jpg" target="_blank" class="map-button map-download-button">Download location map</a>
				</div><!-- /.container -->
			</div><!-- /.position-helper -->
		</div><!-- /.map_canvas-wrapper -->
