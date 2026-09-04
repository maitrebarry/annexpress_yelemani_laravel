@extends('layouts.admin')

@section('title', "Assigner des permissions · Sirali Admin")
@section('breadcrumb-title', 'Configuration')
@section('breadcrumb-active', 'Utilisateur')

@section('content')
<div class="row">
    @include('admin.partials.config-nav', ['active' => 'utilisateur'])

    <div class="col-12 col-xxl-9">

        <div class="card config-card">
            <div class="card-header">
                <h5 class="mb-0"><i class="fas fa-lock-open me-2"></i>Assignation de permissions à :
                    <span style="color:#ea580c">{{ $utilisateur->utilisateurs ?? '' }}</span>
                </h5>
            </div>
            <div class="card-body">
                <form method="post" action="{{ url()->current() }}">
                    @csrf
                    @php
                        $groupes = [];
                        foreach ($allPermissions as $perm) {
                            $parts = explode('_', $perm->nom_permission, 2);
                            [$module, $action] = count($parts) === 2 ? $parts : ['autres', $parts[0]];
                            $groupes[$module][] = ['id' => $perm->id_permision, 'action' => $action, 'full_name' => $perm->nom_permission];
                        }
                        $moduleIcons = [
                            'Billets' => 'fas fa-ticket',
                            'colis' => 'fas fa-box-open',
                            'Configuration' => 'fas fa-gear',
                            'Programme' => 'fas fa-calendar',
                            'Rapport' => 'fas fa-chart-column',
                            'utilisateur' => 'fas fa-user',
                            'Caisse' => 'fas fa-wallet',
                            'Depenses' => 'fas fa-money-bill-wave',
                            'Location' => 'fas fa-car',
                        ];
                    @endphp

                    <div class="mb-3">
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" id="select_all_permissions">
                            <label class="form-check-label fw-bold" for="select_all_permissions">
                                Sélectionner/Désélectionner tout
                            </label>
                        </div>
                    </div>

                    <div class="row g-3">
                        @foreach ($groupes as $module => $permissions)
                            @php $moduleIndex = 'module_'.md5($module); @endphp
                            <div class="col-12 col-lg-6">
                                <div class="card permission-card h-100">
                                    <div class="card-header permission-module-header text-white d-flex justify-content-between align-items-center">
                                        <span><i class="{{ $moduleIcons[$module] ?? 'fas fa-key' }} me-2"></i>{{ ucfirst($module) }}</span>
                                        <label class="module-select-all d-flex align-items-center gap-1 mb-0" for="{{ $moduleIndex }}">
                                            <input class="form-check-input module-all-cb" type="checkbox" id="{{ $moduleIndex }}" data-module="{{ $moduleIndex }}">
                                            <span class="small">Tout</span>
                                        </label>
                                    </div>
                                    <div class="card-body">
                                        <div class="d-flex flex-wrap gap-3">
                                            @foreach ($permissions as $perm)
                                                @php
                                                    $permId = $perm['id'];
                                                    $permAction = ucfirst(str_replace('_', ' ', $perm['action']));
                                                    $checked = in_array($permId, $userPermissions) ? 'checked' : '';
                                                @endphp
                                                <div class="form-check me-3 mb-1">
                                                    <input class="form-check-input permission-checkbox" type="checkbox"
                                                        name="permissions[]" value="{{ $permId }}" id="perm_{{ $permId }}"
                                                        data-module="{{ $moduleIndex }}" {{ $checked }}>
                                                    <label class="form-check-label" for="perm_{{ $permId }}">{{ $permAction }}</label>
                                                </div>
                                            @endforeach
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>

                    <div class="text-end mt-4">
                        <button type="submit" class="btn btn-primary px-5"><i class="fas fa-check me-2"></i>Assigner les permissions</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
    <script>
        tgReady(function () {
            const globalSelectAll = document.getElementById('select_all_permissions');
            const allPermCb = document.querySelectorAll('.permission-checkbox');

            function syncModuleCb(moduleId) {
                const moduleCbs = document.querySelectorAll(`.permission-checkbox[data-module="${moduleId}"]`);
                const moduleAllCb = document.querySelector(`.module-all-cb[data-module="${moduleId}"]`);
                if (!moduleAllCb) return;
                const total = moduleCbs.length;
                const checked = [...moduleCbs].filter(c => c.checked).length;
                moduleAllCb.checked = (checked === total);
                moduleAllCb.indeterminate = (checked > 0 && checked < total);
            }

            function syncGlobal() {
                const total = allPermCb.length;
                const checked = [...allPermCb].filter(c => c.checked).length;
                globalSelectAll.checked = (checked === total);
                globalSelectAll.indeterminate = (checked > 0 && checked < total);
            }

            globalSelectAll.addEventListener('change', function() {
                allPermCb.forEach(cb => cb.checked = this.checked);
                document.querySelectorAll('.module-all-cb').forEach(cb => {
                    cb.checked = this.checked;
                    cb.indeterminate = false;
                });
            });

            document.querySelectorAll('.module-all-cb').forEach(moduleAllCb => {
                moduleAllCb.addEventListener('change', function() {
                    const moduleId = this.dataset.module;
                    document.querySelectorAll(`.permission-checkbox[data-module="${moduleId}"]`).forEach(cb => cb.checked = this.checked);
                    this.indeterminate = false;
                    syncGlobal();
                });
            });

            allPermCb.forEach(cb => {
                cb.addEventListener('change', function() {
                    syncModuleCb(this.dataset.module);
                    syncGlobal();
                });
            });

            syncGlobal();
            document.querySelectorAll('.module-all-cb').forEach(cb => syncModuleCb(cb.dataset.module));
        });
    </script>
@endsection
