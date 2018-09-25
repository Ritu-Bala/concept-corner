jQuery(document).ready(function ($) {

    // configurable
    var qvar = 'rndr',
        qval = 'cache',
        go = true,
        alreadyDone = 'Cached on',
        site = 'conceptcorner.com',
        cache = [
            'explore'
        ];

    // other vars
    var z = getQVar(qvar),
        last = document.body.lastChild.data;

    // if the last node is a comment saying this page was cached, close tab
    if ( go && last.indexOf( alreadyDone ) >= 0 && z == qval ) {
        close();
    }

    prcde( go );


    function prcde( a ) {

        if ( ! a ) return;

        var $links = $('body').find( 'a' );

        // cycle through links, delay each by five seconds to give a bit of breathing room to server
        $links.each( function() {

            var href = $(this).attr('href'),
                openlink = false;

            // is this an absolute link, relative, or in-site link?
            if ( href.indexOf( 'http' ) < 0 || href.indexOf( site ) > 0 ) {
                $.each(cache, function(i,v) {
                    if ( href.indexOf( v ) >= 0 ) {
                        openlink = true;
                    }
                });
            }

            // delay open for five seconds
            if ( openlink ) {
                setTimeout( open_page, 3000);
            }


        });

        // close self five seconds after final open
        setTimeout( close, 5000);
    }

    function open_page( href ) {
        window.open( href + '?' + qvar + '=' + qval, '_blank');
    }

    function getQVar(variable) {
        var query = window.location.search.substring(1),
            vars = query.split("&"),
            pair,
            i;
        for ( i = 0; i < vars.length; i++ ) {
            pair = vars[i].split("=");
            if ( pair[0] == variable ){ return pair[1]; }
        }
        return(false);
    }

});