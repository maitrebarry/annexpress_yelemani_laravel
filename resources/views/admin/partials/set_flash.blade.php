@if (session('notification'))
    @php $notification = session('notification'); @endphp
    {{-- tgReady (pas DOMContentLoaded, qui ne se redéclenche jamais après la première
         navigation — voir mon_js/admin-transitions.js) : c'est le correctif à plus forte
         valeur du portage, sans lui aucune action admin (créer/modifier/supprimer...)
         n'afficherait plus jamais ce toast dès qu'elle est atteinte via une navigation
         douce, alors que la quasi-totalité des actions de l'admin se terminent par une
         redirection avec ce message flash. --}}
    <script>
        tgReady(function () {
            Swal.fire({
                toast: true,
                position: 'top-end',
                icon: @json($notification['swal_icon'] ?? 'info'),
                html: @json($notification['message']),
                showConfirmButton: false,
                timer: 4500,
                timerProgressBar: true,
                showClass: {
                    popup: 'animate__animated animate__fadeInDown animate__faster'
                },
                hideClass: {
                    popup: 'animate__animated animate__fadeOutUp animate__faster'
                },
                didOpen: function (toast) {
                    toast.addEventListener('mouseenter', Swal.stopTimer);
                    toast.addEventListener('mouseleave', Swal.resumeTimer);
                }
            });
        });
    </script>
@endif
