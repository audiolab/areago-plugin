var moveListener;
var moveCircleListener;

var circulo_mueve;

function resizeCircle ( event ){
                  circulo_mueve=this;
	moveListener = google.maps.event.addListener(map, 'mousemove', moveCircle);
	moveCircleListener = google.maps.event.addListener(circulo_mueve, 'mousemove', moveCircle);
	google.maps.event.addListenerOnce(map, 'click', upCircle);
	google.maps.event.addListenerOnce(circulo_mueve, 'click', upCircle);
	map.setOptions({draggable:false});
        
}

function moveCircle( event ){
	var distance = google.maps.geometry.spherical.computeDistanceBetween(circulo_mueve.getCenter(),event.latLng);
	circulo_mueve.setRadius(distance);
	jQuery("#radius").val(distance);
}

function upCircle (event){
	google.maps.event.removeListener(moveListener);
	google.maps.event.removeListener(moveCircleListener);
	map.setOptions({draggable:true});
	google.maps.event.addListenerOnce(circulo_mueve, 'click', resizeCircle);
}