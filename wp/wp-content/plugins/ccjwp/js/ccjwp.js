jQuery(document).ready(function($) {
    
    $( '.cc-jwplayer' ).each( function() {
        var $this = $(this),
            id = $this.attr('id'),
            local = window[id];
        jwplayer( id ).setup( local );
    });
    
});