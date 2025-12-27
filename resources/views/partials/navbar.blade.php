<!-- Top Navbar -->
<script src="https://cdn.jsdelivr.net/npm/feather-icons/dist/feather.min.js"></script>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css" />

<!-- Enhanced Dropdown Styles -->
<style>
.dropdown-menu.show {
    display: block !important;
    opacity: 1 !important;
    transform: translateY(0) !important;
}

.dropdown-menu {
    position: absolute !important;
    top: 100% !important;
    right: 0 !important;
    z-index: 1050 !important;
    min-width: 200px;
    max-width: 250px;
    background-color: #fff;
    border: 1px solid rgba(0,0,0,.15);
    border-radius: 0.375rem;
    box-shadow: 0 0.5rem 1rem rgba(0,0,0,.175);
    opacity: 0;
    transform: translateY(-10px);
    transition: all 0.2s ease-in-out;
    display: none;
    margin-top: 0.125rem;
}

.dropdown-item {
    display: block;
    width: 100%;
    padding: 0.5rem 1rem;
    clear: both;
    font-weight: 400;
    color: #212529;
    text-align: inherit;
    text-decoration: none;
    white-space: nowrap;
    background-color: transparent;
    border: 0;
    transition: all 0.15s ease-in-out;
}

.dropdown-item:hover,
.dropdown-item:focus {
    background-color: #f8f9fa;
    color: #16181b;
    text-decoration: none;
}

.dropdown-item.text-danger:hover {
    background-color: #f8d7da;
    color: #721c24;
}

.dropdown-header {
    display: block;
    padding: 0.5rem 1rem;
    margin-bottom: 0;
    font-size: 0.875rem;
    color: #6c757d;
    white-space: nowrap;
}

.dropdown-divider {
    height: 0;
    margin: 0.5rem 0;
    overflow: hidden;
    border-top: 1px solid #dee2e6;
}

.dropdown-icon {
    margin-right: 0.5rem;
    width: 16px;
    text-align: center;
}

/* Profile dropdown specific styles */
.dropdown-authentication .nav-link {
    cursor: pointer;
    transition: all 0.2s ease;
}

.dropdown-authentication .nav-link:hover {
    background-color: rgba(255,255,255,0.1);
    border-radius: 0.25rem;
}

/* Avatar styles */
.avatar-img {
    object-fit: cover;
}

/* Responsive dropdown positioning */
.dropdown-menu-end {
    right: 0 !important;
    left: auto !important;
}

/* Auto-adjust dropdown position to prevent overflow */
.dropdown-menu {
    transform-origin: top right;
}

@media (max-width: 768px) {
    .dropdown-menu {
        min-width: 180px;
        max-width: 200px;
        right: 0 !important;
        left: auto !important;
        transform-origin: top right;
    }
    
    .dropdown-header {
        padding: 0.4rem 0.8rem;
        font-size: 0.8rem;
    }
    
    .dropdown-item {
        padding: 0.4rem 0.8rem;
        font-size: 0.85rem;
    }
}

@media (max-width: 480px) {
    .dropdown-menu {
        min-width: 160px;
        max-width: 180px;
        font-size: 0.8rem;
    }
}
</style>

