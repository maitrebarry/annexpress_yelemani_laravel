// window.tgInitSelect2 : même principe que window.tgInitDataTables dans
// table-datatable.js — fonction partagée, appelée au premier chargement (ci-dessous) et
// après chaque navigation douce de l'admin (voir mon_js/admin-transitions.js). La classe
// "select2-hidden-accessible" est ajoutée par select2() lui-même à l'élément déjà
// initialisé : elle sert de garde contre une double initialisation.
window.tgInitSelect2 = function (scope) {
	"use strict";
	scope = scope || document;

	$('.single-select, .multiple-select', scope).each(function () {
		var $el = $(this);
		if ($el.hasClass('select2-hidden-accessible')) return;

		$el.select2({
			theme: 'bootstrap4',
			width: $el.data('width') ? $el.data('width') : ($el.hasClass('w-100') ? '100%' : 'style'),
			placeholder: $el.data('placeholder'),
			allowClear: Boolean($el.data('allow-clear')),
		});
	});
};

$(function () {
	"use strict";
	window.tgInitSelect2(document);
});
