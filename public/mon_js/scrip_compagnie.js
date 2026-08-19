// $(document).ready(function() {
//     $('.edit-btn').click(function(e) {
//         e.preventDefault();

//         var idCompagnie = $(this).data('id_compagnie');
//         var nomCompagnie = $(this).data('nom_compagnie');
//         var libele = $(this).data('libele');
//         var slogant = $(this).data('slogant');

//         $('#inputidCompagnie').val(idCompagnie);
//         $('#inputnomCompagnie').val(nomCompagnie);
//         $('#inputlibele').val(libele);
//         $('#inputslogant').val(slogant);

//         $('#exampleDangerModal1').modal('show');
//     });
// });
$('.edit-btn').click(function (e) {
    e.preventDefault();

    let id       = $(this).data('id_compagnie');
    let nom      = $(this).data('nom_compagnie');
    let libele   = $(this).data('libele');
    let slogant  = $(this).data('slogant');
    let logo     = $(this).data('logo'); // full URL for preview
    let logoFilename = $(this).data('logofilename'); // just the filename

    $('#inputidCompagnie').val(id);
    $('#inputnomCompagnie').val(nom);
    $('#inputlibele').val(libele);
    $('#inputslogant').val(slogant);

    if (logo) {
        $('#logoPreview').attr('src', logo).show();
        $('#ancienLogo').val(logoFilename);
    } else {
        $('#logoPreview').hide();
        $('#ancienLogo').val('');
    }

    $('#exampleDangerModal1').modal('show');
});
