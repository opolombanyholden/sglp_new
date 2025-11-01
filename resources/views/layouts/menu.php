@if(Route::has('admin.geolocalisation.provinces.index'))
<li class="nav-item">
    <a href="{{ route('admin.geolocalisation.provinces.index') }}" class="nav-link-custom {{ request()->routeIs('admin.geolocalisation.provinces.*') ? 'active' : '' }}">
        <i class="nav-icon fas fa-list"></i>
        <span class="nav-text">Liste des Provinces</span>
    </a>
</li>
@endif

@if(Route::has('admin.geolocalisation.departements.index'))
<li class="nav-item">
    <a href="{{ route('admin.geolocalisation.departements.index') }}" class="nav-link-custom {{ request()->routeIs('admin.geolocalisation.departements.*') ? 'active' : '' }}">
        <i class="nav-icon fas fa-list"></i>
        <span class="nav-text">Liste des Départements</span>
    </a>
</li>
@endif

@if(Route::has('admin.geolocalisation.communes.index'))
                                    <li class="nav-item">
                                        <a href="{{ route('admin.geolocalisation.communes.index') }}" class="nav-link-custom {{ request()->routeIs('admin.geolocalisation.communes.*') ? 'active' : '' }}">
                                            <i class="nav-icon fas fa-list"></i>
                                            <span class="nav-text">Liste Communes/Villes</span>
                                        </a>
</li>
@endif

                                    @if(Route::has('admin.geolocalisation.arrondissements.index'))
                                    <li class="nav-item">
                                        <a href="{{ route('admin.geolocalisation.arrondissements.index') }}" class="nav-link-custom {{ request()->routeIs('admin.geolocalisation.arrondissements.*') ? 'active' : '' }}">
                                            <i class="nav-icon fas fa-list"></i>
                                            <span class="nav-text">Liste Arrondissements</span>
                                        </a>
                                    </li>
                                    @endif

                                    @if(Route::has('admin.geolocalisation.cantons.index'))
                                    <li class="nav-item">
                                        <a href="{{ route('admin.geolocalisation.cantons.index') }}" class="nav-link-custom {{ request()->routeIs('admin.geolocalisation.cantons.*') ? 'active' : '' }}">
                                            <i class="nav-icon fas fa-list"></i>
                                            <span class="nav-text">Liste des Cantons</span>
                                        </a>
                                    </li>
                                    @endif

                                    @if(Route::has('admin.geolocalisation.regroupements.index'))
                                    <li class="nav-item">
                                        <a href="{{ route('admin.geolocalisation.regroupements.index') }}" class="nav-link-custom {{ request()->routeIs('admin.geolocalisation.regroupements.*') ? 'active' : '' }}">
                                            <i class="nav-icon fas fa-list"></i>
                                            <span class="nav-text">Liste Regroupements</span>
                                        </a>
                                    </li>
                                    @endif

                                    @if(Route::has('admin.geolocalisation.localites.index'))
                                    <li class="nav-item">
                                        <a href="{{ route('admin.geolocalisation.localites.index') }}" class="nav-link-custom {{ request()->routeIs('admin.geolocalisation.localites.*') ? 'active' : '' }}">
                                            <i class="nav-icon fas fa-list"></i>
                                            <span class="nav-text">Toutes Localités</span>
                                        </a>
                                    </li>
                                    @endif