<nav class="navbar navbar-expand-xl navbar-light fixed-top hk-navbar">
    {{-- <a id="navbar_toggle_btn" class="navbar-toggle-btn nav-link-hover" href="javascript:void(0);">
        <span class="feather-icon"><i data-feather="menu"></i></span>
    </a> --}}
    <a class="navbar-brand" href="{{ route('admin.dashboard') }}">
        <img class="brand-img d-inline-block" src="{{ asset('assets/logo.png') }}" alt="brand logo"
            style="height:50px;">
    </a>
    <ul class="navbar-nav hk-navbar-content">
        {{-- <li class="nav-item">
            <a id="navbar_search_btn" class="nav-link nav-link-hover" href="javascript:void(0);"><span
                    class="feather-icon"><i data-feather="search"></i></span></a>
        </li>
        <li class="nav-item">
            <a id="settings_toggle_btn" class="nav-link nav-link-hover" href="javascript:void(0);"><span
                    class="feather-icon"><i data-feather="settings"></i></span></a>
        </li> --}}
        {{-- <li class="nav-item dropdown dropdown-notifications">
            <a class="nav-link dropdown-toggle no-caret" href="#" role="button" data-toggle="dropdown"
                aria-haspopup="true" aria-expanded="false"><span class="feather-icon"><i
                        data-feather="bell"></i></span><span class="badge-wrap"><span
                        class="badge badge-primary badge-indicator badge-indicator-sm badge-pill pulse"></span></span></a>
            <div class="dropdown-menu dropdown-menu-right" data-dropdown-in="fadeIn" data-dropdown-out="fadeOut">
                <h6 class="dropdown-header">Notifications <a href="javascript:void(0);" class="">View all</a></h6>
                <div class="notifications-nicescroll-bar">
                    <a href="javascript:void(0);" class="dropdown-item">
                        <div class="media">
                            <div class="media-img-wrap">
                                <div class="avatar avatar-sm">
                                    <img src="dist/img/avatar1.jpg" alt="user" class="avatar-img rounded-circle">
                                </div>
                            </div>
                            <div class="media-body">
                                <div>
                                    <div class="notifications-text"><span class="text-dark text-capitalize">Evie
                                            Ono</span> accepted your invitation to join the team</div>
                                    <div class="notifications-time">12m</div>
                                </div>
                            </div>
                        </div>
                    </a>
                    <div class="dropdown-divider"></div>
                    <a href="javascript:void(0);" class="dropdown-item">
                        <div class="media">
                            <div class="media-img-wrap">
                                <div class="avatar avatar-sm">
                                    <img src="dist/img/avatar2.jpg" alt="user" class="avatar-img rounded-circle">
                                </div>
                            </div>
                            <div class="media-body">
                                <div>
                                    <div class="notifications-text">New message received from <span
                                            class="text-dark text-capitalize">Misuko Heid</span></div>
                                    <div class="notifications-time">1h</div>
                                </div>
                            </div>
                        </div>
                    </a>
                    <div class="dropdown-divider"></div>
                    <a href="javascript:void(0);" class="dropdown-item">
                        <div class="media">
                            <div class="media-img-wrap">
                                <div class="avatar avatar-sm">
                                    <span class="avatar-text avatar-text-primary rounded-circle">
                                        <span class="initial-wrap"><span><i
                                                    class="zmdi zmdi-account font-18"></i></span></span>
                                    </span>
                                </div>
                            </div>
                            <div class="media-body">
                                <div>
                                    <div class="notifications-text">You have a follow up with<span
                                            class="text-dark text-capitalize"> Mintos head</span> on <span
                                            class="text-dark text-capitalize">friday, dec 19</span> at <span
                                            class="text-dark">10.00 am</span></div>
                                    <div class="notifications-time">2d</div>
                                </div>
                            </div>
                        </div>
                    </a>
                    <div class="dropdown-divider"></div>
                    <a href="javascript:void(0);" class="dropdown-item">
                        <div class="media">
                            <div class="media-img-wrap">
                                <div class="avatar avatar-sm">
                                    <span class="avatar-text avatar-text-success rounded-circle">
                                        <span class="initial-wrap"><span>A</span></span>
                                    </span>
                                </div>
                            </div>
                            <div class="media-body">
                                <div>
                                    <div class="notifications-text">Application of <span
                                            class="text-dark text-capitalize">Sarah Williams</span> is waiting for your
                                        approval</div>
                                    <div class="notifications-time">1w</div>
                                </div>
                            </div>
                        </div>
                    </a>
                    <div class="dropdown-divider"></div>
                    <a href="javascript:void(0);" class="dropdown-item">
                        <div class="media">
                            <div class="media-img-wrap">
                                <div class="avatar avatar-sm">
                                    <span class="avatar-text avatar-text-warning rounded-circle">
                                        <span class="initial-wrap"><span><i
                                                    class="zmdi zmdi-notifications font-18"></i></span></span>
                                    </span>
                                </div>
                            </div>
                            <div class="media-body">
                                <div>
                                    <div class="notifications-text">Last 2 days left for the project</div>
                                    <div class="notifications-time">15d</div>
                                </div>
                            </div>
                        </div>
                    </a>
                </div>
            </div>
        </li> --}}
        <li class="nav-item dropdown dropdown-authentication">
            <a class="nav-link dropdown-toggle no-caret" href="#" role="button" id="profileDropdown"
                aria-haspopup="true" aria-expanded="false">
                <div class="media">
                    <div class="media-img-wrap">
                        <div class="avatar">
                            @if(Auth::check() && Auth::user()->profile_image)
                                <img src="{{ asset('storage/avatars/' . Auth::user()->profile_image) }}" alt="user"
                                    class="avatar-img rounded-circle">
                            @elseif(Auth::check())
                                <div class="avatar-img rounded-circle bg-primary d-flex align-items-center justify-content-center text-white"
                                     style="font-size: 16px; font-weight: bold;">
                                    {{ strtoupper(substr(Auth::user()->f_name, 0, 1)) }}{{ strtoupper(substr(Auth::user()->l_name, 0, 1)) }}
                                </div>
                            @else
                                <img src="{{ asset('dist/img/avatar12.jpg') }}" alt="user"
                                    class="avatar-img rounded-circle">
                            @endif
                        </div>
                        <span class="badge badge-success badge-indicator"></span>
                    </div>
                    <div class="media-body">
                        @if(Auth::check())
                        <span>{{ Auth::user()->f_name }} <i class="fa fa-chevron-down"></i></span>
                        @else
                        <span>Guest <i class="fa fa-chevron-down"></i></span>
                        @endif
                    </div>
                </div>
            </a>
            <div class="dropdown-menu dropdown-menu-end" id="profileDropdownMenu">
                @if(Auth::check())
                    <div class="dropdown-header">
                        <div class="d-flex align-items-center">
                            <div class="me-2">
                                @if(Auth::user()->profile_image)
                                    <img src="{{ asset('storage/avatars/' . Auth::user()->profile_image) }}" alt="user"
                                        class="rounded-circle" width="32" height="32">
                                @else
                                    <div class="rounded-circle bg-primary d-flex align-items-center justify-content-center text-white"
                                         style="width: 32px; height: 32px; font-size: 12px; font-weight: bold;">
                                        {{ strtoupper(substr(Auth::user()->f_name, 0, 1)) }}{{ strtoupper(substr(Auth::user()->l_name, 0, 1)) }}
                                    </div>
                                @endif
                            </div>
                            <div>
                                <div class="fw-bold">{{ Auth::user()->f_name }} {{ Auth::user()->l_name }}</div>
                                <small class="text-muted">{{ Auth::user()->email }}</small>
                            </div>
                        </div>
                    </div>
                    <div class="dropdown-divider"></div>
                    <a class="dropdown-item" href="{{ route('profile.show') }}">
                        <i class="dropdown-icon fa fa-user"></i><span>My Profile</span>
                    </a>
                    <a class="dropdown-item" href="{{ route('admin.dashboard') }}">
                        <i class="dropdown-icon fa fa-tachometer-alt"></i><span>Dashboard</span>
                    </a>
                    <div class="dropdown-divider"></div>
                    <a class="dropdown-item text-danger" href="#" id="logoutBtn">
                        <i class="dropdown-icon fa fa-sign-out-alt"></i><span>Log Out</span>
                    </a>
                @else
                    <a class="dropdown-item" href="{{ route('login') }}">
                        <i class="dropdown-icon fa fa-sign-in-alt"></i><span>Login</span>
                    </a>
                @endif
            </div>
            
            @if(Auth::check())
                <form id="logout-form" action="{{ route('logout') }}" method="POST" style="display: none;">
                    @csrf
                </form>
            @endif
        </li>
    </ul>
