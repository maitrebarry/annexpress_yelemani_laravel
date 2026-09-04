// Délégation sur document + garde anti-double-exécution : même raison que
// mon_js/alert_delete.js (voir ce fichier pour le détail).
(function () {
    if (window.__scripAgenceInit) return;
    window.__scripAgenceInit = true;

    $(document).on('click', '.edit-btn', function (e) {
        e.preventDefault();

        var numeros = $(this).data('numeros');
        var numero = $(this).data('numero');
        var localite = $(this).data('localite');
        var code = $(this).data('code');
        var tel = $(this).data('tel');

        $('#inputnumero').val(numero);
        $('#inputnumeros').val(numeros);
        $('#inputlocalite').val(localite);
        $('#inputcode').val(code);
        $('#tel').val(tel);

        $('#exampleDangerModal1').modal('show');
    });
})();
