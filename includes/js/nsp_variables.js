jQuery.post(
  nsp_variablesAjax.ajaxurl,
  {
    // here we declare the parameters to send along with the request
    // this means the following action hooks will be fired:
    // wp_ajax_nopriv_myajax-submit and wp_ajax_myajax-submit
    action : 'nsp_variables'+nsp_variablesAjax.VAR,
 
    // other parameters can be added along with "action"
    VAR    : nsp_variablesAjax.VAR,
    URL    : nsp_variablesAjax.URL,
    LIMIT  : nsp_variablesAjax.LIMIT,
    FLAG   : nsp_variablesAjax.FLAG,
  },

  function( response ) {
    console.log(response);
    document.getElementById(nsp_variablesAjax.VAR).innerHTML=response;
  }
); 
