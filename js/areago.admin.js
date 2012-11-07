

var markerLayer;

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
    
    jQuery('#markers-table table tbody tr').dblclick(function(event){
    	//console.log(mTable.fnSettings().aoData);
    	var u = event.currentTarget;
    	console.log("ndsf");
    	jQuery(mTable.fnSettings().aoData).each(function (){
			if (this.nTr == u){
				addPoint(this._aData);
				console.log(this._aData);  //0 = ID, 2 = lat, 3 = long				
			};

		});
    	
    });
    
});


function addPoint(data){
	//0 = ID
	//1 = Titulo
	//2 = Latitud
	//3 = Longitud
	//4 = Autor
	var size = new OpenLayers.Size(21,25);
	var offset = new OpenLayers.Pixel(-(size.w/2), -size.h);
	var icon = new OpenLayers.Icon('http://www.openlayers.org/dev/img/marker.png', size, offset);
	markerLayer.addMarker(new OpenLayers.Marker(new OpenLayers.LonLat(data[3],data[2])), icon);
	
}