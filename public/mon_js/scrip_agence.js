$(document).ready(function() {
    $('.edit-btn').click(function(e) {
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
});
