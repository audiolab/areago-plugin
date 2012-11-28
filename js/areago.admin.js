
function Areago(){
	
	var _self = this;
	var _loadAjaxInfo = false;
	
	var _editingMarkerInfo = null;
	var _editingMarker = null;
	
	var _points = new Array(); //Total de puntos existentes.
	var _inMemoryPoint = null; //Punto en el que estamos trabajando.
	
	var _activePoint = null;
	var _save = false;
	
	var _referencePoint = -1;
	
	var map = null;
	var layers = {}; //Capas de OpenLayers
	var projections = {}; //Proyecciones para modificaciones
	var controls = {}; //Controles de modificacion
	var style_mark = null;
	var style_mark_red = null;
	var addedMarkers = new Array(); //Se puede borrar?
	var mTable;
	
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
                	_self.guardarPunto();
                	_self.createNewPoint(jQuery("#areago-dialog-confirm .type").val());
                    jQuery( this ).dialog("close");
                },
                Cancel: function() {
                    jQuery( this ).dialog( "close" );
                    _self.createNewPoint(jQuery("#areago-dialog-confirm .type").val());
                }
            }
        });//jQuery( "#areago-dialog-confirm" ).dialog
		
		jQuery('#areago-message').hide();
		jQuery('#areago-point-actions').hide();
	}
	
	var registerDomEvents = function () {
		
		 jQuery('#marker-radius').change(function () {
			 var nRad = jQuery(this).val();
			 if (parseInt(nRad)){
				 var c = _inMemoryPoint.features[0]._circle;
				 resizeCircle(nRad, c);			
				 _save = true;
				 jQuery('#areago-save-point').button( "option", "disabled", false);
			 }

		 });
		
	}
	
	var resizeCircle = function (radius, circle){
		
		var cRadius = circle.radius; // origin radius
		var sFactor = radius / cRadius; // scale factor
		var cP = circle.geometry.bounds.getCenterPixel();  // origin
		circle.attributes.radius = radius;
		var centerPoint = new OpenLayers.Geometry.Point(cP.x, cP.y); // transform Pixel to Point
		circle.geometry.resize(sFactor, centerPoint);
		circle.layer.drawFeature(circle);
		var m = getMarkerRelatedToCircle();
		
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
	

	
	var eventMarkerFeatureModified = function (event){
		
		var latlon = _inMemoryPoint.getPositionLatLon(event.feature);		
		_save = true;		
		jQuery('#areago-save-point').button( "option", "disabled", false);
		controls.markerDrag.deactivate();
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
	
	var getMarkerRelatedToCircle = function (circle){

		var _tmp = _inMemoryPoint.features;
		
		for (var i=0;i<_tmp.length;i++)
		{ 
			if (circle.id == _tmp[i]._circle.id){
				return _tmp[i]._marker;
			}
		}
		
		return false;

	}
	
	var moveCircle = function (x, y, circle){
		
		
	}// moveCircleById
		
	var eventCircleFeatureModified = function (event){
    	controls.circleResize.deactivate();
    	var radius = _inMemoryPoint.getRadius(event.feature);
		jQuery('#marker-radius').empty().append(radius);	
    	jQuery('#areago-save-point').button( "option", "disabled", false);
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
		
		var posi = _points.searchById(_inMemoryPoint.id);
		
		if (posi != -1){
			_points[posi] = _inMemoryPoint;
			if (markAsReference()){
				_referencePoint = posi;
			}//if
		}else {
			var nPos = _points.push(_inMemoryPoint) -1;			
			if (markAsReference()){
				_referencePoint = nPos;
			}
		}
		
		_save = false;
		jQuery('#areago-save-point').button( "option", "disabled", true);
		jQuery('#areago-message').show().hide(2000);
			
	}
	
	var markAsReference = function(){
		
		var ch = jQuery('#areago-reference').attr('checked');
		if (ch == "checked"){
			return true;
		}
		return false;
	}
	
	var serializeData = function() {
		var d = new Array();
				
		if (_points.length > 0){
			for (var i=0; i<_points.length; i++){
				d[i]= _points[i].exportable();				
			}//for	
		}//if
		//console.log(d);
		var fc = {
			type: "FeatureCollection",
			features: d
		};
		return fc;
		
	}
	
	var initButtons = function() {
	
		jQuery('#edit-position').button()
	    .click(function(){
	    	//Posiciona el marcador en el punto deseado.
	    	controls.circleResize.deactivate();
	    	controls.markerDrag.activate();
	    	var _f = _inMemoryPoint.getPoint(0);
	    	controls.markerDrag.selectFeature(_f);
	    	return false;
	    });
		
		jQuery('#edit-radius').button()
	    .click(function(){
	    	//Posiciona el marcador en el punto deseado.
	    	controls.circleResize.activate();
	    	controls.markerDrag.deactivate();
	    	var _f = _inMemoryPoint.getCircle(0);
	     	controls.circleResize.selectFeature(_f);
	    	return false;
	    });
		
		jQuery('#areago-save-point')
			.button({ disabled: true })
			.click(function(){
				_self.guardarPunto();
				return false;
			});
		
		jQuery('#areago-reference')
			.button({disabled: true})
			.click(function(){
				
			});
		
		jQuery('#areago-submit').click(function (e){
			var data = serializeData();
			var dJ = JSON.stringify(data);
			jQuery("#console").append(dJ);
			jQuery('#areago-points').val(dJ);
			//e.preventDefault();
		});
		
	    
	    jQuery('#areago-new-play_once').click(function (e){
	    	if (_save){
	    		jQuery( "#areago-dialog-confirm .type" ).val(pointType.PLAY_ONCE);
	    		jQuery( "#areago-dialog-confirm" ).dialog('open');
	    	}
	    	else{	    			    		
		    	_self.createNewPoint(pointType.PLAY_ONCE);
	    	}
	    	
	    	e.preventDefault();
	    	
	    });
	    
	    
		
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

	    mTable = jQuery('#markers-table table').dataTable({
	    	'bJQueryUI':true,
	    	width:"100%", 
	    	"sPaginationType": "full_numbers",
	    	'aoColumnDefs':[
	    		 { "bSearchable": false, "bVisible": false, "aTargets": [ 0,2,3,4 ] }             
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
	    
	    style_mark_red = OpenLayers.Util.extend({}, OpenLayers.Feature.Vector.style['default']);
	    style_mark_red.graphicWidth = 21;
	    style_mark_red.graphicHeight = 25;
	    style_mark_red.graphicXOffset = -10; 
	    style_mark_red.graphicYOffset = -25;
	    style_mark_red.graphicOpacity = 1;
	    style_mark_red.externalGraphic = "../wp-content/plugins/areago-plugin/img/marker.png";
	    
	    
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
		
		mark.attributes.ID = id;
		mark.attributes.lat = lat;
		mark.attributes.lon = lon;
		
		
		
		layers.markers.addFeatures([mark]); //La a–ado a la capa
		
		return mark;
		
	}
	
	this.addCircleToMap = function (lon, lat, id){

		var markerPosition = new OpenLayers.LonLat(lon,lat);  // Posici—n del punto.
		markerPosition.transform(projections.vector,projections.base); //Cambio a la proyecci—n adecuada.
		
		var point = new OpenLayers.Geometry.Point(markerPosition.lon,markerPosition.lat);
		var circle = new OpenLayers.Geometry.Polygon.createRegularPolygon(point,5,40);
		var fcircle = new OpenLayers.Feature.Vector(circle);
		
		fcircle.attributes.radius=5;
		fcircle.attributes.ID = id;
		fcircle.attributes.type = 'circle';
		
		layers.circles.addFeatures([fcircle]);
		
		return fcircle;
		
	}
	
	this.createNewPoint = function(type){
		
		var _tmpPoint = null;		
		
		switch (type)
		{
			case pointType.PLAY_ONCE:
				
				_tmpPoint = new Areago.Point.PlayOnce(style_mark, style_mark_red, layers, controls, projections, map);
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
		console.log("_inMemoryPoint",_inMemoryPoint);
		console.log("_tmpPoint",_tmpPoint);
		console.log("_points",_points);
		if (_inMemoryPoint != null){
			controls.circleResize.deactivate();
	    	controls.markerDrag.deactivate();
			_inMemoryPoint.disable();
			_inMemoryPoint = null;
		}
		
		_inMemoryPoint = _tmpPoint; 
		
	}// createNewPoint
	
	var prepareDomForPlay_Once = function (){
		
		jQuery('#marker-title').empty();
		jQuery("#jquery_jplayer_1").jPlayer('setMedia',{mp3:""});
		jQuery('.panel_A').show();
		jQuery('#areago-panel_A-marker-info').hide();
		jQuery('#areago-reference').removeAttr('checked');
		jQuery('#areago-reference').button( "refresh");
		jQuery('#areago-save-point').button( "option", "disabled", true);
		jQuery('#areago-point-actions').hide();
        mTable.$('tr.row_selected').removeClass('row_selected');		

	} // prepareDomForPlay
	
	this.exportPointsToDOM = function(){
		var r="";
		
		if (_points.length > 0){
			jQuery.each(_points, function (i, val){
				r = r + serializePoint(val);
			});
		}
	}
	
	var serializePoints = function (){
		
		
		
		switch (t){
			case pointType.PLAY_ONCE:
			case pointType.PLAY_LOOP:
			case pointType.PLAY_UNTILFINISH:
				p.type = t;
				p.radius = point.features[0]._circle.radius;
				p.lat = point.features[0]._marker.lat;
				p.lon = point.features[0]._marker.lon;
				p.file = point.file;
				var u = new OpenLayers.Format.GeoJSONWG();
				p.valores = u.write([point.features[0]._marker], true);
				p.sds = u.createCRSObject(point.features[0]._marker);
				break;		
		};
		
		point.serialized = JSON.stringify(p);
	}
	
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
				
		 if (_inMemoryPoint.initialized){
			//Se supone que tenemos un punto, y no deber’amos, por lo que me lo cargo
			_inMemoryPoint.reset();
		}
		
		var fLon = parseFloat(data[3]);
		var fLat = parseFloat(data[2]);
		
		var markerPosition = new OpenLayers.LonLat(fLon,fLat);  // Posici—n del punto.
		markerPosition.transform(projections.vector,projections.base); //Cambio a la proyecci—n adecuada.

		_inMemoryPoint.addPoint(markerPosition, 5);
				
		_loadAjaxInfo = true;
		
		_save = true;
		
		_inMemoryPoint.postID = data[0];
		
		jQuery('#areago-save-point').button( "option", "disabled", false);
		jQuery('#areago-reference').button( "option", "disabled", false);
		jQuery('#areago-point-actions').show();
		
		jQuery.post(ajaxurl, dataFromMarker, function(response) {			
											
				if(_loadAjaxInfo){					
					updateMarkerDataInDom(response);
					console.log(response.marker);
					_inMemoryPoint.setFile(response.marker.attachments[0].name + ".mp3", response.marker.attachments[0].filePath);					
					_loadAjaxInfo=false;
				}
				
		}, 'json');  // jQuery.post
		
		
		
	}//addPoint
	
	var updateMarkerDataInDom = function (data){
		
		jQuery('#marker-title').empty().append(data.title);    
		jQuery("#jquery_jplayer_1").jPlayer('setMedia',{mp3:data.marker.attachments[0].fileURI});
		jQuery('#marker-radius').empty().append('5');
		jQuery('#areago-panel_A-marker-info').show();
		
	}//updateMarkerDataInDom
	
	
}//Areago


jQuery('document').ready(function(){
	
	var _areago = new Areago();
	_areago.initialize();
	
});


Array.prototype.searchById=function(id)
{
	var position =-1;
  for (i=0;i<this.length;i++){
	  if (this[i].id == id)
		  {
		  	position= i;
		  	i=this.lenght;
		  }	  
  }
  
  return position;

}


OpenLayers.Format.GeoJSONWG = OpenLayers.Class(OpenLayers.Format.GeoJSON,{
	
    /**
     * APIMethod: write
     * Serialize a feature, geometry, array of features into a GeoJSON string.
     *
     * Parameters:
     * obj - {Object} An <OpenLayers.Feature.Vector>, <OpenLayers.Geometry>,
     *     or an array of features.
     * pretty - {Boolean} Structure the output with newlines and indentation.
     *     Default is false.
     *
     * Returns:
     * {String} The GeoJSON string representation of the input geometry,
     *     features, or array of features.
     */
    write: function(obj, pretty) {
        var geojson = {
            "type": null
        };
        if(OpenLayers.Util.isArray(obj)) {
            geojson.type = "FeatureCollection";
            var numFeatures = obj.length;
            geojson.features = new Array(numFeatures);
            for(var i=0; i<numFeatures; ++i) {
                var element = obj[i];
                if(!element instanceof OpenLayers.Feature.Vector) {
                    var msg = "FeatureCollection only supports collections " +
                              "of features: " + element;
                    throw msg;
                }
                if(element.attributes.hasOwnProperty('type')){
                	if (element.attributes.type != 'circle'){
                        geojson.features[i] = this.extract.feature.apply(
                                this, [element]
                            );
                	};
                }else{
                    geojson.features[i] = this.extract.feature.apply(
                            this, [element]
                        );                	
                };
            }
        } else if (obj.CLASS_NAME.indexOf("OpenLayers.Geometry") == 0) {
            geojson = this.extract.geometry.apply(this, [obj]);
        } else if (obj instanceof OpenLayers.Feature.Vector) {
            geojson = this.extract.feature.apply(this, [obj]);
            if(obj.layer && obj.layer.projection) {
                geojson.crs = this.createCRSObject(obj);
            }
        }
        return OpenLayers.Format.JSON.prototype.write.apply(this,
                                                            [geojson, pretty]);
    },
    
    extract: {
    	'feature' : OpenLayers.Format.GeoJSON.prototype.extract.feature,
    	'geometry': OpenLayers.Format.GeoJSON.prototype.extract.geometry,
    	'point':OpenLayers.Format.GeoJSON.prototype.extract.point,
    	'multipoint':OpenLayers.Format.GeoJSON.prototype.extract.multipoint,
    	'linestring': OpenLayers.Format.GeoJSON.prototype.extract.linestring,
    	'multilinestring': OpenLayers.Format.GeoJSON.prototype.extract.multilinestring,
    	'polygon':OpenLayers.Format.GeoJSON.prototype.extract.polygon,
    	'multipolygon':OpenLayers.Format.GeoJSON.prototype.extract.multipolygon,
    	'collection': OpenLayers.Format.GeoJSON.prototype.extract.collection
    },
    
    CLASS_NAME: "OpenLayers.Format.GeoJSONWG" 
	
	
});


Areago.Point = Class.create({
	
	
	features:[],
	style:{},
	style_disabled:{},
	controls:{},
	map: undefined,
	id: null,
	initialized: false,
	enabled: true,
	
	initialize: function(style, style_dis, layers, controls, projections, map){
		this.position=[];
		this.file = "";	
		this.filePath = "";
		this.style = style;
		this.style_disabled = style_dis
		this.controls = controls;
		this.layers = layers;
		this.map = map;
        this.id = OpenLayers.Util.createUniqueID(this.CLASS_NAME + "_"); 
        this.projections = projections;
        this.features = [];
        this.initialized = false;
        this.enabled = true;		
        
	},
	
	reset: function () {
		this.position=[];
		this.file="";
		this.filePath = "";
		
		for (var i=0; i<this.features.length; i++){
			this.layers.circles.removeFeatures([this.features[i].circle]);
			this.layers.markers.removeFeatures([this.features[i].marker]);
		}
		
		this.features = [];
	},
	
	disable: function (){
		if (this.enabled){
			for (var i = 0; i<this.features.length; i++){
				this.features[i].marker.style = this.style_disabled;
				this.layers.markers.drawFeature(this.features[i].marker);
			}
			this.enabled = false;
			this.layers.markers.events.unregister(
					'featuremodified', this, this.markerMove
				);
			this.layers.circles.events.unregister(			
					'featuremodified', this, this.circleResized         
		        );
			
		}
		
		
	},
	
	
	addPoint: function(lonlat, radius){
		
		var point = new OpenLayers.Geometry.Point(lonlat.lon,lonlat.lat);
		var circle = new OpenLayers.Geometry.Polygon.createRegularPolygon(point,radius,40);
		var fcircle = new OpenLayers.Feature.Vector(circle);
		
		this.layers.circles.addFeatures([fcircle]);
		
		var m_point = new OpenLayers.Geometry.Point(lonlat.lon,lonlat.lat);  //Paso a punto		
		var mark = new OpenLayers.Feature.Vector(m_point,null,this.style); //Creo la marca
				
		this.layers.markers.addFeatures([mark]); //La a–ado a la capa
		
		this.features.push({
			marker: mark,
			circle: fcircle
		});
		
		this.position.push({
			lat: lonlat.lat,
			lon: lonlat.lon,
			radius: radius
		});
		
		this.initialized = true;
		
		this.layers.markers.events.register(
				'featuremodified', this, this.markerMove
			);
			this.layers.circles.events.register(			
				'featuremodified', this, this.circleResized         
	        );
		
	},
	
	circleResized: function (event){
		
		var radius = this.getRadius(event.feature);
		var index = this.circleIndex(event.feature);
		this.position[index].radius = radius;        
    	
	},
	
	getRadius: function (circle){
		var area = circle.geometry.getArea();		
        var radius = 0.565352 * Math.sqrt(area);
        radius = Math.round(radius);            
        return radius;
	},
	
	moveCircle: function(x, y, circle){
		var cP = circle.geometry.bounds.getCenterPixel();
		circle.geometry.move(x - cP.x, y - cP.y);
		circle.layer.drawFeature(circle);
	},
	
	markerMove: function(event){
		
		//get the point of the new position
		var point = new OpenLayers.Geometry.Point(event.feature.geometry.x,event.feature.geometry.y);
		var index = this.markerIndex(event.feature);
		
		var circle = this.features[index].circle;
		if (circle){
			this.moveCircle(point.x, point.y, circle); //Chequear			
		}
		var _ll = this.getPositionLatLon(event.feature);
		this.position[index].lat = _ll.y
		this.position[index].lon = _ll.x;
		
	},
	
	getPositionLatLon: function (mark){
		
		var index = this.markerIndex(mark);
		var _m = this.features[index].marker;
		var point = new OpenLayers.Geometry.Point(_m.x,_m.y);
		point.transform(this.projections.base, this.projections.vector);
		var latlon = {
				lon: point.x,
				lat: point.y
		}
		return latlon;
		
	},
		
	markerIndex: function (mark) {

		var position =-1;
		  for (i=0;i<this.features.length;i++){
			  if (this.features[i].marker.ID == mark.ID)
				  {
				  	position= i;
				  	i=this.lenght;
				  }	  
		  }		  
		  return position;

	},
	
	circleIndex: function (circle) {

		var position =-1;
		  for (i=0;i<this.features.length;i++){
			  if (this.features[i].circle.ID == circle.ID)
				  {
				  	position= i;
				  	i=this.lenght;
				  }	  
		  }		  
		  return position;

		
	},

	getPoint: function(index){
		return this.features[index].marker;
	},
	
	getCircle: function(index){
		return this.features[index].circle;
	},
	
	CLASS_NAME: "Areago.Point"
		
});

Areago.Point.PlayOnce = Class.create(Areago.Point, {
	
	type: 0,
	postID: null,
	
	setFile: function (filename, path){
		this.file = filename;
		this.filePath = path;
	},	
	
	exportable: function(){
		var _d = {
				type: "Feature",
				properties: {
					file: this.file,
					filePath: this.filePath
				},
				geometry: {
					type: 'Circle',
					properties: {
					   radius_units: "m"
					},
					radius: this.position[0].radius,
					coordinates: [ this.position[0].lon, this.position[0].lat ]
				}
		
		};
		return _d;
	},

	
	CLASS_NAME: "Areago.Point.PlayOnce"
});


OpenLayers.Geometry.Circle = OpenLayers.Class(OpenLayers.Geometry.Polygon, {
	
	initialize:function (origin, radius){
		
		var t = OpenLayers.Geometry.Polygon.createRegularPolygon(origin, radius, 40);
		OpenLayers.Geometry.Polygon.apply(this, [t.components]);//this.__proto__.__proto__.initialize(t.components);

	},
	
	
	CLASS_NAME: "OpenLayers.Geometry.Polygon"
});


(function($){
	var fnOldSendToEditor;
	$(function(){
		// store original send_to_editor function
		fnOldSendToEditor = window.send_to_editor;
		// add a different send_to_editor function to each thickbox anchor
		var $Box = $('#areago-picture-wrapper');
		if ($Box.length) {
			$Box.find('a.thickbox').each(function(i,el){
				var $A = $(el).click(function(){
					window.send_to_editor = getFnSendToEditor($A.data('type'));
				});
			});
		}
		// hack tb_remove to reset window.send_to_editor
		var fnTmp = window.tb_remove;
		window.tb_remove = function(){
			if (fnOldSendToEditor) window.send_to_editor = fnOldSendToEditor;
			fnTmp();
		};
	});
	function getFnSendToEditor(type){
		return function(fileHTML){
			var oData = JSON.parse(fileHTML);
			console.log(oData);
			$('#'+oData.input).val(oData.id);
			$('#'+oData.preview).empty().append('<img src="' + oData.img_thumb + '" style="width:100%;"/>');
			tb_remove();
		}
	}
})(jQuery);

