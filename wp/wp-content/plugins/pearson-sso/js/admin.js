(function ( $ ) {
	"use strict";

	$(function () {
		
		var $body       = $( 'body' ),
			checker     = 'input.psso-showhide-check',
            checkall    = '#psso-showhide-all',
            detailall   = 'a.psso-mess-group',
            detailed    = 'a.psso-log-more-button',
            $optform    = $('#cmo-options-form'),
            $opttrig    = $optform.find('.psso-trigger').find('input'),
			$longPath   = $( '#sso-long-url' ),
			originalUrl = $longPath.html(),
            $tab        = $('.cmb_options_page .nav-tab'),
			$result     = $( '#ssoresult' ),
            $ssocopy    = $('#sso-copy-url'),
            $ssotest    = $('#sso-test'),
            $logfilter  = $('.psso-log-filter');

        console.log('SSO');

		// intercept clicks on cmb form tabs
        $tab.on('click', function(e) {
			// don't go anywhere
			e.preventDefault();
			// get the tab this should be showing
			var showtab = $(this).data('tab');
			// hide all metaboxes, remove active class from tabs
			$('.cmb-tab').removeClass('cmb-tab-active');
			$('.nav-tab').removeClass('nav-tab-active');
			// show this mataboxes content, highlight correct tab
			$('#' + showtab).addClass('cmb-tab-active');
			$(this).addClass('nav-tab-active');
			// change query string so submitted form can come back to right tab
			if (history.pushState) {
				var page =  getPssoQueryVariable('page');
				var newurl = window.location.protocol + "//" + window.location.host + window.location.pathname
					+ '?page=' + page + '&tab=' + showtab;
				window.history.pushState({path:newurl},'',newurl);
			}
			// remove focus on tab
			$(this).blur()
		});
        
		// capture form input on test page to undisable disabled items
        $ssotest.on('submit', function(e) {
			e.preventDefault();
			// send ajax request and display result
			var payload = $('#ssoaddress').val(),
				$ssor = $('#ssoresult');
			if (payload.length) {
				$ssor.html('Working...');
				$.ajax({
					url: ajaxurl,
					type: 'POST',
					cache: false,
					data: {
						'action'  : 'psso',
						'psso_test' : payload
					},
					success: function(data){
						$ssor.html('');
						$ssor.append( $.parseHTML( data ) );
						grouper();
					},
					error: function(a,b,c) {
						console.log(a);
						console.log(b);
						console.log(c);
					}
				});
			}
		});

        // undisable items on options form submit
        $optform.on( 'submit', function(e) {
            var $disabled = $(this).find('input:disabled');
            $disabled.prop( "disabled", false );
        });

		// capture copy link
        $ssocopy.on('click', function () {
			var noBreaks = $(this).data('copy');
			var currentUrl = $longPath.html();

			if ( originalUrl != currentUrl ) {
				$longPath.html( originalUrl );
				$(this).text('Remove Line Breaks (for copying)');
			} else {
				$longPath.html( noBreaks );
				$(this).text('Add Line Breaks');
		}
		});
        
        // show/hide all log details
        $body.on( 'click', detailall, function(e) {
            e.preventDefault();
            var what = $(this).attr('href') == '#all' ? 'Details' : 'Hide',
                $details = $( detailed );
            $details.each( function () {
                details( $(this), what );
            });
        });

		// Log details, show more
		$body.on( 'click', detailed, function(e) {
			e.preventDefault();
			details( $(this) );
		});

		// arrival show/hide
		$body.on( 'click', '.psso-arrival-button', function(e) {
			e.preventDefault();
			details( $(this) );
		});
		
		// Log details, show/hide specific classes
		$body.on( 'click', checker, function() {
			optchk( $(this) );
		});

        // show hide all
        $body.on( 'click', checkall, function() {
            var $checkers = $( checker );
            // check or uncheck all boxes
            $checkers.prop( 'checked', $(this).prop( "checked" ) );
            // show/hide
            $checkers.each( function() {
                optchk( $(this) )
            });
        });

        // expand and collapse the tree on log pages
		$body.on( 'click', '.gind-trig', function(e) {
			e.preventDefault();
			var $this = $(this),
				op = '<span class="dashicons dashicons-arrow-right-alt2"></span>',
				cl = '<span class="dashicons dashicons-arrow-down-alt2"></span>',
				targ = '.' + $this.data('target'),
				$not = $this.closest( targ );
			if ( $this.hasClass('gind-open') ) {
				$this.removeClass('gind-open').addClass('gind-closed');
				$this.html( op );
				$(targ).not( $not ).addClass('psso-collapse-hidden');
			} else {
				$this.removeClass('gind-closed').addClass('gind-open');
				$this.html( cl );
				$(targ).not( $not ).removeClass('psso-collapse-hidden');
			}
		});

		// filter SSO results
        $logfilter.on('change', function(){
			var f = [ 'sso_via', 'sso_new', 'sso_status' ],
				fill = $(this).val(),
				ky = $(this).data('key'),
				q = fill == '' ? '' : '&' + ky + '=' + fill,
				pg = getPssoQueryVariable('page'),
				newurl;
			$.each( f, function(k,v) {
				var kk = v == ky ? false : getPssoQueryVariable( v );
				if ( kk !== false ) {
					q += '&' + v + '=' + kk; 
				}
			});
			newurl = window.location.protocol + "//" + window.location.host + window.location.pathname 
				+ '?page=' + pg + q;
			document.location.href = newurl;
		});

        // disable/enable affected fields
        $opttrig.on('change', function(e) {
            disability( $(this) );
        });

        // add group tree on pages which display debugging information
        if ( $result.length ) {
            grouper();
        }

        // initial diabled/enable
        if ( $opttrig.length ) {
            $opttrig.each( function() {
                disability( $(this) );
            });
        }

        /**
         * Shows/Hides details
         * 
         * @param $obj
         * @param dothis
         */
        function details( $obj, dothis ) {
            var $this = $obj,
                $target = $( $this.attr('href') ),
                txt = $this.text(),
				hidetxt = $this.data('hide'),
				showtxt = $this.data('show'),
                newtxt = hidetxt,
                ok = ! ( ( typeof( dothis ) !== 'undefined' ) && txt != dothis );
            if ( ok ) {
                $target.toggle();
                if ( txt == hidetxt ) newtxt = showtxt;
                $this.text( newtxt );
            }
        }
        
        /**
         * Checks state of "hide common calls" checkboxes on logs
         * 
         * @param $obj
         */
		function optchk( $obj ) {
			var $this = $obj,
				targ = $this.data('what'),
				$targs = $('.' + targ);
			if ( $this.is(':checked') ) {
				$targs.addClass('psso-hidden');
			} else {
				$targs.removeClass('psso-hidden');
                $(checkall).prop( 'checked', false );
			}
		}

        /**
         * Adds tree structure to logs
         */
		function grouper() {
			var $checkers,
				$mule = $('#pssomule');
			if ( $mule.length ) {
				var groups = $mule.data('groups').split(',');
				$.each( groups, function(i,v) {
					var $rows = $('.' + v).find( 'td.psso-debug-controller .inner' ),
						rc = randomColorChange();
					$rows
						.append( $.parseHTML( '<span class="gind gind-' + v
							+ '" style="background: ' + rc + '">&nbsp;</span>' ) );
					$rows
						.first()
						.find('.gind-' + v )
						.append( $.parseHTML( '<a href="#" class="gind-trig gind-open" data-target="' + v
							+ '" style="border: 1px solid ' + rc
							+ ';color:' + rc + ';">'
							+ '<span class="dashicons dashicons-arrow-down-alt2"></span></a>' ) );
				});
			}
			// check boxes to show/hide common options
			$checkers = $( checker );
			$checkers.each( function() {
				optchk( $(this) )
			});
		}

		function disability( $item ) {
		    var $affect = $( 'input[name="' + $item.data('affect') + '"]' ),
                $row = $affect.closest( '.cmb-row:not(.cmb-repeat-row)' ),
                check = $item.data('disable'),
                val = $item.is(':checked') ? $item.val() : null;

            val == check ? $row.hide('fast') : $row.show('fast');
        }

        /**
         * Chooses random color
         * 
         * @returns {string}
         */
		function randomColorChange(){
			var hue        = Math.floor(Math.random() * 360),
				saturation = Math.floor(Math.random() * 100),
				lightness  = ( Math.floor(Math.random() * 40) + 20 ); // not too light and not too dark
			return "hsl(" + hue + ", " + saturation + "%, " + lightness + "%)";
		}

        /**
         * Gets query vars
         * 
         * thanks to http://css-tricks.com/snippets/javascript/get-url-variables/
         * @param variable
         * @returns {*}
         */
		function getPssoQueryVariable(variable) {
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
}(jQuery));