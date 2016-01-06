(function($) {
  $.post(ExtData.Url, {
    VAR: "overview",
    KEY: ExtData.Key,
    PAR: "dashboard",
    TYP: "HTML"
  }, 
  function(data,status){
    $( "#loader-dashboard").hide();
    $( "#result-dashboard" ).html( data );
  }, "html").fail(function(error) { $( "#loader-dashboard").hide(); });
})(jQuery);  
