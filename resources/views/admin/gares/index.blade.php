@extends('layouts.admin')

@php $authUser = auth('staff')->user(); @endphp

@section('title', 'Gares · TransHub Admin')
@section('breadcrumb-title', 'Configuration')
@section('breadcrumb-active', 'Gares')

@section('breadcrumb-actions')
    @if ($authUser->droit !== 'PDG')
        <button type="button" id="btnOuvrirAjouterGare" class="btn btn-success d-flex align-items-center gap-2 shadow-sm" data-bs-toggle="modal" data-bs-target="#modalAjouterGare">
            <i class="bx bx-plus-circle fs-5"></i> Ajouter
        </button>
    @endif
@endsection

@section('content')
<div class="row">
    @include('admin.partials.config-nav', ['active' => 'gares'])

    <div class="col-12 col-xxl-9">
        @include('admin.partials.set_flash')
        <div class="card config-card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="mb-0 fw-bold"><i class="bx bx-buildings me-2"></i>Liste des gares</h5>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table id="example" class="table table-striped table-bordered table-hover-effect table-custom-header text-center mobile-card-table" style="width:100%">
                        <thead class="table-light text-center">
                            <tr>
                                <th>N gare</th>
                                <th>Localite</th>
                                <th>Code marchant</th>
                                <th>Tel</th>
                                <th>Statut</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($listes as $liste)
                                <tr>
                                    <td data-label="N gare">{{ $liste->numeroGare }}</td>
                                    <td data-label="Localite">{{ $liste->localite }}</td>
                                    <td data-label="Code marchant">{{ $liste->code }}</td>
                                    <td data-label="Tel">{{ $liste->tel }}</td>
                                    <td data-label="Statut">
                                        @if (isset($liste->status) && $liste->status == 0)
                                            <span class="badge bg-danger">Suspendue</span>
                                        @else
                                            <span class="badge bg-success">Active</span>
                                        @endif
                                    </td>
                                    <td data-label="Action">
                                        <div class="dropup text-center">
                                            <a href="#" class="text-dark text-decoration-none fs-4" data-bs-toggle="dropdown" aria-expanded="false">
                                                &#8943;
                                            </a>
                                            <div class="dropdown-menu dropdown-menu">
                                                @if ($authUser->droit !== 'PDG')
                                                    <a class="dropdown-item edit-btn"
                                                        data-bs-toggle="modal" data-bs-target="#exampleDangerModal1"
                                                        data-numeros="{{ $liste->idAgence }}"
                                                        data-numero="{{ $liste->numeroGare }}"
                                                        data-localite="{{ $liste->localite }}"
                                                        data-code="{{ $liste->code }}"
                                                        data-tel="{{ $liste->tel }}"
                                                        href="">Modifier</a>
                                                    <a class="dropdown-item" href="{{ url('/admin/Liste_gares/suspend/'.$liste->idAgence) }}">
                                                        {{ (isset($liste->status) && $liste->status == 0) ? 'Activer' : 'Suspendre' }}
                                                    </a>
                                                    <a class="dropdown-item text-danger delete-button" href="{{ url('/admin/Liste_gares/delete/'.$liste->idAgence) }}">Supprimer</a>
                                                @endif
                                            </div>
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('modals')
    <!--modal pour la modification-->
    <div class="modal fade" id="exampleDangerModal1" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header bg-primary">
                    <h5 class="modal-title text-white">Modification du gare</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body text-dart">
                    <form class="row g-3" method="post" action="{{ url('/admin/Liste_gares/edit') }}">
                        @csrf
                        <input type="hidden" name="idAgence" id="inputnumeros">
                        <div class="col-md-6">
                            <label for="tel" class="form-label">Numero du gare</label>
                            <input type="text" class="form-control" value="" name="numeroGare" id="inputnumero" placeholder="" required>
                        </div>
                        <div class="col-md-6">
                            <label for="tel" class="form-label">Localite</label>
                            <input type="text" class="form-control" value="" name="localite" placeholder="" required id="inputlocalite">
                        </div>
                        <div class="col-md-6">
                            <label for="tel" class="form-label">Code marchant du gare</label>
                            <input type="text" class="form-control" value="" name="code" placeholder="" required id="inputcode">
                        </div>
                        <div class="col-md-6">
                            <label for="bsValidation10" class="form-label">Numero d'orange</label>
                            <input type="text" class="form-control" id="tel" maxlength="11" oninput="verifierNumero()" value="" name="tel" placeholder="" required>
                            <small id="messageErreur" class="text-danger"></small>
                        </div>
                        <div class="col-md-12">
                            <div class="d-md-flex d-grid align-items-center gap-3">
                                @if ($authUser->droit !== 'PDG')
                                    <button type="submit" name="edit" class="btn btn-primary px-4">Modifier</button>
                                @endif
                                <a href="" class="btn btn-info">Annuler</a>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
    <!-- fin du modal de modification -->

    @php
        $lignesAffichees = ! empty($lignesEnErreur) ? $lignesEnErreur : [
            ['localite' => '', 'numeroGare' => '', 'code' => '', 'tel' => '', 'erreur' => null, 'champs_en_erreur' => []],
        ];
    @endphp
    <!--modal pour l'ajout de gares-->
    <div class="modal fade" id="modalAjouterGare" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header bg-primary">
                    <h5 class="modal-title text-white">Ajouter une gare</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form id="formGares" method="post" action="{{ url('/admin/Liste_gares/add_gares') }}" novalidate>
                        @csrf
                        <input type="hidden" name="enregistre" value="1">
                        @if ($authUser->isSuperAdmin())
                            <div class="mb-3">
                                <label class="form-label fw-semibold">Compagnie <span class="text-danger">*</span></label>
                                <select class="form-select" name="id_compagnie" required>
                                    <option value="" disabled selected>Choisissez une compagnie</option>
                                    @foreach ($listeCompagnie as $c)
                                        <option value="{{ $c->id_compagnie }}">{{ $c->nom_compagnie }}</option>
                                    @endforeach
                                </select>
                            </div>
                        @endif
                        <div id="garesRows">
                            @foreach ($lignesAffichees as $ligne)
                                @php $champsErreur = $ligne['champs_en_erreur'] ?? []; @endphp
                                <div class="row g-3 gare-row mb-3 pb-3 border-bottom">
                                    @if (! empty($ligne['erreur']))
                                        <div class="col-12">
                                            <div class="alert alert-danger py-2 px-3 mb-0 small">{{ $ligne['erreur'] }}</div>
                                        </div>
                                    @endif
                                    <div class="col-md-6">
                                        <label class="form-label fw-semibold">Localité <span class="text-danger">*</span></label>
                                        <input type="text" class="form-control {{ in_array('localite', $champsErreur, true) ? 'is-invalid' : '' }}" name="localite[]" placeholder="Ex: Ségou" required autocomplete="off" value="{{ $ligne['localite'] }}">
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label fw-semibold">Numéro de gare <span class="text-danger">*</span></label>
                                        <input type="text" class="form-control {{ in_array('numeroGare', $champsErreur, true) ? 'is-invalid' : '' }}" name="numeroGare[]" placeholder="Ex: Gare I" required autocomplete="off" value="{{ $ligne['numeroGare'] }}">
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label fw-semibold">Code marchand <span class="text-danger">*</span></label>
                                        <input type="text" class="form-control {{ in_array('code', $champsErreur, true) ? 'is-invalid' : '' }}" name="code[]" placeholder="Ex: 123489" required autocomplete="off" value="{{ $ligne['code'] }}">
                                    </div>
                                    <div class="col-md-5">
                                        <label class="form-label fw-semibold">Numéro Orange (Mobile Money) <span class="text-danger">*</span></label>
                                        <input type="text" class="form-control gare-tel {{ in_array('tel', $champsErreur, true) ? 'is-invalid' : '' }}" maxlength="11" name="tel[]" placeholder="Ex: 78907812" required autocomplete="off" value="{{ $ligne['tel'] }}">
                                        <small class="text-danger mt-1 d-block gare-tel-error"></small>
                                    </div>
                                    <div class="col-md-1 d-flex align-items-start justify-content-end">
                                        <button type="button" class="btn btn-outline-danger remove-row-btn mt-4 {{ count($lignesAffichees) <= 1 ? 'd-none' : '' }}" title="Retirer cette ligne">
                                            <i class="bx bx-trash"></i>
                                        </button>
                                    </div>
                                </div>
                            @endforeach
                        </div>

                        <button type="button" id="addGareRow" class="btn btn-sm btn-outline-primary mb-3">
                            <i class="bx bx-plus"></i> Ajouter une ligne
                        </button>

                        <div class="modal-footer border-0 px-0">
                            <button type="button" class="btn btn-light" data-bs-dismiss="modal">Annuler</button>
                            @if ($authUser->droit !== 'PDG')
                                <button class="btn btn-primary fw-semibold d-flex align-items-center" type="submit" id="submitGareBtn">
                                    <span class="spinner-border spinner-border-sm me-2 d-none" id="submitGareSpinner" role="status" aria-hidden="true"></span>
                                    <i class="bx bx-save fs-5 me-2" id="submitGareIcon"></i> Enregistrer
                                </button>
                            @endif
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
    <!-- fin du modal d'ajout -->
