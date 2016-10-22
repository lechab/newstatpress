jQuery.post(
  nsp_variablesAjax_totalpageviews.ajaxurl,
  {
    // here we declare the parameters to send along with the request
    // this means the following action hooks will be fired:
    // wp_ajax_nopriv_myajax-submit and wp_ajax_myajax-submit
    action : 'nsp_variables_'+nsp_variablesAjax_totalpageviews.VAR,
 
    // other parameters can be added along with "action"
    VAR    : nsp_variablesAjax_totalpageviews.VAR,
    URL    : nsp_variablesAjax_totalpageviews.URL,
    LIMIT  : nsp_variablesAjax_totalpageviews.LIMIT,
    FLAG   : nsp_variablesAjax_totalpageviews.FLAG,
  },

  function( response ) {
    console.log('nsp_variables_'+nsp_variablesAjax_totalpageviews.VAR+" "+response);
    document.getElementById(nsp_variablesAjax_totalpageviews.VAR).innerHTML=response;
  }
); 
