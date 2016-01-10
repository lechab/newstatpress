(function($) {
  $.post(ExtData.Url, {
    VAR: "dashboard",
    KEY: ExtData.Key,
    PAR: "",
    TYP: "HTML"
  }, 
  function(data,status){
    $( "#nsp_loader-dashboard").hide();
    $( "#nsp_result-dashboard" ).html( data );
  }, "html").fail(function(error) { $( "#nsp_loader-dashboard").hide(); });
})(jQuery);  
