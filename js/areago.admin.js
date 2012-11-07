

    

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
    map.addLayer(gphy);
    console.log(Number(Areago.lng));
    console.log(Number(Areago.zoom));
    map.setCenter(new OpenLayers.LonLat(Areago.lng, Areago.lat), Areago.zoom);    
    
    
    
    mTable = jQuery('#markers-table table').dataTable({
    	bJQueryUI:true,
    	width:'auto'
    });
    
    jQuery( "#markers-table" ).dialog({
    	autoOpen: false,
    	width:700
    });
    
    
    
    jQuery( "#addpoint" ).button({
   
        icons: {
            primary: "ui-icon-plus"
        }
    	
    }).click(function() {
        jQuery( "#markers-table" ).dialog( "open" );
        return false;
    });

    jQuery( "#removepoint" ).button({
    	   
        icons: {
            primary: "ui-icon-minus"
        }
    	
    });
    
});
