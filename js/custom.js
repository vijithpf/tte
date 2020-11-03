/** Custom JavaScript */
(function($) {

        var $el = {},
            _screenWidth,
            _screenHeight,
            scrollTop;

        $(document).ready(domReady);

        function cacheDom() {
            $el.htmlNbody = $('body , html');
            $el.body = $('body');
            $el.html = $('html');

            $el.siteLoader = $('.site-loader');
            $el.header = $('header');
            $el.siteBody = $('.site-body');
            $el.footer = $('footer');
            $el.banner = $('.banner');
            $el.homeBanner = $('#home_banner');
            $el.mainHeader = $('.main-header');

            $el.gotoTop = $('.gotoTop');
            $el.bannerSlides = $('#banner_slides');
            $el.testimonialsSlider = $('#testimonials_slider');
            $el.articlesSlider = $('#articles_slider');
            $el.blurImage = $('.blurImage');

            //banner blurs
            $el.bannerMainImg = $('.bannerMainImg');
            $el.bannerBlurWrap = $('.bannerBlurWrap');
            $el.bannerBlurImg = $('.bannerBlurImg');
            $el.bannerContent = $('.banner_content');

            $el.tempWrap = $('.temp-wrap');
            $el.largeGallerySlider = $('.large-gallery-slider');
            $el.clientTestimonialSlider = $('.client-t-slider');
            $el.milestoneSlider = $('#milestone-list-slider');
            $el.enquireBtnModal = $('.enquire-btn-modal');

            $el.mepHomeSlider = $('#mep-home-slider');
            $el.fmHomeSlider = $('#fm-home-slider');
            $el.mainClientSelectionSlider = $('.main-client-selection-slider');

            $el.menuHamburger = $('.menu-hamburger');
            $el.secondarymenu = $('.other_links');

            $el.mobileNav= $('.mobile-nav');
            $el.$ieModal = $('#ie-modal');

          }

        function domReady() {
            cacheDom();
            setEvents();

            handleSplashScreen();
            smoothScroll();
            //sliders must be called before blurring
            handleSliders();
            handleBannerSlider();
            handleBlurImages();

            $('#productModal').on('hide.bs.modal', function () {
              if ($('.formidable').hasClass('submission')) {
                location.reload();
              }
            });

            $('.business-filter select').on('change',function () {
              $('#filters_form').submit();
            });

            $el.gotoTop.click(function(e) {
                e.preventDefault();
                $el.htmlNbody.animate({
                    scrollTop: 0
                }, 200);
            });

            $('.toggle-menu').jPushMenu();

            $('.close-enquiry').click(function () {
              if ($('.formidable').hasClass('submission')) {
                location.reload();
              }
            });

            //Basically
            //onfocus
            $('.formidable input:not([type=radio]):not([type=checkbox]), textarea').focus(function() {
              $(this).closest('.element').addClass('has_focus');
            }); //end focus

            //outfocus
            $('.formidable input:not([type=radio]):not([type=checkbox]), textarea').blur(function() {
              if($.trim($(this).val()).length === 0) {
                $(this).closest('.element').removeClass('has_focus');
              }
            }); //end blur

            $el.enquireBtnModal.click(function(e) {
                e.preventDefault();
                $("#productModal").modal('show');
                $("#product-name-16").val($(this).data("product"));
            });

            $('.fancy-img').fancybox();

            $el.menuHamburger.find('> a').click(function () {
              $(this).find('.hamburger').toggleClass('active');

              if ($(window).width() > 767) {
                $el.secondarymenu.toggleClass('active');
              } else {
                $el.mobileNav.toggleClass('active');
              }

               return false;
            });


            if( $el.html.hasClass("ie9") || $el.html.hasClass("ie8")) {
                $el.$ieModal.modal();
            }

          }

        function setEvents() {
          screenResize();
            $(window)
                .load(handleWidgetsLoading)
                .resize(screenResize)
                .scroll(windowScroll);

            $el.footer.on('click', handleFooterClick);

            $el.header.find('.mobile-menu').on('click', handleMobileMenu);
        }

        function handleFooterClick() {

        }

        function smoothScroll() {
          $('a[href*="#"]:not([href="#"])').click(function() {
           if (location.pathname.replace(/^\//,'') == this.pathname.replace(/^\//,'') && location.hostname == this.hostname) {
             var target = $(this.hash);
             target = target.length ? target : $('[name=' + this.hash.slice(1) +']');
             if (target.length) {
               $('html, body').animate({
                 scrollTop: target.offset().top
               }, 1000);
               return false;
             }
           }
         });
        }


        function screenResize() {
            _screenWidth = $(window).width();
            _screenHeight = $(window).height();

            if(_screenHeight>550 & _screenHeight<800){
          		$el.homeBanner.height(_screenHeight);
          	}

            handleBlurImages();
        }

        function windowScroll() {
          scrollTop = $(window).scrollTop();
          _screenWidth = $(window).width();
          _screenHeight = $(window).height();

          $el.mainHeader.toggleClass("sticky",scrollTop > 10);

          //toggle goto top button
          $el.gotoTop.toggleClass("active",scrollTop > (_screenHeight/2));
          //$el.tempWrap.toggleClass("active", scrollTop >= $el.banner.height() - 500 );

        }

        function handleMobileMenu() {

        }

        function handleSplashScreen() {
            /* loading screen */
            $el.siteLoader.delay(1500).fadeOut(500);
        }

        function handleBannerSlider() {
          var slidePlay = false;
           if ($el.bannerSlides.find('.slides-container li').length>1) {
             slidePlay = 7000
           } else {
             slidePlay = false;
           }

          $el.bannerSlides.superslides({
        		animation: 'fade',
            play: slidePlay,
            inherit_width_from: $el.banner,
        		inherit_height_from: $el.banner,
            pagination: true
        	});

          $el.bannerSlides.on('animating.slides', function() {
            var slideIndex = $('#banner_slides .slides-pagination a.current').index();

            $('.banner_blurred_wrap .banner_blurred_image').eq(slideIndex).addClass('active').siblings().removeClass('active');
            $('.banner_details').eq(slideIndex).addClass('active').siblings().removeClass('active');
          });
        }

        function handleSliders() {
          if (!$el.body.hasClass('edit-mode')) {
            $el.testimonialsSlider.slick({
              slidesToShow: 1,
              slidesToScroll: 1,
              infinite: false,
              arrows: true,
              // vertical: true,
              adaptiveHeight: false,
              dots: true,
              autoplay: 7000
            });

            $el.largeGallerySlider.slick({
              centerMode: true,
              centerPadding: '300px',
              slidesToShow: 1,
              slidesToScroll: 1,
              initialSlide: 1,
              infinite: false,
              arrows: false,
              dots: true,
              swipe: false,
              autoplay: 7000,
              responsive: [
                {
                  breakpoint: 1100,
                  settings: {
                    centerMode: false,
                    slidesToShow: 2,
                    slidesToScroll: 2
                  }
                },
                {
                  breakpoint: 991,
                  settings: {
                    centerMode: false,
                    slidesToShow: 1,
                    slidesToScroll: 1
                  }
                }
              ]
            });

            // $el.milestoneSlider.slick({
            //   dots: false,
            //   arrows: true,
            //   infinite: false,
            //   vertical: true,
            //   verticalSwiping: true,
            //   slidesToShow: 3
            // });

            var $frame  = $('.milestone-list__parent');

            if ($frame.length) {
              var $slidee = $frame.children('ul').eq(0);
          		var $wrap   = $frame.parent();

          		// Call Sly on frame
          		$frame.sly({
          			itemNav: 'basic',
          			smart: 1,
          			activateOn: 'click',
          			mouseDragging: 1,
          			touchDragging: 1,
          			releaseSwing: 1,
          			startAt: 0,
          			scrollBy: 1,
          			speed: 300,
          			elasticBounds: 1,
          			easing: 'easeOutExpo',
          			dragHandle: 1,
          			dynamicHandle: 1,
          			clickBar: 1,
          			prev: $wrap.find('.ms-prev'),
          			next: $wrap.find('.ms-next'),
          		});

              $(window).resize(function () {
                if ($(window).width() < 768) {
                  $frame.sly(false); // does the same thing
                } else {
                  $frame.sly().init();
                }
              });
            }



            $el.clientTestimonialSlider.slick({
              slidesToShow: 3,
              slidesToScroll: 1,
              speed: 500,
              infinite: true,
              arrows: false,
              dots: true,
              autoplay: 7000,
              responsive: [
                {
                  breakpoint: 991,
                  settings: {
                    slidesToShow: 2,
                    slidesToScroll: 2
                  }
                },
                {
                  breakpoint: 640,
                  settings: {
                    slidesToShow: 1,
                    slidesToScroll: 1
                  }
                }
              ]
            });
          }

          $el.articlesSlider.slick({
            slidesToShow: 2,
            slidesToScroll: 1,
            infinite: false,
            arrows: false,
            adaptiveHeight: false,
            dots: true,
            infinite: false,
            autoplay: 7000,
            responsive: [
              {
                breakpoint: 991,
                settings: {
                  slidesToShow: 1
                }
              }
            ]
          });

          $('.home-client-selection-slider').slick({
             slidesToShow: 3,
             slidesToScroll: 3,
             infinite: false,
             arrows: false,
             adaptiveHeight: false,
             dots: true,
             autoplay: 7000,
             responsive: [
               {
                 breakpoint: 991,
                 settings: {
                   slidesToShow: 2,
                   slidesToScroll: 2
                 }
               },
               {
                 breakpoint: 480,
                 settings: {
                   slidesToShow: 1,
                   slidesToScroll: 1
                 }
               }
             ]
           });

           $('.main-client-selection-slider').slick({
             slidesToShow: 4,
             slidesToScroll: 4,
             infinite: false,
             arrows: false,
             adaptiveHeight: false,
             dots: true,
             autoplay: 7000,
             responsive: [
               {
                 breakpoint: 991,
                 settings: {
                   slidesToShow: 3,
                   slidesToScroll: 3
                 }
               },
               {
                 breakpoint: 660,
                 settings: {
                   slidesToShow: 2,
                   slidesToScroll: 2
                 }
               },
               {
                 breakpoint: 440,
                 settings: {
                   slidesToShow: 1,
                   slidesToScroll: 1
                 }
               }
             ]
           });

           $('.all-clients-selection-slider').slick({
             slidesToShow: 1,
             slidesToScroll: 1,
             infinite: false,
             arrows: false,
             adaptiveHeight: false,
             dots: true,
             autoplay: 7000
           });
        }

        function handleWidgetsLoading() {

        }

        function handleBlurImages() {
          //Banner blur effect
          //since the banner image is full width, we have to take a different approach
          if ($el.bannerBlurImg.length) {
            $el.bannerBlurImg.width($el.bannerMainImg.width());
            $el.bannerBlurImg.height($el.bannerMainImg.height());

            $el.bannerBlurImg.css('top', -($el.bannerMainImg.height() - $el.bannerBlurWrap.height()));
            $el.bannerBlurImg.css('left', -$el.bannerContent.offset().left);
          }

          //otherblur
          $el.blurImage.each(function() {
            var parent = $(this).parents('section'),
                mainImage = '.mainImage',
                blurWrap = '.blurWrap',
                margins = 0;

            //if there is block content
            //else we dont need to substract anything
            if (parent.find('.block_content').length) {
              var blockContent = parent.find('.block_content');
              margins = parseInt(blockContent.css('marginTop')) + parseInt(blockContent.css('marginRight')) + parseInt(blockContent.css('marginBottom')) + parseInt(blockContent.css('marginLeft'));
            }

            //set width and height of 'blurred image' to same as the 'main image'
            $(this).width(parent.find(mainImage).outerWidth());
            $(this).height(parent.find(mainImage).outerHeight());

            //substract the 'main image' width and height with the 'blur wrap' to properly position the blurred image
            var top = -(parent.find(mainImage).height() - parent.find(blurWrap).height());
            //we must substract the margin of parent(of blurWrap) to get the correct value without any margins
            //this should be done to borders as well but here there are no border soo...
            var left = -((parent.find(mainImage).width() - parent.find(blurWrap).width()) - margins);

            //set 'top' for the blurred image
            $(this).css('top', top);

            //set 'left' or 'right' for blurred image
            //if the block is on the left
            if ($(this).parents('section').find(blurWrap).hasClass('left')) {
              //use negative right position
              $(this).css('right', left);
            } else {
              //else use negative left
              $(this).css('left', left);
            }
          }); //end each
        }


        (function init() {
            //detect mobile platform
            if (navigator.userAgent.match(/(iPod|iPhone|iPad)/)) {
                $("body").addClass("ios-device");
            }
            if (navigator.userAgent.match(/Android/i)) {
                $("body").addClass("android-device");
            }

            //detect desktop platform
            if (navigator.appVersion.indexOf("Win") != -1) {
                $('body').addClass("win-os");
            }
            if (navigator.appVersion.indexOf("Mac") != -1) {
                $('body').addClass("mac-os");
            }

            //detect IE 10 and above 11
            if (navigator.userAgent.indexOf('MSIE') !== -1 || navigator.appVersion.indexOf('Trident/') > 0) {
                $("html").addClass("ie10");
            }

            //Specifically for IE8 (for replacing svg with png images)
            if ($("html").hasClass("ie8")) {
                var imgPath = "/themes/theedge/images/";
                $("header .logo a img,.loading-screen img").attr("src", imgPath + "logo.png");
            }
        })();

})(jQuery);

/* Uncomment below if you need to add google captcha (also in includes/script.php) => Make sure the SITEKEY is changed below
var CaptchaCallback = function(){
    $('.g-recaptcha').each(function(index, el) {
        grecaptcha.render(el, {'sitekey' : '6LeB3QwUAAAAADQMo87RIMbq0ZnUbPShlwCPZDTv'});
    });
};
*/

function showFormErrors($form, errors) {
    if (!$form || !($form instanceof jQuery) || $form.length < 1 || !errors || errors.constructor !== Array || errors.length < 1) {
        return;
    }

    var $errors = $('<ul>').attr({'class': "ccm-error"});
    errors.forEach(function (error) {
        $errors.append(
            $('<li>').text(error)
        )
    });
    $form.before($errors);
}

function removeFormErrors($form) {
    if (!$form || !($form instanceof jQuery) || $form.length < 1) {
        return;
    }

    $form.prevAll('.ccm-error').remove();
}

var CaptchaCallback = function(){
  $('.g-recaptcha').each(function(index, el) {
    grecaptcha.render(el, {'sitekey' : '6LeJ9z4UAAAAAIVo4oIFXrpzeqttTZi7LVeb2geX'});
  });
};
