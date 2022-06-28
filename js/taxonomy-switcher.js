(function(window, document, $, undefined){

	window.TaxonomySwitcher = {};

	var txsw         = TaxonomySwitcher;
	var $context     = $('.wrap.taxonomy-switcher');
	var $nonce       = $('#taxonomy_switcher_nonce');
	var $ajaxinput   = $('#taxonomy-switcher-parent');
	var $ajaxcontext = $ajaxinput.parents('tr');
	var $ajaxresults = $('.taxonomy-switcher-ajax-results-posts', $ajaxcontext);
	var $ajaxhelp    = $('.taxonomy-switcher-ajax-results-help', $ajaxcontext);
	var $spinner     = $('.taxonomy-switcher-spinner', $ajaxcontext);

	txsw.hideSpinner = function() {
		// when leaving the input
		setTimeout(function(){
			// if it's been 2 seconds, hide our spinner
			$spinner.hide();
		}, 2000);
	}

	txsw.resultsClick = function( event ) {
		event.preventDefault();
		var $self = $(this);
		$spinner.hide();
		// populate post ID to field
		$ajaxinput.val( $self.data('termid') );/*.focus()*/
		$ajaxresults.html('');
		$ajaxhelp.hide();
	}

	txsw.ajaxSuccess = function(response) {
		console.log( 'response', response );
		// hide our spinner
		$spinner.hide();

		if ( typeof response.data !== 'undefined' ) {
			$ajaxresults.html(response.data.html);
			$ajaxhelp.show();
		}
	}

	txsw.maybeAjax = function( evt ) {

		$self = $(this);
		var term_search = $self.val();
		if ( term_search.length < 2 )
			return this;

		// only proceed if the user has pressed a number, letter or backspace
		if (evt.which <= 90 && evt.which >= 48 || evt.which == 8) {
			// clear out our results
			$ajaxresults.html('');
			$ajaxhelp.hide();
			$spinner.css({'float':'none'}).show();
			setTimeout(function(){
				// if they haven't typed in 500 ms
				if ( $ajaxinput.val() == term_search ) {
					$.ajax({
						type     : 'post',
						dataType : 'json',
						url      : ajaxurl,
						success  : txsw.ajaxSuccess,
						data     : {
							'action'   : 'taxonomy_switcher_search_term_handler',
							'tax_name' : $('#from_tax').val(),
							'search'   : term_search,
							'nonce'    : $nonce.val()
						}
					});
				}
			}, 500);
		}
	}

	$context
		.on( 'keyup', '#taxonomy-switcher-parent', txsw.maybeAjax ).blur( txsw.hideSpinner )
		.on( 'click', '.taxonomy-switcher-ajax-results-posts a', txsw.resultsClick );

})(window, document, jQuery);
