jQuery(document).ready(function ($) {
    
    console.log('M');
    
    var isMobile = false,
        ccClickStart = isMobile ? 'touchstart click' : 'click',
        submenu = '.dropdown-menu-submenu',
        subindicator = 'cc-has-child',
        $pop = $('.popoverclass'),
        $ccBodyAnswer = $('.cc-body-answer'),
        $entry = $('.entry'),
        $body = $('body'),
        $drop = $('ul.dropdown-menu'),
        $dropitem = $drop.find('li'),
        $submenus;


    // home page menus
    $pop.popover({
        html: true,
        placement: function (context, source) {
            if ($(source).hasClass('homecolumn4')) {
                return "left";
            }
            return "right";
        },
        content: function () {
            return $(this).next().html();
        },
        template: '<div class="popover" role="tooltip" aria-expanded="true"><div class="arrow"></div><h2 class="popover-title"></h2><div class="popover-content"></div></div>'
    }).on(ccClickStart, function (e) {
        console.log('home popover hide active click');
        e.preventDefault();
        $pop.not(this).popover('hide');
    });

    // hide home menus on clicks outside menu
    $body.on(ccClickStart, function (e) {
        if ($pop.length && !$pop.is(e.target) && $pop.has(e.target).length === 0 && $pop.parent().has(e.target).length === 0) {
            $pop.not(this).popover('hide');
        }
    });

    // add class to indicate child
    $dropitem.each(function () {
        var $ul = $(this).find('ul');
        if ( $ul.length ) {
            $(this).addClass(subindicator);
        }
    });

    $submenus = $(submenu);
    
    // open sub-menus on click
    $body.on(ccClickStart, '.' + subindicator + ' a', function(e) {
        var $parent = $(this).parent(),
            $sub = $parent.find(submenu);
        if ( $sub.length ) {
            e.preventDefault();
            e.stopPropagation();
            $submenus.hide();
            $sub.show();
            if ( ! $sub.visible() ) {
                $parent.addClass('edge');
            }
        }
    });

    // hide sub-menus on clicks outside menu
    $body.on(ccClickStart, function (e) {
        if ($drop.length && !$drop.is(e.target) && $drop.has(e.target).length === 0 && $drop.parent().has(e.target).length === 0) {
            $submenus.hide();
        }
    });
    

    // fancybox adjustments
    function fixFancybox() {
        $('div.glossary[role="dialog"]').attr('aria-hidden',"false");
        $('#first_letter').focus();
        var fancyboxHeight = $('.glossarywrap .fancybox-inner').outerHeight();
        var doubleshadowHeight = $('.glossarywrap .doubleshadow').outerHeight();
        $('.glossarycontentwrap').css({'height': (fancyboxHeight - doubleshadowHeight - 40) + 'px'});
    }

    // glossary button
    $('.btn-glossary').fancybox({
        width: '90%',
        height: '90%',
        autoSize: false,
        closeClick: false,
        openEffect: 'elastic',
        closeEffect: 'elastic',
        padding: '0',
        wrapCSS: 'glossarywrap',
        scrolling: 'hidden',
        afterShow: fixFancybox,
        onUpdate: fixFancybox,
        helpers: {
            title: {
                type: 'inside'
            },
            overlay: {
                locked: true
            }
        }
    });

    // credits button
    if ($('#cc-unit-credits').length) {
        var button = '<li class="menu-item"><a class="fancyinline" href="#cc-unit-credits">Credits</a></li>';
        $('#footermenu').append(button);
        $('.fancyinline').fancybox({
            maxWidth: 600,
            maxHeight: 400,
            fitToView: false,
            width: '70%',
            height: '70%',
            autoSize: false,
            closeClick: false,
            openEffect: 'elastic',
            closeEffect: 'elastic',
            padding: '0',
            wrapCSS: 'creditswrap',
            scrolling: 'hidden',
            afterShow: fixFancybox,
            onUpdate: fixFancybox,
            helpers: {
                title: {
                    type: 'inside'
                },
                overlay: {
                    locked: true
                }
            }
        });
    }

    // remove classes from images, add img-responsive class
    $entry.find('img').addClass('img-responsive').removeAttr("width").removeAttr("height");

    // faq
    $("div[id^=qa-faq]").each(function () {
        var num = this.id.match(/qa-faq(\d+)/)[1],
            $faqContainer = $('.qa-faqs'),
            $faq = $('#qa-faq' + num);
        if ($faqContainer.is('.collapsible')) {
            $faq.find('.qa-faq-anchor').bind(ccClickStart, function () {
                if ($faqContainer.is('.accordion')) {
                    $('.qa-faq-answer').not('#qa-faq' + num + ' .qa-faq-answer').hide();
                }
                if ($faqContainer.is('.animation-fade')) {
                    $faq.find('.qa-faq-answer').fadeToggle();
                } else if ($faqContainer.is('.animation-slide')) {
                    $faq.find('.qa-faq-answer').slideToggle();
                } else  /* no animation */ {
                    $faq.find('.qa-faq-answer').toggle();
                }
                return false;
            });
            $('.expand-all.expand').bind(ccClickStart, function () {
                $('.expand-all.expand').hide();
                $('.expand-all.collapse').show();
                if ($faqContainer.is('.animation-fade')) {
                    $('.qa-faq-answer').fadeIn(400);
                } else if ($faqContainer.is('.animation-slide')) {
                    $('.qa-faq-answer').slideDown();
                } else  /* no animation */ {
                    $('.qa-faq-answer').show();
                }
            });
            $('.expand-all.collapse').bind(ccClickStart, function () {
                $('.expand-all.collapse').hide();
                $('.expand-all.expand').show();
                if ($faqContainer.is('.animation-fade')) {
                    $('.qa-faq-answer').fadeOut(400);
                } else if ($faqContainer.is('.animation-slide')) {
                    $('.qa-faq-answer').slideUp();
                } else  /* no animation */ {
                    $('.qa-faq-answer').hide();
                }
            });
        }
    });

    // problem solution buttons
    $("#cc-problem-ans-buttons").find("button").on('click', function () {
        if ($(this).hasClass("selected")) {
            $(this).removeClass('selected');
            $ccBodyAnswer.css('display', 'none');
        } else {
            $("#cc-problem-ans-buttons").find("button").removeClass('selected');
            $(this).addClass('selected');
            var wp = $(this).attr('data-answer');
            $ccBodyAnswer.css('display', 'none');
            $("#cc-body-answer-" + wp).css('display', 'block');
        }
    });

    // open items with class "opennew" in new tab
    $('.opennew').find('a').attr('target', '_blank');

    // forces mathjax font load
    $entry.prepend('<div style="position:absolute;top:0;left:0;width:0;height:0;"><math><mtext></mtext></math></div>');

    // parse the text once page is loaded for inline numbers
    $(window).load(function () {

        // classes to avoid
        var avoidClasses = ["MathJax_Preview", "MathJax", "jwplayer", "cc-jwp", "xdebug-var-dump"],
            avoidTags = ["img", "math", "br", "hr", "nobr", "script", "style", "form", "object", "embed", "video", "media"],
            replacespan = '<span class="mathjaxfont dey-added">$1</span>';

        mathjaxfont_parser('.entry');

        // turn inline numbers into mathjax fonts
        function mathjaxfont_parser(el) {
            $(el).contents().each(function () {
                var node = this.nodeName.toString().toLowerCase();
                // make sure element doesn't have one of our classnames
                if ($(this).hasClasses(avoidClasses) == false) {
                    // make sure this is not a tag we're avoiding; -1 is returned if node is not in the array
                    if ($.inArray(node, avoidTags) < 0) {
                        if (this.childNodes.length > 0) {
                            mathjaxfont_parser(this);
                        } else {
                            $(this).replaceWith(this.textContent.replace(/(\[|]|\(|\)|\+|–|=|\d+)/gi, replacespan));
                        }
                    }
                }
            });
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

// function to allow checking of multiple classes
jQuery.fn.extend({
    hasClasses: function (classNames) {
        var self = this;
        for (var i in classNames) {
            if (jQuery(self).hasClass(classNames[i]))
                return true;
        }
        return false;
    }
});

// adds filter to determine if element is offscreen
jQuery.expr.filters.offscreen = function (el) {
    return (
        (el.offsetLeft + el.offsetWidth) < 0
        || (el.offsetTop + el.offsetHeight) < 0
        || (el.offsetLeft > window.innerWidth || el.offsetTop > window.innerHeight)
    );
};

jQuery('.btn-group').on('show.bs.dropdown', function (e) {
    jQuery(this).attr('aria-expanded', true);
});

jQuery('.btn-group').on('hide.bs.dropdown', function (e) {
    jQuery(this).attr('aria-expanded', false);
});

