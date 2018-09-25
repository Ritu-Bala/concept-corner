jQuery(document).ready(function($) {
    
	console.log('K');

	// determine if user is on mobile device

	var isMobile = 'ontouchstart' in document.documentElement;
	var ccClickAction = isMobile ? 'touchend' : 'click';
	var ccClickStart = isMobile ? 'touchstart click' : 'click';
	var $body = $('body'),
		submenu = '.dropdown-menu-submenu',
		subindicator = 'cc-has-child',
		$drop = $('ul.dropdown-menu'),
		$dropitem = $drop.find('li'),
		$submenus;

	// go to all video page

	$('.side').on(ccClickStart, function(e) {
		e.preventDefault();
		var gogo = $(this).attr('data-location');
		window.location.href = 'https://' + window.location.host + '/explore/' + gogo + "/video/";
	});

	// add class to indicate child
	$dropitem.each(function () {
		var $ul = $(this).find('ul');
		if ( $ul.length ) {
			$(this).addClass(subindicator);
			console.log("indicate child");
		}
	});

	$submenus = $(submenu);

	// open sub-menus on click
	$body.on(ccClickStart, '.' + subindicator + ' a', function(e) {
		var $parent = $(this).parent(),
			$sub = $parent.find(submenu);
			console.log("sub-menu1");
		if ( $sub.length ) {
			e.preventDefault();
			e.stopPropagation();
			$submenus.hide();
			$sub.show();
			console.log("sub-menu2");
			if ( ! $sub.visible() ) {
				$parent.addClass('edge');
				console.log("inside if");
			}
		}
	});

	// hide sub-menus on clicks outside menu
	$body.on(ccClickStart, function (e) {
		if ($drop.length && !$drop.is(e.target) && $drop.has(e.target).length === 0 && $drop.parent().has(e.target).length === 0) {
			$submenus.hide();
			console.log("hide outside");
		}
	});

	if( $('.hidden.transcript').length ){
        var random = Math.floor((Math.random() * 100) + 1);
        $( "<a href='#' id='video-"+random+"' class='view-transcript'>View Transcript</a>" ).insertBefore(".hidden.transcript");
        $('a.view-transcript#video-'+random).click(function(event){
            event.preventDefault();
            $('.transcript').toggleClass('hidden');
            if( $('.transcript').hasClass('hidden') ){
                $('.view-transcript').text('View Transcript');
            }else{
                $('.view-transcript').text('Hide Transcript');
            }
        });
    }
});