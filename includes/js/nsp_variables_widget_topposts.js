jQuery.post(
  nsp_variablesAjax_widget_topposts.ajaxurl,
  {
    // here we declare the parameters to send along with the request
    // this means the following action hooks will be fired:
    // wp_ajax_nopriv_myajax-submit and wp_ajax_myajax-submit
    action : 'nsp_variables_'+nsp_variablesAjax_widget_topposts.VAR,
 
    // other parameters can be added along with "action"
    VAR    : nsp_variablesAjax_widget_topposts.VAR,
    URL    : nsp_variablesAjax_widget_topposts.URL,
    LIMIT  : nsp_variablesAjax_widget_topposts.LIMIT,
    FLAG   : nsp_variablesAjax_widget_topposts.FLAG,
  },

  function( response ) {
    document.getElementById(nsp_variablesAjax_widget_topposts.VAR).innerHTML=response;
  }
); 
