
function Areago(){
	
	var _self = this;
	var _loadAjaxInfo = false;
	
	var _editingMarkerInfo = null;
	var _editingMarker = null;
	
	var _points = new Array(); //Total de puntos existentes.
	var _inMemoryPoint = null; //Punto en el que estamos trabajando.
	
	var _activePoint = null;
	var _save = false;
	
	var map = null;
	var layers = {}; //Capas de OpenLayers
	var projections = {}; //Proyecciones para modificaciones
	var controls = {}; //Controles de modificacion
	var style_mark = null;
	var addedMarkers = new Array(); //Se puede borrar?
	
	var pointType =  {
			PLAY_ONCE:			0,
			PLAY_LOOP:			1,
			PLAY_UNTILFINISH:	2,
			PLAY_TOGGLE:		3,
			PLAY_CONDITIONAL:	4,
			WIFI:				5				
	};
	
	var _zoomLevels = 22;
	
	
	this.initialize = function(){
		
		createMapAndLayers();
		addControls();
		registerLayersEvents();
		
		initTables();	
		initButtons();
		initSoundPlayer();
		
		initInterface();
		registerDomEvents();
		
		
	}//initialize
	
	
	var initInterface = function (){
		jQuery('.panel_A').hide();
			jQuery('#areago-panel_A-marker-info').hide();
			
		jQuery( "#areago-dialog-confirm" ).dialog({
            resizable: false,
            height:140,
            autoOpen:false,
            modal: true,
            buttons: {
                "Save": function() {
                	guardarPunto();
                	createNewPoint(jQuery("#areago-dialog-confirm .type").val());
                    jQuery( this ).dialog("close");
                },
                Cancel: function() {
                    jQuery( this ).dialog( "close" );
                }
            }
        });
	}
	
	var registerDomEvents = function () {
		
		 jQuery('#marker-radius').change(function () {
			 var nRad = jQuery(this).val();
			 if (parseInt(nRad)){
				 var c = _inMemoryPoint.features[0]._circle;
				 resizeCircle(nRad, c);			
				 _save = true;
			 }

		 });
		
	}
	
	var resizeCircle = function (radius, circle){
		
		var cRadius = circle.radius; // origin radius
		var sFactor = radius / cRadius; // scale factor
		var cP = circle.geometry.bounds.getCenterPixel();  // origin
		circle.radius = radius;
		var centerPoint = new OpenLayers.Geometry.Point(cP.x, cP.y); // transform Pixel to Point
		circle.geometry.resize(sFactor, centerPoint);
		circle.layer.drawFeature(circle);
		_save = true;
		
	}//resizeCircle

	var registerLayersEvents = function () {
        
		layers.circles.events.on({
			
            "featuremodified": eventCircleFeatureModified            
        });
		
		layers.markers.events.on({
			'featuremodified': eventMarkerFeatureModified
		});
		
	} //registerLayersEvents
	
	var markerMovedUpdateData = function(point, marker){
		
		jQuery('#marker-lat').empty().append(point.y);
    	jQuery('#marker-lng').empty().append(point.x);
		
	}
	
	var eventMarkerFeatureModified = function (event){
		
		var point = new OpenLayers.Geometry.Point(event.feature.geometry.x,event.feature.geometry.y);				
		
		var circle = getCircleRelatedToMarker(event.feature);
		if (circle){
			moveCircle(point.x, point.y, circle); //Chequear			
		}
		point.transform(projections.base, projections.vector);
		controls.markerDrag.deactivate();
		markerMovedUpdateData(point, event.feature);
		_save = true;
		
	}
	
	var getCircleRelatedToMarker = function (marker){
		
		var _tmp = _inMemoryPoint.features;
		
		for (var i=0;i<_tmp.length;i++)
		{ 
			if (marker.id == _tmp[i]._marker.id){
				return _tmp[i]._circle;
			}
		}
		
		return false;
		
	}
	
	var moveCircle = function (x, y, circle){
		
		var cP = circle.geometry.bounds.getCenterPixel();
		circle.geometry.move(x - cP.x, y - cP.y);
		circle.layer.drawFeature(circle);
		_save = true;
		
	}// moveCircleById
	
	var circleResizedUpdateData = function (radius, circle){
		
		jQuery('#marker-radius').val(radius);

	}
	
	var eventCircleFeatureModified = function (event){

		var area = event.feature.geometry.getArea();
        var radius = 0.565352 * Math.sqrt(area);
        radius = Math.round(radius);            
        circleResizedUpdateData(radius, event.feature);
        event.feature.radius = radius;
    	controls.circleResize.deactivate();
    	_save = true;

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
	
	this.guardarPunto = function() {
		if (_inMemoryPoint.saved){
			var pos = _inMemoryPoint.position;
			_points[pos] = _inMemoryPoint;
		}else {
			var nPos = _points.push(_inMemoryPoint);
			_points[nPos].saved = true;
			_points[nPos].position = nPos;
		}
		_save = false;
			
	}
	
	var initButtons = function() {
	
		jQuery('#edit-position').button()
	    .click(function(){
	    	//Posiciona el marcador en el punto deseado.
	    	controls.circleResize.deactivate();
	    	controls.markerDrag.activate();
	    	controls.markerDrag.selectFeature(_inMemoryPoint.features[0]._marker);

	    	return false;
	    });
		
		jQuery('#edit-radius').button()
	    .click(function(){
	    	//Posiciona el marcador en el punto deseado.
	    	controls.circleResize.activate();
	    	controls.markerDrag.deactivate();
	     	controls.circleResize.selectFeature(_inMemoryPoint.features[0]._circle);
	    	return false;
	    });
		
		jQuery('#areago-save-point')
			.button()
			.click(function(){
				guardarPunto();
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
	    
	    jQuery('#areago-new-play_once').click(function (){
	    	if (_save){
	    		jQuery( "#areago-dialog-confirm .type" ).val(pointType.PLAY_ONCE);
	    		jQuery( "#areago-dialog-confirm" ).dialog('open');
	    	}
	    	else{
		    	_self.createNewPoint(pointType.PLAY_ONCE);
	    	}
	    	
	    });
	    
	    jQuery('#areago-add-menu').menu().hide();
		
	}//initButtons
	
	var initTables = function(){
		
	    jQuery('#markers-table table tbody tr').click(function(event){

	    	var u = event.currentTarget;
	    	//Pongo como seleccionada....
    	  if ( jQuery(this).hasClass('row_selected') ) {
              jQuery(this).removeClass('row_selected');
          }
          else {
              mTable.$('tr.row_selected').removeClass('row_selected');
              jQuery(this).addClass('row_selected');
          }// row-selected
	    	  
	    	jQuery(mTable.fnSettings().aoData).each(function (){
				if (this.nTr == u){
					addPointFromTable(this._aData);
				};
			});
	    	
	    });//jQuery('#markers-table table tbody tr').dblclick	    	    

		var mTable = jQuery('#markers-table table').dataTable({
	    	'bJQueryUI':true,
	    	width:"100%", 
	    	"sPaginationType": "full_numbers",
	    	'aoColumnDefs':[
	    		 { "bSearchable": false, "bVisible": false, "aTargets": [ 0,2,3 ] }             
	    	]
	    });
	    	    
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
	
	this.createNewPoint = function(type){
		
		var _tmpPoint = {};
		
		_tmpPoint.type = type;
		_tmpPoint.features = new Array();
		
		switch (type)
		{
			case pointType.PLAY_ONCE:
				_tmpPoint.lat = 0;
				_tmpPoint.lon = 0;
				_tmpPoint.radius = 5;
				_tmpPoint.file = "";
				prepareDomForPlay_Once();
				
				break;
			case pointType.PLAY_LOOP:
				_tmpPoint.lat = 0;
				_tmpPoint.lon = 0;
				_tmpPoint.radius = 5;
				_tmpPoint.file = "";
				
				break;
			case pointType.PLAY_UNTILFINISH:
				_tmpPoint.lat = 0;
				_tmpPoint.lon = 0;
				_tmpPoint.radius = 5;
				_tmpPoint.file = "";
				
				break;
			case pointType.PLAY_TOGGLE:
				
				break;
			case pointType.WIFI:
				
				break;
				
		}//switch
		
		_inMemoryPoint = _tmpPoint; 
		
	}// createNewPoint
	
	var prepareDomForPlay_Once = function (){
		
		jQuery('#marker-title').empty();
    	jQuery('#marker-lat').empty();
    	jQuery('#marker-lng').empty();
		jQuery("#jquery_jplayer_1").jPlayer('setMedia',{mp3:""});
		jQuery('.panel_A').show();
		jQuery('#areago-panel_A-marker-info').hide();

	} // prepareDomForPlay
	
	var cleanLayers = function (features){

		for (var i=0; i<features.length; i++){
			layers.circles.removeFeatures([features[i]._circle]);
			layers.markers.removeFeatures([features[i]._marker]);
		}
	} // clearLayers
	
	var addPointFromTable = function(data){
	
		//0 = ID
		//1 = Titulo
		//2 = Latitud
		//3 = Longitud
		//4 = Autor
		
		var dataFromMarker = {
				action: 'areago_get_marker',
				id: data[0],			
			};
		
		
		if (_inMemoryPoint.length != 0){
			//Se supone que tenemos un punto, y no deber’amos, por lo que me lo cargo
			cleanLayers(_inMemoryPoint.features);
			_inMemoryPoint = {};
		}
		
		var fLon = parseFloat(data[3]);
		var fLat = parseFloat(data[2]);
		
		var circle = _self.addCircleToMap(fLon, fLat, data[0]);
		var marker = _self.addMarkerToMap(fLon, fLat, data[0]);
		
		controls.circleResize.activate();
		
		_loadAjaxInfo = true;

		_save = true;
		_inMemoryPoint.ID = data[0];
		_inMemoryPoint.lat = fLat;
		_inMemoryPoint.lon = fLon;
		_inMemoryPoint.features = [{_marker: marker, _circle: circle}];
		_inMemoryPoint.saved = false;
		
		jQuery.post(ajaxurl, dataFromMarker, function(response) {			
											
				if(_loadAjaxInfo){
					_inMemoryPoint.response = response;
					updateMarkerDataInDom(response);
					_inMemoryPoint.file = response.marker.attachments[0].name;
					_loadAjaxInfo=false;
				}
				
		}, 'json');  // jQuery.post
		
		
		
	}//addPoint
	
	var updateMarkerDataInDom = function (data){

		jQuery('#marker-title').empty().append(data.title);
    	jQuery('#marker-lat').empty().append(data.marker.lat[0]);
    	jQuery('#marker-lng').empty().append(data.marker.lng[0]);
		jQuery("#jquery_jplayer_1").jPlayer('setMedia',{mp3:data.marker.attachments[0].fileURI});
		jQuery('#areago-panel_A-marker-info').show();

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