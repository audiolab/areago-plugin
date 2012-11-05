var infoAbierto=false;
var infoPanel;
var marker=new Array(); //Array of google markers controls.
var selectedMarker="";
var infowindow;

var parado=false;
var posicion=0;
var buffering=false;

var playerOptions="";


function sm_marker_ob(marker, value){
    this.marker = marker;
    this.value=value;
}


function renewMarkers(data){

    if (data.length){
        remove_markers();
        jQuery("#hiddenPosts").empty();
        jQuery("#postEntryBox").empty();
        jQuery("#hiddenPosts").append("<div id='contenido'><p>Cargando...</p></div>");

        for (var i=0; i<data.length;i++){
            var p=new google.maps.LatLng(data[i].lat,data[i].lng);
            var marcador = new google.maps.Marker({
                position: p,
                map: map,
                title: data[i].post_title,
                clickable: true
            });
            var circulo = new google.maps.Circle({
                center: p,
                map:map,
                clickable:true,
                fillOpacity:0.4,
                fillColor:"#00AAFF",
                strokeColor:"#00AAFF",
                strokeOpacity:0.9,
                strokeWeight:1,
                radius: parseFloat(data[i].radio)
            });
            circulo.bindTo("center",marcador,"position");

            
            infowindow = new google.maps.InfoWindow({
                content: document.getElementById("contenido")
            });
            
            marker[i]=new sm_marker_ob(marcador,data[i].post_id);
            
            
            google.maps.event.addListener(infowindow, "domready",function(){

                var archivo=jQuery("#sound_download").attr("href");
                playerOptions={
                    ready: function () {
                        this.element.jPlayer("setFile", archivo).jPlayer("play");
                        this.element.jPlayer("onProgressChange", function(lp,ppr,ppa,pt,tt){

                            var incre=pt-posicion;
                            posicion=pt;
                            if ((incre==0 && parado==false) || (pt==0 && parado==false)){
                                buffering=true;
                            }else{
                                buffering=false;
                            }

                            if(buffering){
                                jQuery(".jplayer_buffer").show();
                            }else{
                                jQuery(".jplayer_buffer").hide();
                            }

                        });
                    },
                    swfPath: path_url + "/js/jplayer/",
                    volume: 100,
                    oggSupport: false,
                    preload: 'auto',
                    nativeSupport: true,
                    width: 45,
                    heigth:15
                };
                parado=false;
                posicion=0;
                buffering=true;
                jQuery("#jquery_jplayer").jPlayer(playerOptions);
                jQuery("#jplayer_pause").click(function(){
                    parado=true;
                });
                jQuery("#jplayer_stop").click(function(){
                    parado=true;
                });

            })   // End DOMREADY listener          
            
            
            google.maps.event.addListener(infowindow,"closeclick",function(){
                jQuery("#jquery_jplayer").jPlayer("clearFile");
                jQuery("#jquery_jplayer").jPlayer("onProgressChange", function(lp,ppr,ppa,pt,tt){});
            }) // End CLOSE CLICK listener
            
            
            google.maps.event.addListener(marcador, 'click', function() {

				if (infoAbierto){
                    jQuery("#jquery_jplayer").jPlayer("clearFile");
                    jQuery("#jquery_jplayer").jPlayer("onProgressChange", function(lp,ppr,ppa,pt,tt){});
                    infowindow.close();
                }
                
                infoAbierto=false;
                selectedMarker=this;
                jQuery("#postEntryBox").empty();
                jQuery("#contenido").remove();
                jQuery("#hiddenPosts").append("<div id='contenido'><p>Cargando...</p></div>");

                infowindow.setContent(document.getElementById("contenido"));
                
                var id=search_marker();    
                     
                infowindow.open(map,this);
                
                infoAbierto=true;
                
				cambiarPosicion();  

          		loadAjaxPanel(id);                                                          

            }); // End CLICK listener
        } // End for
    }  // End if
} //End function

function loadAjaxPanel(id){

	var ubicacion = path_url + '/post.php';
	
	jQuery.ajax({
	    url: ubicacion,
	    cache: false,
	    dataType:'html',
	    data: 'postid=' + id,
	    success: function(html) {
	    	infowindow.setContent(html);		                
	    }
	});
	
}

function search_marker(){
	var id=0;
	for (var j=0; j<marker.length;j++){
	    if (marker[j].marker==selectedMarker){
	        id=marker[j].value;
	        break;
	    }
	}
	return id;
}

function cambiarPosicion(){
	bordes=map.getBounds();
	norte=bordes.getNorthEast().lat();
	sur=bordes.getSouthWest().lat();
	diferencia=norte-sur;
	increPosicion=diferencia/4;
	posicion=selectedMarker.getPosition();
	nuevocentro=new google.maps.LatLng(posicion.lat() + increPosicion, posicion.lng());
	map.setCenter(nuevocentro);
}

function remove_markers(){
    if (infoAbierto){
        infoPanel.close();
    }
    for ( var i=0; i<marker.length;i=i+1){
        marker[i].marker.setMap(null);
    }
    marker=new Array();
}



