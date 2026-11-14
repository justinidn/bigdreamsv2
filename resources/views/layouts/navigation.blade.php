<nav class="navbar sm:navbar-expand bg-1 fg-2">
    <div class="container-fluid">
        <a class="navbar-brand" href="{{ route('dashboard') }}">{{ config('app.name', 'Big Dreams') }}</a>

        <button class="btn-icon navbar-toggler" type="button" data-bs-toggle="drawer" data-bs-target="#navbarDarkDrawer" aria-controls="navbarDarkDrawer" aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon" aria-hidden="true"></span>
        </button>

        <dialog class="drawer drawer-end" tabindex="-1" id="navbarDarkDrawer" aria-labelledby="navbarDarkDrawerLabel">
            <div class="drawer-header">
                <h5 class="drawer-title" id="navbarDarkDrawerLabel">Menu</h5>
                <button type="button" class="btn-close" data-bs-dismiss="drawer" aria-label="Close"></button>
            </div>

            <div class="drawer-body mb-2 md:mb-0">
                <ul class="nav navbar-nav me-auto">
                    <li class="nav-item">
                        <a class="nav-link" href="{{ route('dashboard') }}">
                            Dashboard
                        </a>
                    </li>
                    @canany(['master-menus.view', 'roles.view', 'user-roles.view', 'user-permissions.view'])
                    <li class="nav-item">
                        <button class="nav-link" type="button" data-bs-toggle="menu" aria-expanded="false">
                            Sites Settings
                        </button>
                        <div class="menu">
                            @can('master-menus.view')
                            <a class="menu-item" href="{{ route('master-menus.index') }}">
                                Master Menus
                            </a>
                            @endcan
                            @can('roles.view')
                            <a class="menu-item" href="{{ route('roles.index') }}">
                                Roles
                            </a>
                            @endcan
                            @can('user-roles.view')
                            <a class="menu-item" href="{{ route('user-roles.index') }}">
                                User Roles
                            </a>
                            @endcan
                            @can('user-permissions.view')
                            <a class="menu-item" href="{{ route('user-permissions.index') }}">
                                User Permissions
                            </a>
                            @endcan

                        </div>
                    </li>
                    @endcanany
                    @canany(['users.view', 'companies.view', 'transaction-hourlies.view', 'transaction-dailies.view'])
                    <li class="nav-item">
                        <button class="nav-link" type="button" data-bs-toggle="menu" aria-expanded="false">
                            Master
                        </button>
                        <div class="menu">
                            @can('users.view')
                            <a class="menu-item" href="{{ route('users.index') }}">
                                Users
                            </a>
                            @endcan
                            @can('companies.view')

                            <a class="menu-item" href="{{ route('companies.index') }}">
                                Company
                            </a>
                            @endcan
                            @endcanany

                        </div>

                    </li>
                    <li class="nav-item">
                        <button class="nav-link" type="button" data-bs-toggle="menu" aria-expanded="false">
                            Transaction
                        </button>
                        <div class="menu">
                            <a class="menu-item" href="{{ route('transaction-hourlies.index') }}">
                                Hourly
                            </a>
                            <a class="menu-item" href="{{ route('transaction-dailies.index') }}">
                                Daily
                            </a>
                        </div>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="{{ route('screeners.index') }}">
                            Screeners
                        </a>
                    </li>
                </ul>

                <ul class="nav navbar-nav ms-auto">
                    <li class="nav-item">
                        <button class="nav-link" type="button" data-bs-toggle="menu" aria-expanded="false">
                            <i class="bi bi-person"></i> {{ Auth::user()->name }}
                        </button>
                        <div class="menu">
                            <a class="menu-item" href="{{ route('profile.edit') }}">Profile</a>

                            <hr class="menu-divider">

                            <form method="POST" action="{{ route('logout') }}">
                                @csrf
                                <a class="menu-item text-danger" href="{{ route('logout') }}" onclick="event.preventDefault(); this.closest('form').submit();">
                                    Log Out
                                </a>
                            </form>
                        </div>
                    </li>
                </ul>
            </div>
        </dialog>
    </div>
</nav>