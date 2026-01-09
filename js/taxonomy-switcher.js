window.TaxonomySwitcher = {};

(function (window, document, txsw) {

	let context = document.querySelector('#wds-taxonomy-switcher');
	let nonce = document.querySelector('#taxonomy_switcher_nonce');
	let ajaxinput = document.querySelector('#taxonomy-switcher-parent');
	let ajaxcontext = ajaxinput.closest('tr');
	let ajaxresults = ajaxcontext.querySelector('.taxonomy-switcher-ajax-results-posts');
	let ajaxhelp = ajaxcontext.querySelector('.taxonomy-switcher-ajax-results-help');
	let spinner = ajaxcontext.querySelector('.taxonomy-switcher-spinner');
	let from_tax = document.querySelector('#from_tax');
	let parentselect = document.querySelector('#taxonomy-switcher-parent');

	if (from_tax) {
		from_tax.addEventListener('change', (e) => {
			let curval = e.currentTarget.value;
			let selected = tsTaxData.find(obj => {
				return obj.taxonomy === curval
			});

			if (selected) {
				parentselect.disabled = selected.hierarchical === 'false';
			}
		});
	}

	txsw.hideSpinner = function () {
		// when leaving the input
		setTimeout(function () {
			// if it's been 2 seconds, hide our spinner
			spinner.style.display = 'none';
		}, 2000);
	}

	txsw.resultsClick = function (e) {
		e.preventDefault();

		let self = e.target;
		spinner.style.display = 'none';

		ajaxinput.value = self.dataset.termid;
		ajaxresults.innerHTML = '';
		ajaxhelp.style.display = 'none';
	}

	txsw.ajaxSuccess = function (response) {
		ajaxresults.innerHTML = '';
		// hide our spinner
		spinner.style.display = 'none';
		if (typeof response.data !== 'undefined') {
			ajaxresults.insertAdjacentHTML('afterbegin', response.data.html);
			ajaxhelp.style.display = 'block';
		}
	}

	txsw.maybeAjax = function (e) {
		let term_search = e.target.value;
		if (term_search.length < 2) {
			return this;
		}

		// only proceed if the user has pressed a number, letter or backspace
		if (e.keyCode <= 90 && e.keyCode >= 48 || e.code === 'Backspace') {
			// clear out our results
			ajaxresults.innerHTML = '';
			ajaxhelp.style.display = 'none';
			spinner.style.float = 'none';
			spinner.style.display = 'block';
			spinner.style.visibility = 'visible';
			setTimeout(function () {
				// if they haven't typed in 500 ms
				if (ajaxinput.value === term_search) {
					const data = new FormData();
					const fromtax = document.querySelector('#from_tax');
					data.append('action', 'taxonomy_switcher_search_term_handler');
					if (fromtax) {
						data.append('tax_name', fromtax.value);
					}
					data.append('search', term_search);
					data.append('nonce', nonce.value);
					const options = {
						method: 'POST', body: data,
					}

					fetch(ajaxurl, options)
						.then((response) => response.json())
						.then((response) => {
							txsw.ajaxSuccess(response);
						}).catch((error) => {
						console.log(error);
					});
				}
			}, 500);
		}
	}

	if (ajaxinput) {
		ajaxinput.addEventListener('keyup', txsw.maybeAjax);
		ajaxinput.addEventListener('blur', txsw.hideSpinner);
	}

	if (context) {
		context.addEventListener('click', () => {
			let links = document.querySelectorAll('.taxonomy-switcher-ajax-results-posts a');
			if (links) {
				Array.from(links).forEach((link) => {
					link.addEventListener('click', txsw.resultsClick);
				});
			}
		})
	}
})(window, document, TaxonomySwitcher);
