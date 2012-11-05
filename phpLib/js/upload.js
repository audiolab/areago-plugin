
var geocoder;

var uploadingFile=false;
var sendingForm=false;
var uploadedFile=false;
var captchaVerificado=false;
var captchaCorrecto=false;
var contenidoCorrecto=true;
var gbounds;

var apendices=new Array(3);
apendices[0]="eu";
apendices[1]="en";
apendices[2]="es";

var contenido=new multiLanguageContent(apendices);

var soinuparams=new Object();

jQuery("document").ready(function(){	
    jQuery(".etiqueta").inFieldLabels();
    geocoder = new google.maps.Geocoder();
    var sw=new google.maps.LatLng(41.7631174470059,-4.713134765625);
    var ne=new google.maps.LatLng(43.73538317799622,-0.60150146484375);
    gbounds=new google.maps.LatLngBounds(sw,ne);
    jQuery("#tabs").tabs();
    jQuery("#fecha").datepicker({
        showOn: 'button',
        buttonImage: url_soinumapa + '/phpLib/images/calendar.gif',
        buttonImageOnly: true,
        dateFormat: 'dd/mm/yy'
    });
	
    jQuery('#direccion').geo_autocomplete(new google.maps.Geocoder, {
        mapkey: 'ABQIAAAAbnvDoAoYOSW2iqoXiGTpYBT2yXp_ZAY8_ufC3CFXhHIE1NvwkxQNumU68AwGqjbSNF9YO8NokKst8w',
        selectFirst: false,
        minChars: 3,
        cacheLength: 50,
        width: 300,
        scroll: true,
        bounds: gbounds,
        scrollHeight: 330
    }).result(function(_event, _data) {
        if (_data) map.fitBounds(_data.geometry.viewport);
    });
});

function onMapClick(event){
    marker.setPosition(event.latLng);
    jQuery("#posLat").val(event.latLng.lat());
    jQuery("#posLong").val(event.latLng.lng());
}


function codeAddress() {
    var address = document.getElementById("direccion").value;
    if (geocoder) {
        geocoder.geocode( { 'address': address, 'region':'ES'}, function(results, status) {
            if (status == google.maps.GeocoderStatus.OK) {
                map.setCenter(results[0].geometry.location);
                map.setZoom(12);
                marker.setPosition(results[0].geometry.location);
            } else {
                alert("Error al buscar la dirección");
            }
        });
    }
}


function multiLanguageContent(apendices){
    this.apendices=apendices;
    this.textos=new Array();
	
    this.plantillas=new Array();

    this.loadText = function loadText(){

        var t=new Array(4);
        t[0]='Egilea: ';
        t[1]="Iraupena: ";
        t[2]="Data: ";
        t[3]="Azalpena: ";
        this.plantillas.push(t);
        t=new Array(4);
        t[0]='Author: ';
        t[1]="Length: ";
        t[2]="Date: ";
        t[3]="Description: ";
        this.plantillas.push(t);
        t=new Array(4);
        t[0]='Autor: ';
        t[1]="Duración: ";
        t[2]="Fecha: ";
        t[3]="Descripción: ";
        this.plantillas.push(t);
        var longitud = this.apendices.length;
        for (i = 0; i < longitud; i++)
        {
            this.appendiceText(this.apendices[i],i);
        }
        this.appendToParams();
    };
    
    this.check=function check(){
    	var longitud=this.apendices.length;
    	var correcto=false;
        var t="";
    	for (i=0;i<longitud;i++){
            t=jQuery("#titulo_" + this.apendices[i]).val();
            if (t!=""){
                correcto=true;
            }
    	}
    	return correcto;
    };
    
    this.appendToParams= function appendToParams(){
    	var longitud=this.apendices.length;
    	var titulo="";
    	var contenido="";
    	for (i=0;i<longitud;i++){
            titulo=titulo + "<!--:" + this.apendices[i] + "-->" + this.textos[i][0] + "<!--:-->";
            contenido=contenido + "<!--:" + this.apendices[i] + "-->" + this.textos[i][1] + "<!--:-->";
    	}
    	soinuparams.titulo=titulo;
    	soinuparams.contenido=contenido;
    }
    
    
    this.appendiceText = function appendiceText(apendice, indice){
        var titulo=jQuery("#titulo_" + apendice).val();
        var descripcion=jQuery("#descripcion_" + apendice).val();
	var fecha=jQuery("#fecha").val();
	var autor=jQuery("#autor").val();
        var duracion=jQuery("#duracion").val();
        var t=new Array(2);
        t[0]=titulo;
        t[1]=this.plantillas[i][0] + autor + "<br/>" + this.plantillas[i][1] + duracion + "<br>" + this.plantillas[i][2] + fecha + "<br/>" + this.plantillas[i][3] + descripcion;
        this.textos.push(t);
    };

	
	
}


