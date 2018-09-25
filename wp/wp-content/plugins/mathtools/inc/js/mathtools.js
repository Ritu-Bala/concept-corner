jQuery(document).ready(function($) {
    
    var isMobile = false,
        subindicator = 'cc-has-child',
        ccClickStart = isMobile ? 'touchstart click' : 'click',
        $mt        = $('.mathtools'),
        $grid      = $('.mathtools-grid-item'),
        $gridmenus = $grid.find('.dropdown-menu'),		
        $menu      = false,
        $parent    = false;

    $mt.on(ccClickStart, function( e ) {
        if ( ! $(this).parent().hasClass(subindicator) ) {			
			fbox( $(this) );			
            e.preventDefault();
            e.stopPropagation();
            hidemenus( false, false );
            
        }
    });
	
    // make grid items hot
    if ( $gridmenus.length ) {
        // hide menus if outside of target
        $(document).on(ccClickStart, function(e) {
            if ( ! $parent || ( ! $parent.is( e.target ) && $parent.has( e.target ).length === 0 ) ) {
                hidemenus( false, false );
            }
        });

        // add apparent rollover state
        $grid.css( 'cursor', 'pointer' );

        // show menu on click, or go directly to tool if no options
        $grid.on(ccClickStart, function(e) {
            var $this = $(this),
                $thismenu = $this.find('.dropdown-menu');
            hidemenus( $thismenu, $this );
            $thismenu.length ? $menu.show() : fbox( $this );
        });
    }



    function hidemenus( menu, parent ) {
        $menu = menu;
        $parent = parent;
        $gridmenus.hide();
    }

    function fbox( $item ) {
        var $this = $item,
            x = $this.data( 'dimx'),
            y = $this.data( 'dimy'),
            href = typeof( $this.attr('href') ) !== 'undefined' ? $this.attr('href') : $this.data( 'basetool' );

        $.fancybox.open({
            type: 'iframe',
            maxWidth	: x,
            maxHeight	: y,
            fitToView	: false,
            width		: x,
            height		: y,
            autoSize	: false,
            closeClick	: false,
            openEffect	: 'elastic',
            closeEffect	: 'elastic',
            padding		: '0',
            wrapCSS		: 'mtwrap',
            scrolling   : 'hidden',
            helpers : {
                title : {
                    type : 'inside'
                },
                overlay: {
                    locked: true
                }
            },
            href: href
        });
    }
});