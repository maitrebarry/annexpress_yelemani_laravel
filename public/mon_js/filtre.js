  $(document).ready(function() {
            $('#selectheure, #id_destination').change(function() {
                const selectheure = $('#selectheure').val();
                const id_destination = $('#id_destination').val();

                if (selectheure && id_destination) {
                    $.ajax({
                        url: '<?= BASE_URL ?>/AjaxFiltreListe', // Contrôleur AJAX MVC
                        type: 'POST',
                        data: {
                            selectheure: selectheure,
                            id_destination: id_destination
                        },
                        dataType: 'json',
                        success: function(response) {
                            if (response.error) {
                                Swal.fire('Erreur', response.error, 'error');
                            } else {
                                $('#tableClient').html(response.tbody);
                            }
                        },
                        error: function(xhr, status, error) {
                            Swal.fire('Erreur AJAX', xhr.responseText, 'error');
                        }
                    });
                }
            });
        });