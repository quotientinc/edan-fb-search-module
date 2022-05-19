jQuery(function() {
    AOS.init();
    jQuery("#fb-search-form").submit(function(e){
      e.preventDefault();
      let q = jQuery("#fb-field").val();

      let url = "/fb-search-search/" + escape(q);

      window.location.href=url;
    })
});