@endsection

@section('scripts')
    <script src="{{ asset('mon_js/scrip_agence.js') }}"></script>
    <script src="{{ asset('mon_js/alert_delete.js') }}"></script>
    <script>
        document.addEventListener("DOMContentLoaded", function() {
            const modalEl = document.getElementById("modalAjouterGare");
            const ouvrirBtn = document.getElementById("btnOuvrirAjouterGare");
            const form = document.getElementById("formGares");
            const rowsContainer = document.getElementById("garesRows");
            const addBtn = document.getElementById("addGareRow");
            const submitBtn = document.getElementById("submitGareBtn");
            const submitSpinner = document.getElementById("submitGareSpinner");
            const submitIcon = document.getElementById("submitGareIcon");

            let backdropEl = null;
            function ouvrirModal() {
                modalEl.classList.add("show");
                modalEl.style.display = "block";
                modalEl.removeAttribute("aria-hidden");
                modalEl.setAttribute("aria-modal", "true");
                document.body.classList.add("modal-open");
                if (!backdropEl) {
                    backdropEl = document.createElement("div");
                    backdropEl.className = "modal-backdrop fade show";
                    document.body.appendChild(backdropEl);
                }
            }
            function fermerModal() {
                modalEl.classList.remove("show");
                modalEl.style.display = "none";
                modalEl.setAttribute("aria-hidden", "true");
                modalEl.removeAttribute("aria-modal");
                document.body.classList.remove("modal-open");
                if (backdropEl) {
                    backdropEl.remove();
                    backdropEl = null;
                }
            }

            if (ouvrirBtn) {
                ouvrirBtn.addEventListener("click", ouvrirModal);
            }
            modalEl.querySelectorAll('[data-bs-dismiss="modal"]').forEach(function(btn) {
                btn.addEventListener("click", fermerModal);
            });
            modalEl.addEventListener("click", function(e) {
                if (e.target === modalEl) {
                    fermerModal();
                }
            });

            function toggleRemoveButtons() {
                const rows = rowsContainer.querySelectorAll(".gare-row");
                rows.forEach(function(row) {
                    row.querySelector(".remove-row-btn").classList.toggle("d-none", rows.length <= 1);
                });
            }

            function formaterNumero(numero) {
                numero = numero.replace(/\D/g, '');
                if (numero.length > 8) {
                    numero = numero.substring(0, 8);
                }
                return numero.replace(/(\d{2})(?=\d)/g, '$1 ');
            }

            function verifierNumeroRow(input) {
                const erreur = input.closest(".gare-row").querySelector(".gare-tel-error");
                const numero = input.value.replace(/\D/g, '');
                input.value = formaterNumero(numero);

                let valide = true;
                let message = "";
                if (/^[1234]/.test(numero)) {
                    message = "Le numéro ne doit pas commencer par 1, 2, 3 ou 4.";
                    valide = false;
                } else if (numero.length !== 8) {
                    message = "Le numéro doit contenir exactement 8 chiffres.";
                    valide = false;
                }

                erreur.textContent = message;
                input.classList.toggle("is-invalid", !valide);
                return valide;
            }

            rowsContainer.addEventListener("input", function(e) {
                if (!e.target.matches("input")) {
                    return;
                }
                if (e.target.classList.contains("gare-tel")) {
                    verifierNumeroRow(e.target);
                } else if (e.target.value.trim() !== "") {
                    e.target.classList.remove("is-invalid");
                }
            });

            addBtn.addEventListener("click", function() {
                const firstRow = rowsContainer.querySelector(".gare-row");
                const newRow = firstRow.cloneNode(true);
                newRow.querySelectorAll("input").forEach(function(input) {
                    input.value = "";
                    input.classList.remove("is-invalid");
                });
                newRow.querySelector(".gare-tel-error").textContent = "";
                const alerte = newRow.querySelector(".alert-danger");
                if (alerte) {
                    alerte.closest(".col-12").remove();
                }
                rowsContainer.appendChild(newRow);
                toggleRemoveButtons();
            });

            rowsContainer.addEventListener("click", function(e) {
                const btn = e.target.closest(".remove-row-btn");
                if (btn) {
                    btn.closest(".gare-row").remove();
                    toggleRemoveButtons();
                }
            });

            function validerFormulaire() {
                let valide = true;
                let premierChampInvalide = null;

                rowsContainer.querySelectorAll(".gare-row").forEach(function(row) {
                    row.querySelectorAll("input[required]").forEach(function(input) {
                        const vide = input.value.trim() === "";
                        input.classList.toggle("is-invalid", vide);
                        if (vide) {
                            valide = false;
                            premierChampInvalide = premierChampInvalide || input;
                        }
                    });

                    const telInput = row.querySelector(".gare-tel");
                    if (telInput.value.trim() !== "" && !verifierNumeroRow(telInput)) {
                        valide = false;
                        premierChampInvalide = premierChampInvalide || telInput;
                    }
                });

                if (!valide && premierChampInvalide) {
                    premierChampInvalide.scrollIntoView({ behavior: "smooth", block: "center" });
                    premierChampInvalide.focus();
                }

                return valide;
            }

            form.addEventListener("submit", function(e) {
                if (!validerFormulaire()) {
                    e.preventDefault();
                    return;
                }
                submitBtn.disabled = true;
                submitSpinner.classList.remove("d-none");
                submitIcon.classList.add("d-none");
            });

            @if (! empty($lignesEnErreur))
            ouvrirModal();
            @endif
        });
    </script>
@endsection
