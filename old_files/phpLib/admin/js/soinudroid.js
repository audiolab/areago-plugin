
var marcadores_json;
var selectedMarkers=[];

var actionWalks="save";
var editedWalk=-1;
var selectedWalk=-1;

function sm_marker_ob(marker, value, circulo, titulo){
    this.marker = marker;
    this.value=value;
    this.circulo=circulo;
    this.titulo=titulo;
}


jQuery("document").ready(function(){
      
    loadMapMarkers();
    prepareForms();
    tableMarkers();
    tableWalks();
  
});

function tableMarkers(){
    jQuery("#marcadores").jqGrid({
        datatype:"local",
        autowidth: true,
        altRows:true,
        colNames:['ID', 'Lat', 'Long', 'Radio', 'Titulo'],
        colModel:[
        {
            name:'post_id',
            index:'post_id', 
            width:35
        },

        {
            name:'lat',
            index:'lat', 
            width:50
        },

        {
            name:'lng',
            index:'lng', 
            width:50
        },

        {
            name:'radio',
            index:'radio', 
            width:50
        },

        {
            name:'title',
            index:'title', 
            width:300
        },
        ]                     
    });
}

function tableWalks(){
    
    jQuery("#paseos").jqGrid({
        url: ajax_url + "?action=list_walks",
        datatype:"json",
        autowidth: true,
        altRows:true,
        colNames:['ID', 'Name', 'Description', 'Markers'],
        colModel:[
        {
            name:'id',
            index:'id', 
            width:35
        },

        {
            name:'name',
            index:'name', 
            width:150
        },

        {
            name:'description',
            index:'description', 
            width:300
        },

        {
            name:'markers',
            index:'markers', 
            width:50
        }           
        ],
        viewrecords: true,
        toolbar: [true,"bottom"],
        onSelectRow: function(rowid, status){
            jQuery("#walks-toolbar a").addClass("enabled");
            selectedWalk=rowid;
        }
       
    });

    jQuery("#t_paseos").append(jQuery("#walks-toolbar"));
    walksToolbarActions();
}

function walksToolbarActions(){
    
    jQuery("#icon-edit-walk").click(function(){
        var active=jQuery(this).hasClass("enabled");
        if (active && selectedWalk!=-1){
            loadWalk(selectedWalk);
        }
    });
    
    jQuery("#icon-remove").click(function(){
        var active=jQuery(this).hasClass("enabled");
        if (active && selectedWalk!=-1){
            removeWalk(selectedWalk);
        }
    });
    
}

function removeWalk(id){
     
     jQuery("#paseos").jqGrid('delRowData',id);
    
    url=ajax_url + "?action=removeWalk&id=" + id;
    jQuery.getJSON(url,function(data){               
        jQuery("#updated").append(data);
       
    });
}

function loadWalk(id){
    url=ajax_url + "?action=loadWalk&id=" + id;
    jQuery.getJSON(url,function(data){               
        loadWalkData(data);
    });
}
function loadWalkData(data){
    
    jQuery("#walk_name").val(data.name);
    jQuery("#walk_description").val(data.description);
    selectedMarkers=[];
    var filas= data.rows;
    var longitud=filas.length;
    for (var i = 0; i < longitud; i++) {
        var marca=data.rows[i].marker;
        var id=marca.post_id;
        jQuery("#marcadores").addRowData(id,marca,"last");
        selectedMarkers.push(id);
                
    }
    actionWalks="edit";           
    editedWalk=data.id;
    updateForms();
}

function updateForms(){
    
    var url_e =ajax_url + "?action=createSoundWalk";
    
    if (actionWalks="edit"){
        url_e=ajax_url + "?action=updateSoundWalk&id=" + editedWalk;
    }
    
    var options = {         
        beforeSubmit:  showRequest,  // pre-submit callback 
        success:       showResponse,  // post-submit callback 
        target:        '#updated',
        url:  url_e     
    }; 
    jQuery("#sound_walk_form").ajaxForm(options);
    
}

function prepareForms(){
    
    var options = { 
        
        beforeSubmit:  showRequest,  // pre-submit callback 
        success:       showResponse,  // post-submit callback 
        target:        '#updated',
        url:  ajax_url + "?action=createSoundWalk"     

    }; 
    jQuery("#sound_walk_form").ajaxForm(options);
    
}


// pre-submit callback 
function showRequest(formData, jqForm, options) { 
    
    
    formData[3].value=selectedMarkers.toString();
    return true; 
} 
 
// post-submit callback 
function showResponse(responseText, statusText, xhr, $form)  { 
    
} 


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
                clickable: true,
                draggable: true                
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
            google.maps.event.addListenerOnce(circulo, 'click', resizeCircle)
            
            infowindow = new google.maps.InfoWindow({
                content: document.getElementById("contenido")
            });
            
            marker[i]=new sm_marker_ob(marcador,data[i].post_id,circulo,data[i].post_title);
            
            
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

    var ubicacion = ajax_url + '?action=loadpost&post=' + id;
	
    jQuery.ajax({
        url: ubicacion,
        cache: false,
        dataType:'html',
        data: 'postid=' + id,
        success: function(html) {
            var imagen='<div class="boton-add"><a href="#" rel="' + id +'"><img src="' + path_url + '/phpLib/admin/images/add.png"></img> Add marker to sound walk</a></div>';
            html=html + imagen;
            infowindow.setContent(html);	
            jQuery(".boton-add").click(function(){
                addMarcadorTabla(jQuery("a",this).attr("rel"));                 
            });
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


function addMarcadorTabla(id){    
   
    var previousAdded=searchMarkerOnSelectedArray(id);
    if (previousAdded==false){
        var indice=searchMarkerInfoByID(id);
        jQuery("#marcadores").addRowData(id,marcadores_json[indice],"last");
        selectedMarkers.push(id);
    }

}

function searchMarkerOnSelectedArray(id){
    var index=selectedMarkers.indexOf(id);
    if (index==-1){
        return false;
    }
    return true;
}

function searchMarkerInfoByID(id){
    var index=-1;
    for(var i=0;i<marcadores_json.length;i++){              
        if (marcadores_json[i].post_id==id){
            index=i;
        }
    }
    return index;
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


function loadMapMarkers(){
    url=ajax_url + "?action=allmarkers";
    jQuery.getJSON(url,function(data){       
        marcadores_json=data;
        if (data.length){
            renewMarkers(data);
        }        
    });
}
