
function Areago(){
	
	var _self = this;
	var _loadAjaxInfo = false;
	var _editingMarkerInfo = null;
	
	var _editingMarker = null;
	
	var map = null;
	var layers = {};
	var projections = {};
	var controls = {};
	var style_mark = null;
	var addedMarkers = new Array();
	
	
	var _zoomLevels = 22;
	
	this.initialize = function(){
		
		createMapAndLayers();
		addControls();
		registerLayersEvents();
		
		initTables();	
		initButtons();
		initSoundPlayer();
		
		
	}//initialize

	var registerLayersEvents = function () {
        
		layers.circles.events.on({
			
            "featuremodified": updateRadius            
        });
		
		layers.markers.events.on({
			'featuremodified': updateMarkerPosition
		});
		
	} //registerLayersEvents
	
	var updateMarkerPosition = function (event){
		
		console.log(event);
		var point = new OpenLayers.Geometry.Point(event.feature.geometry.x,event.feature.geometry.y);
		point.transform(projections.base, projections.vector);
		var id = event.feature.ID;
		var f = layers.circles.features.searchMarkerById(id);
		var circle = layers.circles.features[f];
		console.log(point);
		console.log(circle);
		
	}
	
	var updateRadius = function (event){

		var area = event.feature.geometry.getArea();
        var radius = 0.565352 * Math.sqrt(area);
        radius = Math.round(radius);            
        
        jQuery('#marker-radius').val(radius);
        event.feature.radius = radius;

	} // updateRadius
	
	var initSoundPlayer = function () {
		
		var myPlayer = jQuery("#jquery_jplayer_1"),
		myPlayerData,
		fixFlash_mp4, // Flag: The m4a and m4v Flash player gives some old currentTime values when changed.
		fixFlash_mp4_id, // Timeout ID used with fixFlash_mp4
		ignore_timeupdate, // Flag used with fixFlash_mp4
		options = {
			ready: function (event) {

				// Determine if Flash is being used and the mp4 media type is supplied. BTW, Supplying both mp3 and mp4 is pointless.

				fixFlash_mp4 = event.jPlayer.flash.used && /m4a|m4v/.test(event.jPlayer.options.supplied);

			},

			timeupdate: function(event) {
				if(!ignore_timeupdate) {
					myControl.progress.slider("value", event.jPlayer.status.currentPercentAbsolute);
				}
			},

			swfPath: "js",
			supplied: "mp3",
			cssSelectorAncestor: "#jp_container_1",
			wmode: "window"
		},

		myControl = {
			progress: jQuery(options.cssSelectorAncestor + " .jp-progress-slider")
		};

	// Instance jPlayer
		myPlayer.jPlayer(options);
		// A pointer to the jPlayer data object
		myPlayerData = myPlayer.data("jPlayer");

		// Define hover states of the buttons
		jQuery('.jp-gui ul li').hover(
			function() { jQuery(this).addClass('ui-state-hover'); },
			function() { jQuery(this).removeClass('ui-state-hover'); }
		);
		// Create the progress slider control

		myControl.progress.slider({

			animate: "fast",
			max: 100,
			range: "min",
			step: 0.1,
			value : 0,
			slide: function(event, ui) {

				var sp = myPlayerData.status.seekPercent;
				if(sp > 0) {

					// Apply a fix to mp4 formats when the Flash is used.

					if(fixFlash_mp4) {
						ignore_timeupdate = true;
						clearTimeout(fixFlash_mp4_id);
						fixFlash_mp4_id = setTimeout(function() {
							ignore_timeupdate = false;
						},1000);

					}

					// Move the play-head to the value and factor in the seek percent.
					myPlayer.jPlayer("playHead", ui.value * (100 / sp));

				} else {

					// Create a timeout to reset this slider to zero.

					setTimeout(function() {
						myControl.progress.slider("value", 0);
					}, 0);

				}

			}

		}); //Progress slider

		
	}//initSoundPlayer
	
	var initButtons = function() {
	
		jQuery('#edit-position').button()
	    .click(function(){
	    	//Posiciona el marcador en el punto deseado.
	    	controls.circleResize.deactivate();
	    	controls.markerDrag.activate();
	    	controls.markerDrag.selectFeature(_editingMarker);
	    	//console.log(_editingMarker);
	    	return false;
	    });
		
	    jQuery('#areago-add-button')
		    .button({
		    	icons:{ primary:"ui-icon-triangle-1-s"}
		    })
	    	.click(function(){
	    		var menu = jQuery('#areago-add-menu').show().position({
	    			my: 'left top',
	    			at: 'left bottom',
	    			of: this
	    		});
	    		
	    		jQuery(document).one('click', function(){
	    			menu.hide();
	    		})
	    		
	    		return false;
	    	
	    	})
	    	.buttonset(); //  jQuery('#areago-add-button')
	    
	    jQuery('#areago-add-menu').menu().hide();
		
	}//initButtons
	
	var initTables = function(){
		
	    var mTable = jQuery('#markers-table table').dataTable({
	    	'bJQueryUI':true,
	    	width:"100%", 
	    	"sPaginationType": "full_numbers",
	    	'aoColumnDefs':[
	    		 { "bSearchable": false, "bVisible": false, "aTargets": [ 0,2,3 ] }             
	    	]
	    });
	    
	    jQuery('#markers-table table tbody tr').dblclick(function(event){

	    	var u = event.currentTarget;
	    	jQuery(mTable.fnSettings().aoData).each(function (){
				if (this.nTr == u){
					addPoint(this._aData);
				};
			});
	    	
	    });//jQuery('#markers-table table tbody tr').dblclick	    	    
	    
	} //initTables
	
	var createMapAndLayers = function(){
		
		var mapOptions = {
				controls:[
						new OpenLayers.Control.Navigation(),
						new OpenLayers.Control.PanZoomBar(),
						new OpenLayers.Control.LayerSwitcher({'ascending':false}),
						new OpenLayers.Control.Permalink(),
						new OpenLayers.Control.ScaleLine(),
						new OpenLayers.Control.Permalink('permalink'),
						new OpenLayers.Control.MousePosition(),
						new OpenLayers.Control.OverviewMap(),
						new OpenLayers.Control.KeyboardDefaults()
				          ]
			}// mapOptions;
		
		var mapDiv = 'map';
		
		//Create the map
		map = new OpenLayers.Map(mapDiv, mapOptions);
		
		layers = {				
				base:		new OpenLayers.Layer.Google('Google Satellite',{type:google.maps.MapTypeId.SATELLITE, numZoomLevels:_zoomLevels, sphericalMercator:true}),
				circles: 	new OpenLayers.Layer.Vector('Circles', {numZoomLevels:_zoomLevels}),
				markers: 	new OpenLayers.Layer.Vector('Markers', {numZoomLevels:_zoomLevels})
		};
		
		projections = {
			base:	new OpenLayers.Projection('EPSG:900913'),
			vector: new OpenLayers.Projection('EPSG:4326')
		};
		
		map.addLayers([layers.base, layers.circles, layers.markers]);	
		
	    style_mark = OpenLayers.Util.extend({}, OpenLayers.Feature.Vector.style['default']);
	    style_mark.graphicWidth = 21;
	    style_mark.graphicHeight = 25;
	    style_mark.graphicXOffset = -10; 
	    style_mark.graphicYOffset = -25;
	    style_mark.graphicOpacity = 1;
	    style_mark.externalGraphic = "../wp-content/plugins/areago-plugin/img/marker-green.png";
	    
	    var center = new OpenLayers.LonLat(AreagoOptions.lng, AreagoOptions.lat);	    
	    center.transform(projections.vector, projections.base);
	    map.setCenter(center, AreagoOptions.zoom); 
	
	}//createMapAndLayers
	
	var addControls = function(){
		
		controls = {
				circleResize:  	new OpenLayers.Control.ModifyFeature(layers.circles),
				markerDrag:		new OpenLayers.Control.ModifyFeature(layers.markers)
		};
	    
		controls.circleResize.mode |= OpenLayers.Control.ModifyFeature.RESIZE;
		controls.circleResize.mode &= ~OpenLayers.Control.ModifyFeature.RESHAPE;
		
		controls.markerDrag.mode |= OpenLayers.Control.ModifyFeature.DRAG;
		controls.markerDrag.standalone = true;
		
	    map.addControls([controls.circleResize, controls.markerDrag]);
	
	}//adControls
	
	this.addMarkerToMap = function (lon, lat, id){

		var markerPosition = new OpenLayers.LonLat(lon,lat);  // Posici—n del punto.
		markerPosition.transform(projections.vector,projections.base); //Cambio a la proyecci—n adecuada.
		var point = new OpenLayers.Geometry.Point(markerPosition.lon,markerPosition.lat);  //Paso a punto.
		
		var mark = new OpenLayers.Feature.Vector(point,null,style_mark); //Creo la marca
		
		mark.ID = id;
		
		layers.markers.addFeatures([mark]); //La a–ado a la capa
		
		return mark;
		
	}
	
	this.addCircleToMap = function (lon, lat, id){

		var markerPosition = new OpenLayers.LonLat(lon,lat);  // Posici—n del punto.
		markerPosition.transform(projections.vector,projections.base); //Cambio a la proyecci—n adecuada.

		var point = new OpenLayers.Geometry.Point(markerPosition.lon,markerPosition.lat);
		var circle = new OpenLayers.Geometry.Polygon.createRegularPolygon(point,5,40);
		var fcircle = new OpenLayers.Feature.Vector(circle);
		
		fcircle.radius=5;
		fcircle.ID = id;
		
		layers.circles.addFeatures([fcircle]);
		
		return fcircle;
		
	}
	
	var addPoint = function(data){
	
		//0 = ID
		//1 = Titulo
		//2 = Latitud
		//3 = Longitud
		//4 = Autor
		
		var dataFromMarker = {
				action: 'areago_get_marker',
				id: data[0],			
			};
		
		var fLon = parseFloat(data[3]);
		var fLat = parseFloat(data[2]);
		
		_self.addCircleToMap(fLon, fLat, data[0]);
		_editingMarker = _self.addMarkerToMap(fLon, fLat, data[0]);
		
		controls.circleResize.activate();
		
		_loadAjaxInfo = true;

		jQuery.post(ajaxurl, dataFromMarker, function(response) {			
				
				addedMarkers.push(response);
				
				if(_loadAjaxInfo){
					_editingMarkerInfo = response;
					updateMarkerDataInDom(response);
					_loadAjaxInfo=false;
				}
		}, 'json');  // jQuery.post
		
		
	}//addPoint
	
	var updateMarkerDataInDom = function (data){

		jQuery('#marker-title').empty().append(data.title);
    	jQuery('#marker-lat').empty().append(data.marker.lat[0]);
    	jQuery('#marker-lng').empty().append(data.marker.lng[0]);
		jQuery("#jquery_jplayer_1").jPlayer('setMedia',{mp3:data.marker.attachments[0].fileURI});

	}//updateMarkerDataInDom
	
	
}//Areago


jQuery('document').ready(function(){
	
	var _areago = new Areago();
	_areago.initialize();
	
});


Array.prototype.searchMarkerById=function(id)
{
	var position =-1;
  for (i=0;i<this.length;i++){
	  if (this[i].ID == id)
		  {
		  	position= i;
		  	i=this.lenght;
		  }	  
  }
  
  return position;

}