// Délégation sur document + garde anti-double-exécution : même raison que
// mon_js/alert_delete.js (voir ce fichier pour le détail).
(function () {
    if (window.__scripCompagnieInit) return;
    window.__scripCompagnieInit = true;

    $(document).on('click', '.edit-btn', function (e) {
        e.preventDefault();

        let id       = $(this).data('id_compagnie');
        let nom      = $(this).data('nom_compagnie');
        let libele   = $(this).data('libele');
        let slogant  = $(this).data('slogant');
        let logo     = $(this).data('logo'); // full URL for preview
        let logoFilename = $(this).data('logofilename'); // just the filename
        let telephone = $(this).data('telephone');
        let email     = $(this).data('email');
        let whatsapp  = $(this).data('whatsapp');
        let adresse   = $(this).data('adresse');
        let facebook  = $(this).data('facebook');
        let instagram = $(this).data('instagram');

        $('#inputidCompagnie').val(id);
        $('#inputnomCompagnie').val(nom);
        $('#inputlibele').val(libele);
        $('#inputslogant').val(slogant);
        $('#inputtelephone').val(telephone);
        $('#inputemail').val(email);
        $('#inputwhatsapp').val(whatsapp);
        $('#inputadresse').val(adresse);
        $('#inputfacebook').val(facebook);
        $('#inputinstagram').val(instagram);

        if (logo) {
            $('#logoPreview').attr('src', logo).show();
            $('#ancienLogo').val(logoFilename);
        } else {
            $('#logoPreview').hide();
            $('#ancienLogo').val('');
        }

        $('#exampleDangerModal1').modal('show');
    });
})();
