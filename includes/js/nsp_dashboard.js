(function($) {
  $( "#nsp_error-dashboard").hide();  
  $.post(nsp_externalAjax_dashboard.ajaxurl, {
    action : 'nsp_external_dashboard',  
    VAR: "dashboard",
    KEY: nsp_externalAjax_dashboard.Key,
    PAR: "",
    TYP: "HTML"
  }, 
  function(data,status){
    $( "#nsp_loader-dashboard").hide();
    $( "#nsp_result-dashboard" ).html( data );
  }, "html").fail(function(error) { 
        $( "#nsp_loader-dashboard").hide();
        $( "#nsp_error-dashboard").show();
      });
})(jQuery);  