</nav>

<!-- Enhanced Dropdown & Logout Functionality -->
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Profile dropdown functionality
    const profileDropdown = document.getElementById('profileDropdown');
    const profileDropdownMenu = document.getElementById('profileDropdownMenu');
    const logoutBtn = document.getElementById('logoutBtn');
    const logoutForm = document.getElementById('logout-form');
    
    if (profileDropdown && profileDropdownMenu) {
        // Toggle dropdown on click
        profileDropdown.addEventListener('click', function(e) {
            e.preventDefault();
            e.stopPropagation();
            
            // Close all other dropdowns first
            document.querySelectorAll('.dropdown-menu').forEach(function(menu) {
                if (menu !== profileDropdownMenu) {
                    menu.classList.remove('show');
                }
            });
            
            // Toggle this dropdown
            profileDropdownMenu.classList.toggle('show');
            
            // Adjust position to prevent overflow
            if (profileDropdownMenu.classList.contains('show')) {
                adjustDropdownPosition(profileDropdownMenu);
            }
        });
        
        // Prevent dropdown from closing when clicking inside it
        profileDropdownMenu.addEventListener('click', function(e) {
            e.stopPropagation();
        });
    }
    
    // Logout functionality
    if (logoutBtn && logoutForm) {
        logoutBtn.addEventListener('click', function(e) {
            e.preventDefault();
            e.stopPropagation();
            
            // Show confirmation
            if (confirm('Are you sure you want to log out?')) {
                logoutForm.submit();
            }
        });
    }
    
    // Close dropdowns when clicking outside
    document.addEventListener('click', function(e) {
        if (!e.target.closest('.dropdown')) {
            document.querySelectorAll('.dropdown-menu.show').forEach(function(menu) {
                menu.classList.remove('show');
            });
        }
    });
    
    // Close dropdowns on escape key
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') {
            document.querySelectorAll('.dropdown-menu.show').forEach(function(menu) {
                menu.classList.remove('show');
            });
        }
    });
    
    // Function to adjust dropdown position to prevent overflow
    function adjustDropdownPosition(dropdown) {
        if (!dropdown) return;
        
        // Reset any previous adjustments
        dropdown.style.right = '0';
        dropdown.style.left = 'auto';
        dropdown.style.transform = 'translateY(0)';
        
        // Get dropdown and viewport dimensions
        const rect = dropdown.getBoundingClientRect();
        const viewportWidth = window.innerWidth;
        const viewportHeight = window.innerHeight;
        
        // Check if dropdown overflows right edge
        if (rect.right > viewportWidth) {
            const overflow = rect.right - viewportWidth;
            dropdown.style.right = (overflow + 10) + 'px'; // 10px padding from edge
        }
        
        // Check if dropdown overflows left edge
        if (rect.left < 0) {
            dropdown.style.right = 'auto';
            dropdown.style.left = '10px'; // 10px padding from edge
        }
        
        // Check if dropdown overflows bottom edge
        if (rect.bottom > viewportHeight) {
            const availableSpaceAbove = rect.top;
            const dropdownHeight = rect.height;
            
            if (availableSpaceAbove > dropdownHeight) {
                // Show above the trigger
                dropdown.style.top = 'auto';
                dropdown.style.bottom = '100%';
                dropdown.style.marginBottom = '0.125rem';
                dropdown.style.marginTop = '0';
            }
        }
    }
    
    // Adjust dropdown position on window resize
    window.addEventListener('resize', function() {
        const visibleDropdown = document.querySelector('.dropdown-menu.show');
        if (visibleDropdown) {
            adjustDropdownPosition(visibleDropdown);
        }
    });
    
    // Initialize Bootstrap dropdowns as fallback
    try {
        if (typeof bootstrap !== 'undefined' && bootstrap.Dropdown) {
            var dropdownElementList = [].slice.call(document.querySelectorAll('.dropdown-toggle'));
            var dropdownList = dropdownElementList.map(function (dropdownToggleEl) {
                return new bootstrap.Dropdown(dropdownToggleEl);
            });
        }
    } catch (error) {
        console.log('Bootstrap dropdown initialization failed, using custom implementation');
    }
});
</script>

<!-- <form role="search" class="navbar-search">
    <div class="position-relative">
        <a href="javascript:void(0);" class="navbar-search-icon"><span class="feather-icon"><i
                    data-feather="search"></i></span></a>
        <input type="text" name="example-input1-group2" class="form-control" placeholder="Type here to Search">
        <a id="navbar_search_close" class="navbar-search-close" href="#"><span class="feather-icon"><i
                    data-feather="x"></i></span></a>
    </div>
</form> -->
<!-- /Top Navbar -->