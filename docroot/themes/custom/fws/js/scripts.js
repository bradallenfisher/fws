/*global $, jQuery, alert*/ ;
(function($) {
  "use strict";
  $(document).ready(function() {


    //Start jquery
    $('#nav-icon').click(function() {
      $(this).toggleClass('open');
      $('#navbar').toggleClass('open');
    });

    $(window).scroll(function() {
      var scroll = $(window).scrollTop();

      if (scroll >= 10) {
        $(".navbar").addClass("scrolling");
      } else {
        $(".navbar").removeClass("scrolling");
      }
    });

    //Slick Carousel
    $('.hero-slider').slick({
      dots: true,
      focusOnSelect: true,
      autoplay: true,
      autoplaySpeed: 5000,
      arrows: true,
      adaptiveHeight: true
    });

    //Slick Carousel pause/play functions
    $('.pause').on('click', function() {
        $('.slick-slider')
            .slick('slickPause')
            .slick('slickSetOption', 'pauseOnDotsHover', false);
        $(".pause").toggle();
        $(".play").toggle();
    });

    $('.play').on('click', function() {
        $('.slick-slider')
            .slick('slickPlay')
            .slick('slickSetOption', 'pauseOnDotsHover', true);
        $(".pause").toggle();
        $(".play").toggle();
    });

    //tabs
    $('.tabs-wrapper').tabs({
      //hide: { effect: "slide", direction: "left", distance: 200, duration: 500},
      show: {
        effect: "slide",
        direction: "down",
        distance: 800,
        duration: 150
      }
    });

    // Brad did this.
    //Scott added condition to exclude tabs (not([href*=\\#tab]) dependent on tabs have an id prefixed with #tab
    $('a[href*=\\#]:not([href=\\#]):not([href*=\\#tab])').click(function() {
      if (location.pathname.replace(/^\//, '') == this.pathname.replace(/^\//, '') && location.hostname == this.hostname) {
        var target = $(this.hash);
        target = target.length ? target : $('[name=' + this.hash.slice(1) + ']');
        if (target.length) {
          $('html,body').animate({
            scrollTop: target.offset().top-80
          }, 500);
          return false;
        }
      }
    });

    // Navbar Anchor based link analytics triggers
    $('.navbar-nav a').click(function() {
      var match = $(this).attr('href').match(/#\S+/);
      if (window.ga && ga.create) {
        ga('send', 'event', {
          eventCategory: 'Menu Link',
          eventAction: 'click',
          eventLabel: location.pathname + match[0]
        });
      }
    });
    // Tab click analytics triggers
    $('.nav-tabs a').click(function() {
      var match = $(this).attr('href').match(/#\S+/);
      if (window.ga && ga.create) {
        ga('send', 'pageview', {
          page: location.pathname + match[0],
          title: $(this).text()
        });
      }
    });
    // Tab selection Scroll into view
    $(".tabs-wrapper", this).tabs({
      beforeActivate: function(event, ui) {
        $("html, body").animate({
          scrollTop: $(this).offset().top - 80
        }, 1000);
      }
    });

    // Anchor links to same page tab selection. Activates and/or scrolls tab content into view from anchor links.
    $('.scroll-to-tab').click(function(event, ui) { //.scroll-to-tab is the only thing necessary from the GUI side to make this all work.
      var tabTarget, tabTargetIndex = 0,
        tabContent,
        tabCount = 0,
        currentTab, i = 0,
        totalTabCount = 0;
      event.preventDefault();
      tabTarget = "#" + $(this).attr('href').split('#')[1]; // What is our target tab destination
      if($('a[href="' + tabTarget + '"]')[0]==undefined){return;} //if the desired target doesn't exist, do nothing
      tabTargetIndex = $('a[href="' + tabTarget + '"]')[0].id.slice(-1); // get the id of the target anchor tag since it increments +1 for all tabs
      // :eq(i) is what makes this work from 0 to many tab elements
      tabCount = $('.tabs-wrapper:eq(' + i + ') >ul >li').length; //tabs in current instance. Initialize it in case we don't enter while loop
      totalTabCount = parseInt(tabCount); //I use this as an exit from the while loop. It increments by the number of tabs per tab instance until we find the user's target.
      currentTab = $('.tabs-wrapper:eq(' + i + ') .nav-tabs .ui-state-active > a').attr('href'); // need to know if target tab is also the selected tab cause you you can't activate an active tab and there would be no scroll into focus

      var index = $(tabTarget).index(); //indexes for the tab content for each tab instance starts at zero
      while (totalTabCount < tabTargetIndex ) {
        i++; //We entered here if the target was not in the first tabgroup so increment
        tabCount = $('.tabs-wrapper:eq(' + i + ') >ul >li').length; //tabs in current instance. Initialize it in case we don't enter while loop
        totalTabCount = parseInt(totalTabCount + tabCount);
        currentTab = $('.tabs-wrapper:eq(' + i + ') .nav-tabs .ui-state-active > a').attr('href'); //We need to check the current selected tab in each tab instance for a match to target

      }
      if (currentTab != tabTarget) { //activate the tab. The function above will trigger the scroll into view
        $('.tabs-wrapper:eq(' + i + ')').tabs("option", "active", index);
      } else if(currentTab == tabTarget) {
        //target tab is already selected scroll to it.
        $("html, body").animate({
          scrollTop: $('.tabs-wrapper:eq(' + i + ')').offset().top - 80
        }, 1000);
      }
      // send a Pageview
      //var match = $(this).attr('href').match(/#\S+/);
      var tabTargetLabel = $('a[href="' + tabTarget + '"]')[0].innerHTML;
      if (window.ga && ga.create) {
        ga('send', 'pageview', {
          page: location.pathname + tabTarget,
          title: $(this).text()
        });
      }

    });

  });
})(jQuery);