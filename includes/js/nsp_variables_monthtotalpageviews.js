jQuery.post(
  nsp_variablesAjax_monthtotalpageviews.ajaxurl,
  {
    // here we declare the parameters to send along with the request
    // this means the following action hooks will be fired:
    // wp_ajax_nopriv_myajax-submit and wp_ajax_myajax-submit
    action : 'nsp_variables_'+nsp_variablesAjax_monthtotalpageviews.VAR,
 
    // other parameters can be added along with "action"
    VAR    : nsp_variablesAjax_monthtotalpageviews.VAR,
    URL    : nsp_variablesAjax_monthtotalpageviews.URL,
    LIMIT  : nsp_variablesAjax_monthtotalpageviews.LIMIT,
    FLAG   : nsp_variablesAjax_monthtotalpageviews.FLAG,
  },

  function( response ) {
    console.log('nsp_variables_'+nsp_variablesAjax_monthtotalpageviews.VAR+" "+response);
    document.getElementById(nsp_variablesAjax_monthtotalpageviews.VAR).innerHTML=response;
  }
); 
