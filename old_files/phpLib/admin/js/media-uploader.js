function insertToPost(mediaID){

    jQuery('#media-item-' + mediaID + ' a.toggle').siblings('.slidetoggle').slideToggle(150, function(){
        var o = jQuery(this).offset();
        window.scrollTo(0, o.top-36);
        jQuery(this).parent().children('.toggle').toggle();
        jQuery(this).siblings('a.toggle').focus();
        var h=jQuery("#mediaID").val();
        jQuery('#media-item-' + h).toggleClass("selectedMediaItem");
        jQuery("#mediaID").val(mediaID);
        jQuery('#media-item-' + mediaID).toggleClass("selectedMediaItem");
        return false;
    });
}

function onMapClick(event){
    marker.setPosition(event.latLng);
    jQuery("#posLat").val(event.latLng.lat());
    jQuery("#posLong").val(event.latLng.lng());
    
}


jQuery(function($){
    var grabacion=jQuery("#mediaID").val();
	jQuery('#media-item-' + grabacion).toggleClass("selectedMediaItem");

});