jQuery(document).ready(function(){
    jQuery("#submit").click(function(){
		
        //We have to check that all is ok!
		
        var errores=new Array(7);
        var fileURL="";
        var fileDir="";
        var duracion="";
        var filename="";
		
        contenidoCorrecto=true;
		
        soinuparams=new Object();
		
        if (uploadingFile==false && uploadedFile==true){
            fileURL=jQuery("#fileURL").val();
            fileDir=jQuery("#fileDir").val();
            duracion=jQuery("#duracion").val();
            filename=jQuery("#fileName").val();
        }
		
        var posLat=jQuery("#posLat").val();
        var posLong=jQuery("#posLong").val();
				
        soinuparams.posLat=posLat;
        soinuparams.posLong=posLong;
		

	//	contenido.loadText();
		
        var autor=jQuery("#autor").val();
        var fecha=jQuery("#fecha").val();
		
        var categoria=jQuery("#categoria").val();
        var captcha=jQuery("#captcha").val();
		
        if (uploadedFile==false && uploadingFile==false){
            errores[0]=true;
            contenidoCorrecto=false;
        }else if (uploadedFile==true){
            errores[0]=false;
            soinuparams.fileURL=fileURL;
            soinuparams.fileDir=fileDir;
            soinuparams.fileName=filename;
            soinuparams.duracion=duracion;
        }else{
            errores[0]=false;
        }
		
        var contentOK=contenido.check();
		
        if (contentOK==false){
            errores[2]=true;
            contenidoCorrecto=false;
        }else{
            errores[2]=false;
            //contenido.appendToParams();
        }
		
        if (autor==""){
            errores[3]=true;
            contenidoCorrecto=false;
        }else{
            errores[3]=false;
            soinuparams.autor=autor;
        }
		
        if(fecha==""){
            errores[4]=true;
            contenidoCorrecto=false;
        }else{
            errores[4]=false;
            soinuparams.fecha=fecha;
        }
		
        if(categoria==""){
            errores[5]=true;
            contenidoCorrecto=false;
        }else{
            errores[5]=false;
            soinuparams.categoria=categoria;
        }
		
        if (captcha==""){
            errores[6]=true;
            contenidoCorrecto=false
        }else{
		
            //Verify the captcha
            var dat="captcha=" + captcha;
            jQuery.post(url_soinumapa + "/phpLib/verify_captcha.php",dat, function(data){
                captchaVerificado=true;
                if (data=="Correcto"){
                    captchaCorrecto=true
                    jQuery(".captchaImg").attr("src", url_soinumapa + "/phpLib/captcha/securimage_show.php")
                    if(contenidoCorrecto){
                        jQuery("#error_7").slideUp();						   
                        saveData();
                    }
                }else{
                    jQuery("#error_7").slideDown();
                    contenidoCorrecto=false;
                    captchaCorrecto=false;
                    jQuery(".captchaImg").attr("src", url_soinumapa + "/phpLib/captcha/securimage_show.php")
                }
            });
        }
		
		
        for (i=0;i<7;i++){
            if (errores[i]==true)
            {
                jQuery("#error_"+ (i+1)).slideDown();
            }else{
                jQuery("#error_"+ (i+1)).slideUp();
            }
			
        }
		
        if (contenidoCorrecto){
            saveData();
        }
        return false;
    });
});


function saveData(){
	
    if (contenidoCorrecto && captchaVerificado && captchaCorrecto && uploadedFile){
        //Guardar
        jQuery("#savingContent").slideDown();
        contenido.loadText();
            var p= jQuery.param(soinuparams);
        jQuery.post(url_soinumapa + "/phpLib/save-post.php",p,function(data){
            jQuery("#savingContent").slideUp();
            if (data=="correcto"){
                jQuery.jGrowl("¡Grabación correcta!");
            } else {
                jQuery.jGrowl("Error");
            };
        });
    }else if (contenidoCorrecto && captchaVerificado && captchaCorrecto && uploadingFile){
        //muestro el panel, pero no hago nada
        sendingForm=true;
        jQuery("#savingContent").slideDown();
    }

}