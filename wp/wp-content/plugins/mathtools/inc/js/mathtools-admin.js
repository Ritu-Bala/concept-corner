jQuery.noConflict();
jQuery(document).ready(function($){

    var slug       = 'opt-content',
        activetab  = '',
        defaulttab = '#opt-tab-display',
        page       = 'mathtoolsets_options';

    /* SETUP */

    // move metaboxes to correct tabs
    move_metaboxes();

    // when page loads, make tab active
    tab_switch();

    // add metabox functionality
    postboxes.add_postbox_toggles( slug );

    /* ACTIONS */

    // show and hide tab content on tab clicks
    $( '.opt-tab' ).on( 'click', function(e) {
        e.preventDefault();
        tab_switch( $( this ) );
    });

    /* FUNCTIONS */

    /**
     * TAB SWITCH
     * Changes the tab content when tab is clicked.
     *
     * @since 1.0
     * @param $tab
     */
    function tab_switch( $tab ) {

        var id,
            content,
            tab;

        if ( typeof( $tab ) === 'undefined' ) {

            tab = getQ( 'tab' ).length ? getQ( 'tab' ) : defaulttab;
            $tab = $( '#' + tab );
        }

        id = $tab.attr( 'id' );
        content = $tab.data( 'optcontent' );

        // hide all content
        $( '.opt-content' ).hide();

        // show this content
        $( content ).show();

        // remove active class from all tabs
        $( '.opt-tab' ).removeClass( 'nav-tab-active' );

        // add active tab class to this tab
        $( '#' + id ).addClass( 'nav-tab-active' ).blur();

        // update the activetab var
        activetab = id;

        // change the query string
        if ( history.pushState ) {
            var stateObject = { dummy: true };
            var url = window.location.protocol
                + "//"
                + window.location.host
                + window.location.pathname
                + '?page=' + page + '&tab=' + id;
            history.pushState( stateObject, $( document ).find( 'title' ).text(), url );
        }
    }

    /**
     * MOVE METABOXES
     * Moves metaboxes to their proper tabs
     */
    function move_metaboxes() {

        var $metaboxes = $( '.meta-box-sortables' );

        // move metaboxes to correct tabs
        $metaboxes.each( function() {

            var $this = $( this ),
                tab = $this.find( 'table' ).data( 'fasstab' );

            // move the box and remove hidden class
            $this.appendTo( tab )
                .find( '.opt-hidden' )
                .removeClass( 'opt-hidden' )
                .removeClass( 'hide-if-js' )
                .show()
            ;
        });
    }

    /**
     * GETQ
     * Gets the query string
     *
     * @param name
     * @returns {string}
     */
    function getQ( name ) {
        name = name.replace( /[\[]/, "\\[").replace(/[\]]/, "\\]" );
        var regex = new RegExp( "[\\?&]" + name + "=([^&#]*)" ),
            results = regex.exec( location.search );
        return results === null ? "" : decodeURIComponent( results[1].replace( /\+/g, " " ) );
    }

});