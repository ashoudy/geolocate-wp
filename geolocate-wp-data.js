(function($) {

function render_map( $el ) {
    // var
    
    var $markers = $el.find('.marker');

if ($(window).width() < 768) {
    $draggable = false;
} else {
    $draggable = true;
}
    // vars
    var args = {
        zoom        : 14,
        center      : new google.maps.LatLng(43.661471,-70.255326),
        mapTypeId   : google.maps.MapTypeId.ROADMAP,
        scrollwheel : 0,
        draggable: $draggable,
    };
    // create map
    var map = new google.maps.Map( $el[0], args);
    // add a markers reference
    map.markers = [];
    // add markers
    $markers.each(function(){
        add_marker( $(this), map );
    });
    // center map
    center_map( map );
}

// create info window
var infowindow = new google.maps.InfoWindow({});

function add_marker( $marker, map ) {
    // var
console.log($marker);
    var latlng = new google.maps.LatLng( $marker.attr('data-lat'), $marker.attr('data-lng') );
var ico = $marker.attr('data-icon');
    // create marker
    var marker = new google.maps.Marker({
        position    : latlng,
        map         : map,
icon:ico
    });
    // add to array
    map.markers.push( marker );
    // if marker contains HTML, add it to an infoWindow
    if( $marker.html() )
    {

        // show info window when marker is clicked
        google.maps.event.addListener(marker, 'click', function() {
            infowindow.setContent( $marker.html() );
            infowindow.open( map, marker );
        });
    }
}

function center_map( map ) {
    // vars
    var bounds = new google.maps.LatLngBounds();
    // loop through all markers and create bounds
    $.each( map.markers, function( i, marker ){
        var latlng = new google.maps.LatLng( marker.position.lat(), marker.position.lng() );
        bounds.extend( latlng );
    });

    // only 1 marker?
    if( map.markers.length == 1 )
    {
        // set center of map
        map.setCenter( bounds.getCenter() );
        map.setZoom( 16 );
    }
    else
    {
        // fit to bounds
        map.fitBounds( bounds );
    }
}
function getDataCall(cur){
	   $.ajax({
        url: './wp-content/plugins/foodtrucks/ajax/getdata.php',
        data: {
		"cur": 1     },
        async: false,
        type: 'POST',
        dataType: 'json',
	error:function(data){
		console.log(data);
	},
        success:function(data){
        	console.log(data);
        	$.each(data,function(i,v){
		$('.acf-map').append(v);
		});
        
        }
    });
    $('.acf-map').each(function(){
        render_map( $(this) );
    });	
}
$(document).ready(function(){
	getDataCall();



  });
})(jQuery);
