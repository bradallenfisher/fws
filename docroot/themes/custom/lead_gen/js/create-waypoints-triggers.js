;(function ($) {
  'use strict';
  $( document ).ready(function() {

    $.each($('div[data-ga-scroll-track="1"]'), function(index, value) {
      var sectionTitle = $('h2:first', value).text().trim(), sectionId = "#"+$(this)[0].id, anchor =  "#" + sectionTitle.replace(/\s+/g, '-').toLowerCase();
         // setup triggers
         if(sectionTitle) {
           $(sectionId).waypointVirtualPage({url: $(location).attr('pathname') + anchor, pageTitle:sectionTitle, triggerDelay: 3000} );
         } 
  });

 });
})
(jQuery);