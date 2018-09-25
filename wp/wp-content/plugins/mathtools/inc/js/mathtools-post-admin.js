jQuery.noConflict(); jQuery(document).ready(function($){

    var useclass = 'opt-use',
        $uses    = $('.' + useclass);

    $uses.each(function(){
        show_hide_on_check($(this).find('input'));
    });

    $uses.on('click','input',function(){
        show_hide_on_check($(this));
    });

    function show_hide_on_check($input) {

        var type    = $input.attr( 'type' ),
            use     = $input.data( 'use' ),
            radio   = $input.data( 'radio' ),
            name    = $input.attr( 'name'),
            rval    = $( '[name="' + name + '"]:checked').val(),
            $fields = $('.' + use);

        $fields.hide();

        if ( type === 'checkbox' && $input.is(':checked') ) $fields.show();
        if ( type === 'radio' && rval == radio ) $fields.show();
    }

});