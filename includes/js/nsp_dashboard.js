(function($) {
  $.post(ExtData.Url, {
    VAR: "overview",
    KEY: ExtData.Key,
    PAR: "dashboard",
    TYP: "HTML"
  }, 
  function(data,status){
    $( "#nsp_loader-dashboard").hide();
    $( "#nsp_result-dashboard" ).html( data );
  }, "html").fail(function(error) { $( "#nsp_loader-dashboard").hide(); });
})(jQuery);  
