/*global $, jQuery, alert*/ ;
(function($) {
  "use strict";
  $(document).ready(function() {


    //Start jquery
    $('.search').click(function() {
      $(this).toggleClass('open');
      $('.click-search-form ').toggleClass('open');
    });

  });
})(jQuery);