jQuery(document).ready(function($) {
		
	var form = $('.wcfmmp-product-geolocate-search-form');
	var xhr;
	var timer = null;
	
	if( $('.wcfmmp-product-geolocate-search-form').length > 0 ) {
		
		if( $('#wcfmmp_radius_addr').length > 0 ) {
			var max_radius = parseInt( wcfmmp_product_list_options.max_radius );
			var wcfmmp_radius_addr_input = document.getElementById("wcfmmp_radius_addr");
			var geocoder = new google.maps.Geocoder;
			var awcfmmp_radius_addr_autocomplete = new google.maps.places.Autocomplete(wcfmmp_radius_addr_input);
			awcfmmp_radius_addr_autocomplete.addListener("place_changed", function() {
				var place = awcfmmp_radius_addr_autocomplete.getPlace();
				$('#wcfmmp_radius_lat').val(place.geometry.location.lat());
				$('#wcfmmp_radius_lng').val(place.geometry.location.lng());
				//form.submit();
			});
			
			$('#wcfmmp_radius_range').on('input', function() {
				$('.wcfmmp_radius_range_cur').html(this.value+'km');
				$('.wcfmmp_radius_range_cur').css( 'left', ((this.value/max_radius)*$('.wcfm_radius_slidecontainer').outerWidth())-(15/2)+'px' );
				$wcfmmp_radius_lat = $('#wcfmmp_radius_lat').val();
				if( $wcfmmp_radius_lat ) {
					//setTimeout(function() {form.submit();}, 100);
				}
			});
			$('.wcfmmp_radius_range_cur').css( 'left', ((10/max_radius)*$('.wcfm_radius_slidecontainer').outerWidth())-(15/2)+'px' );
			
			if ( navigator.geolocation ) {
				$('.wcfmmmp_locate_icon').on( 'click', function () {
					setUser_CurrentLocation();
				});
				
				if( wcfmmp_product_list_options.is_geolocate ) {
					$('.wcfmmmp_locate_icon').click();
				}
				
				function setUser_CurrentLocation() {
					navigator.geolocation.getCurrentPosition( function( position ) {
						console.log( position.coords.latitude, position.coords.longitude );
						geocoder.geocode( {
                location: {
                    lat: position.coords.latitude,
                    lng: position.coords.longitude
                }
            }, function ( results, status ) {
                if ( 'OK' === status ) {
									$('#wcfmmp_radius_addr').val( results[0].formatted_address );
									$('#wcfmmp_radius_lat').val( position.coords.latitude );
									$('#wcfmmp_radius_lng').val( position.coords.longitude );
									if( wcfmmp_product_list_options.is_geolocate ) {
									  //form.submit();
									}
                }
            } )
					});
				}
			}
		}
	}
	
	function fetchMarkers() {
		if( $('.wcfmmp-product-list-map').length > 0 ) {
			reloadMarkers();
			
			var data = {
				search_term             : '',
				wcfmmp_store_category   : '',
				wcfmmp_store_country    : '',
				wcfmmp_store_state      : '',
				action                  : 'wcfmmp_stores_list_map_markers',
				pagination_base         : 1,
				paged                   : 1,
				per_row                 : $per_row,
				per_page                : $per_page,
				includes                : $includes,
				excludes                : $excludes,
				has_product             : $has_product,
				has_orderby             : $has_orderby,
				sidebar                 : $sidebar,
				theme                   : $theme,
				search_data             : '' //jQuery('.wcfmmp-product-geolocate-search-form').serialize(),
			};
			
			xhr = $.post(wcfm_params.ajax_url, data, function(response) {
				if (response.success) {
					var locations = response.data;
					setMarkers( $.parseJSON(locations) );
				}
			});
		}
	}
	
	// Store List Map
	if( $('.wcfmmp-product-list-map').length > 0 ) {
		$('.wcfmmp-product-list-map').css( 'height', $('.wcfmmp-product-list-map').outerWidth()/2);
		
		var markers = [];
		var store_list_map = '';
		
		function setMarkers(locations) {
			var latlngbounds = new google.maps.LatLngBounds();
			var infowindow = new google.maps.InfoWindow();
				
			$.each(locations, function( i, beach ) {
				var myLatLng = new google.maps.LatLng(beach.lat, beach.lang);
				latlngbounds.extend(myLatLng);
				var marker = new google.maps.Marker({
						position: myLatLng,
						map: store_list_map,
						animation: google.maps.Animation.DROP,
						title: beach.name,
						icon: beach.icon,
						zIndex: i 
				});
				
				var infoWindowContent = beach.info_window_content;
				
				google.maps.event.addListener(marker, 'click', (function(marker, i) {
					return function() {
						infowindow.setContent(infoWindowContent);
						infowindow.open(store_list_map, marker);
					}
				})(marker, i));
				
				store_list_map.setCenter(marker.getPosition());

				// Push marker to markers array                                   
				markers.push(marker);
			});
			if( $auto_zoom && locations.length > 0 ) {
			  store_list_map.fitBounds(latlngbounds);
			}
		}
		
		function reloadMarkers() {
			for( var i = 0; i < markers.length; i++ ) {
				markers[i].setMap(null);
			}
			markers = [];
		}
		
		if( !wcfmmp_product_list_options.is_poi ) {
			var myStyles =[
											{
													featureType: "poi",
													elementType: "labels",
													stylers: [
																{ visibility: "off" }
													]
											}
									];
		} else {
			var myStyles =[];
		}
		
		var mapOptions = {
        zoom: $map_zoom,
        center: new google.maps.LatLng(wcfmmp_product_list_options.default_lat,wcfmmp_product_list_options.default_lng,13),
        mapTypeId: google.maps.MapTypeId.ROADMAP,
        styles: myStyles
    }

    store_list_map = new google.maps.Map(document.getElementById('wcfmmp-product-list-map'), mapOptions);
    fetchMarkers();
	}
});