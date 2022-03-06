/**
 * This total visits script
 *
 * @package NewStatpress
 */

jQuery.post(
	nsp_variablesAjax_thistotalvisits.ajaxurl,
	{
		// here we declare the parameters to send along with the request
		// this means the following action hooks will be fired:
		// wp_ajax_nopriv_myajax-submit and wp_ajax_myajax-submit.
		action : 'nsp_variables',

		// other parameters can be added along with "action".
		VAR    : nsp_variablesAjax_thistotalvisits.VAR,
		URL    : nsp_variablesAjax_thistotalvisits.URL,
		LIMIT  : nsp_variablesAjax_thistotalvisits.LIMIT,
		FLAG   : nsp_variablesAjax_thistotalvisits.FLAG,

		// send the nonce along with the request.
		postCommentNonce : nsp_variablesAjax_thistotalvisits.postCommentNonce
	},
	function( response ) {
		document.getElementById( nsp_variablesAjax_thistotalvisits.VAR ).innerHTML = response;
	}
);
