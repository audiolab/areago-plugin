

var markerLayer;
var editingMarker = null;
var mPlayer;

jQuery("document").ready(function(){
      
	var map = new OpenLayers.Map('map', { 
            controls: [
                       new OpenLayers.Control.Navigation(),
                       new OpenLayers.Control.PanZoomBar(),
                       new OpenLayers.Control.LayerSwitcher({'ascending':false}),
                       new OpenLayers.Control.Permalink(),
                       new OpenLayers.Control.ScaleLine(),
                       new OpenLayers.Control.Permalink('permalink'),
                       new OpenLayers.Control.MousePosition(),
                       new OpenLayers.Control.OverviewMap(),
                       new OpenLayers.Control.KeyboardDefaults()
                   ]});
	
	
    var gphy = new OpenLayers.Layer.Google(
            "Google Physical",
            {type: G_SATELLITE_MAP,numZoomLevels: 20}          
        );
    
    markerLayer = new OpenLayers.Layer.Markers('Markers');
    
    map.addLayer(gphy);
    map.addLayer(markerLayer);
    map.setCenter(new OpenLayers.LonLat(Areago.lng, Areago.lat), Areago.zoom);    
    
    
    jQuery('#markers-table table tbody tr').dblclick(function(event){
    	//console.log(mTable.fnSettings().aoData);
    	var u = event.currentTarget;

    	jQuery(mTable.fnSettings().aoData).each(function (){
			if (this.nTr == u){
				addPoint(this._aData);
//				console.log(this._aData);  //0 = ID, 2 = lat, 3 = long				
			};

		});
    	
    });//jQuery('#markers-table table tbody tr').dblclick
    
    mTable = jQuery('#markers-table table').dataTable({
    	'bJQueryUI':true,
    	width:"100%", 
    	"sPaginationType": "full_numbers",
    	'aoColumnDefs':[
    		 { "bSearchable": false, "bVisible": false, "aTargets": [ 0,2,3 ] }             
    	]
    	
    });

    
    jQuery( "#addpoint" ).button({
   
        icons: {
            primary: "ui-icon-plus"
        }
    	
    }),

    jQuery( "#removepoint" ).button({
    	   
        icons: {
            primary: "ui-icon-minus"
        }
    	
    });
    
    soundPlayer();
    
});


function soundPlayer(){
	var myPlayer = jQuery("#jquery_jplayer_1"),
	myPlayerData,
	fixFlash_mp4, // Flag: The m4a and m4v Flash player gives some old currentTime values when changed.
	fixFlash_mp4_id, // Timeout ID used with fixFlash_mp4
	ignore_timeupdate, // Flag used with fixFlash_mp4
	options = {
		ready: function (event) {

			// Hide the volume slider on mobile browsers. ie., They have no effect.
			if(event.jPlayer.status.noVolume) {

				// Add a class and then CSS rules deal with it.

				jQuery(".jp-gui").addClass("jp-no-volume");

			}
			// Determine if Flash is being used and the mp4 media type is supplied. BTW, Supplying both mp3 and mp4 is pointless.

			fixFlash_mp4 = event.jPlayer.flash.used && /m4a|m4v/.test(event.jPlayer.options.supplied);

		},

		timeupdate: function(event) {
			if(!ignore_timeupdate) {
				myControl.progress.slider("value", event.jPlayer.status.currentPercentAbsolute);
			}
		},

		volumechange: function(event) {
			if(event.jPlayer.options.muted) {
				myControl.volume.slider("value", 0);
			} else {
				myControl.volume.slider("value", event.jPlayer.options.volume);
			}
		},

		swfPath: "js",
		supplied: "mp3",
		cssSelectorAncestor: "#jp_container_1",
		wmode: "window"
	},

	myControl = {
		progress: jQuery(options.cssSelectorAncestor + " .jp-progress-slider"),
		volume: jQuery(options.cssSelectorAncestor + " .jp-volume-slider")
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

// Create the volume slider control

myControl.volume.slider({
	animate: "fast",
	max: 1,
	range: "min",
	step: 0.01,
	value : jQuery.jPlayer.prototype.options.volume,
	slide: function(event, ui) {
		myPlayer.jPlayer("option", "muted", false);
		myPlayer.jPlayer("option", "volume", ui.value);
	}

});



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

});


}  //soundPlayer


function addPoint(data){
	//0 = ID
	//1 = Titulo
	//2 = Latitud
	//3 = Longitud
	//4 = Autor
	var size = new OpenLayers.Size(21,25);
	var offset = new OpenLayers.Pixel(-(size.w/2), -size.h);
	var icon = new OpenLayers.Icon('http://www.openlayers.org/dev/img/marker.png', size, offset);

	var marker = new OpenLayers.Marker(new OpenLayers.LonLat(data[3],data[2]), icon);
	marker.ID = data[0];
	markerLayer.addMarker(marker);
	
	var datafrommarker = {
			action: 'areago_get_marker',
			id: marker.ID,			
		};


	jQuery.post(ajaxurl, datafrommarker, function(response) {
			alert('Got this from the server: ' + response);
			console.log(response);
			jQuery("#jquery_jplayer_1").jPlayer('setMedia',{mp3:response.marker.attachments[0].fileURI});
			
	}, 'json');
	
    marker.events.register('mousedown', marker, function(evt) { 
    	console.log(this); 
    	OpenLayers.Event.stop(evt);
    	if (editingMarker){
    		editingMarker.setUrl('http://www.openlayers.org/dev/img/marker.png');
    	}
    	this.setUrl('http://www.openlayers.org/dev/img/marker-green.png');    	
    	editingMarker=this;
    	
    	jQuery('#marker-title').empty().append("dhfsf");
    	
    	
    });
}