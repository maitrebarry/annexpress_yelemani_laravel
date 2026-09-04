// window.tgInitDataTables (scope) : fonction partagée, appelée une fois au tout premier
// chargement réel (ci-dessous) ET après chaque navigation douce de l'admin (voir
// mon_js/admin-transitions.js, dont le hook post-swap l'appelle) — une seule définition
// pour éviter toute divergence entre les deux cas (ex : les boutons d'export de #example2
// doivent être configurés à l'identique dans les deux situations). $.fn.DataTable.isDataTable()
// protège contre une double initialisation si jamais elle était appelée deux fois sur la
// même table (ce qui lève normalement une erreur DataTables).
window.tgInitDataTables = function (scope) {
	"use strict";
	scope = scope || document;

	var $example = $('#example', scope);
	if ($example.length && ! $.fn.DataTable.isDataTable($example[0])) {
		$example.DataTable();
	}

	var $example1 = $('#example1', scope);
	if ($example1.length && ! $.fn.DataTable.isDataTable($example1[0])) {
		$example1.DataTable();
	}

	var $example2 = $('#example2', scope);
	if ($example2.length && ! $.fn.DataTable.isDataTable($example2[0])) {
		var table = $example2.DataTable({
			lengthChange: false,
			buttons: ['copy', 'excel', 'pdf', 'print']
		});

		table.buttons().container()
			.appendTo('#example2_wrapper .col-md-6:eq(0)');
	}
};

$(function () {
	"use strict";
	window.tgInitDataTables(document);
});
