jQuery(function() {
    AOS.init();

    let currentSearchParams = new URLSearchParams(location.search);

    /*if(currentSearchParams.has("fname"))
    {
      jQuery("#fb-search-fname").val(currentSearchParams.get("fname"));
    }

    if(currentSearchParams.has("lname"))
    {
      jQuery("#fb-search-lname").val(currentSearchParams.get("lname"));
    }

    if(currentSearchParams.has("location"))
    {
      jQuery("#fb-search-location").val(currentSearchParams.get("location"));
    }*/

    //if(currentSearchParams.has("rtype"))

    jQuery("#fb-search-rtype").select2();

    jQuery("#fb-search-form").submit(function(e){
      e.preventDefault();
      let q = jQuery("#fb-search-field").val();

      if(q.trim() == "")
      {
        q = "*";
      }

      //let url = new URL("/fb-search/" + escape(q), location.host);
      let url = new URL(location.origin + "/fb-search/" + escape(q));

      if(jQuery("#fb-search-fname").val().trim() !== "")
      {
        url.searchParams.append("fname", jQuery("#fb-search-fname").val());
      }

      if(jQuery("#fb-search-lname").val().trim() !== "")
      {;
        url.searchParams.append("lname", jQuery("#fb-search-lname").val());
      }

      if(jQuery("#fb-search-location").val().trim() !== "")
      {
        url.searchParams.append("location", jQuery("#fb-search-location").val());
      }

      if(jQuery("#fb-search-rtype").val().trim() !== "")
      {
        url.searchParams.append("rtype", jQuery("#fb-search-rtype").val());
      }

      if(jQuery("#fb-search-start-date").val().trim() !== "" && jQuery("#fb-search-end-date").val().trim() !== "")
      {
          url.searchParams.append("date", jQuery("#fb-search-start-date").val() + "|" + jQuery("#fb-search-end-date").val());
      }

      console.log("START: " + jQuery("#fb-search-start-date").val());
      console.log("END: " + jQuery("#fb-search-end-date").val());

      window.location.href=url;
    });

    jQuery(".fb-nav-link").click(function(e){
      e.preventDefault();
      let url = new URL(jQuery(this).prop("href") + location.search);

      if(jQuery("#fb-search-fname").val().trim() == "")
      {
        url.searchParams.delete("fname");
      }

      if(jQuery("#fb-search-lname").val().trim() == "")
      {
        url.searchParams.delete("lname");
      }

      if(jQuery("#fb-search-location").val().trim() == "")
      {
        url.searchParams.delete("location");
      }

      if(jQuery("#fb-search-start-date").val().trim() == "" || jQuery("#fb-search-end-date").val().trim() == "")
      {
        url.searchParams.delete("date");
      }


      window.location.href=url;
    });
});
