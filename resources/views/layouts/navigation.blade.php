<nav class="navbar md:navbar-expand bg-1" data-bs-theme="dark">
    <div class="container-fluid">
        <a class="navbar-brand" href="{{ route('dashboard') }}">{{ config('app.name', 'Laravel') }}</a>

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
                    <li class="nav-item">
                        <button class="nav-link" type="button" data-bs-toggle="menu" aria-expanded="false">
                            Sites Settings
                        </button>
                        <div class="menu">
                            <a class="menu-item" href="{{ route('master-menus.index') }}">
                                Master Menus
                            </a>
                            <a class="menu-item" href="{{ route('roles.index') }}">
                                Roles
                            </a>
                            <a class="menu-item" href="{{ route('user-roles.index') }}">
                                User Roles
                            </a>
                            <a class="menu-item" href="{{ route('user-permissions.index') }}">
                                User Permissions
                            </a>

                        </div>
                    </li>
                    <li class="nav-item">
                        <button class="nav-link" type="button" data-bs-toggle="menu" aria-expanded="false">
                            Master
                        </button>
                        <div class="menu">
                            <a class="menu-item" href="{{ route('users.index') }}">
                                Users
                            </a>
                            <a class="menu-item" href="{{ route('departments.index') }}">
                                Department
                            </a>

                        </div>
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