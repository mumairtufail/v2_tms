<!-- Font Awesome Icons -->
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

<style>
    /* Sidebar Navigation Styling */
    .hk-nav {
        width: 260px;
        background: #fff;
        box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        position: fixed;
        height: 100vh;
        z-index: 1000;
        overflow: hidden;
    }
    
    /* Collapsed state */
    .hk-nav.hk-nav-mini {
        width: 70px;
    }
    
    .hk-nav.hk-nav-mini .nav-link-text {
        opacity: 0;
        visibility: hidden;
        transform: translateX(-10px);
    }
    
    .hk-nav.hk-nav-mini .nav-link {
        justify-content: center;
        padding: 12px 10px;
    }
    
    .hk-nav.hk-nav-mini .nav-icon {
        margin-right: 0;
    }
    
    /* Tooltip for collapsed state */
    .hk-nav.hk-nav-mini .nav-link {
        position: relative;
    }
    
    .hk-nav.hk-nav-mini .nav-link:hover::after {
        content: attr(data-tooltip);
        position: absolute;
        left: 100%;
        top: 50%;
        transform: translateY(-50%);
        background: #333;
        color: white;
        padding: 8px 12px;
        border-radius: 6px;
        white-space: nowrap;
        z-index: 1001;
        margin-left: 10px;
        font-size: 12px;
        opacity: 0;
        animation: tooltipFadeIn 0.2s ease forwards;
    }
    
    @keyframes tooltipFadeIn {
        to { opacity: 1; }
    }
    
    .nicescroll-bar {
        height: 100%;
        overflow-y: auto;
        overflow-x: hidden;
    }
    
    /* Prevent scrollbar flicker during transitions */
    .nicescroll-bar::-webkit-scrollbar {
        width: 6px;
        background: transparent;
    }
    
    .nicescroll-bar::-webkit-scrollbar-track {
        background: #f8f9fa;
        border-radius: 3px;
    }
    
    .nicescroll-bar::-webkit-scrollbar-thumb {
        background: #dee2e6;
        border-radius: 3px;
        transition: background 0.2s ease;
    }
    
    .nicescroll-bar::-webkit-scrollbar-thumb:hover {
        background: #adb5bd;
    }
    
    .navbar-nav-wrap {
        padding: 20px 0;
    }
    
    .nav-item {
        margin: 3px 15px;
        transition: margin 0.3s ease;
    }
    
    .hk-nav.hk-nav-mini .nav-item {
        margin: 3px 10px;
    }
    
    .nav-link {
        display: flex;
        align-items: center;
        padding: 12px 20px;
        color: #6c757d;
        text-decoration: none;
        border-radius: 8px;
        transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        font-size: 14px;
        font-weight: 500;
        position: relative;
        overflow: hidden;
    }
    
    .nav-link:hover {
        background-color: #f8f9fa;
        color: #495057;
        text-decoration: none;
        transform: translateX(2px);
    }
    
    .hk-nav.hk-nav-mini .nav-link:hover {
        transform: none;
    }
    
    .nav-link.active {
        background-color: #3A55B1 !important;
        color: #fff !important;
        box-shadow: 0 2px 8px rgba(58, 85, 177, 0.3);
    }
    
    .nav-link.active .nav-icon {
        color: #fff !important;
    }
    
    .nav-icon {
        display: flex;
        align-items: center;
        justify-content: center;
        width: 20px;
        height: 20px;
        margin-right: 15px;
        color: #6c757d;
        transition: all 0.3s ease;
        flex-shrink: 0;
    }
    
    .nav-icon i {
        font-size: 16px;
    }
    
    .nav-link:hover .nav-icon {
        color: #495057;
    }
    
    .nav-link-text {
        flex: 1;
        white-space: nowrap;
        transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        overflow: hidden;
    }
    
    .hk-nav-close {
        padding: 15px 20px;
        color: #6c757d;
        transition: all 0.3s ease;
        border: none;
        background: none;
        cursor: pointer;
        width: 100%;
        text-align: left;
        display: flex;
        align-items: center;
    }
    
    .hk-nav-close:hover {
        background-color: #f8f9fa;
        color: #495057;
    }
    
    .hk-nav.hk-nav-mini .hk-nav-close {
        padding: 15px 10px;
        justify-content: center;
    }
    
    .hk-nav.hk-nav-mini .hk-nav-close .nav-icon {
        margin-right: 0;
    }
    
    /* Main content area adjustment */
    .hk-wrapper[data-layout="vertical"] .hk-pg-wrapper {
        margin-left: 260px;
        transition: margin-left 0.3s cubic-bezier(0.4, 0, 0.2, 1);
    }
    
    .hk-wrapper[data-layout="vertical"].hk-nav-mini .hk-pg-wrapper {
        margin-left: 70px;
    }
    
    /* Responsive */
    @media (max-width: 1199px) {
        .hk-nav {
            transform: translateX(-100%);
            width: 260px;
        }
        
        .hk-nav.nav-show {
            transform: translateX(0);
        }
        
        .hk-wrapper[data-layout="vertical"] .hk-pg-wrapper {
            margin-left: 0;
        }
        
        .hk-wrapper[data-layout="vertical"].hk-nav-mini .hk-pg-wrapper {
            margin-left: 0;
        }
    }
    
    @media (max-width: 768px) {
        .nav-item {
            margin: 2px 10px;
        }
        
        .nav-link {
            padding: 10px 15px;
            font-size: 13px;
        }
        
        .nav-icon {
            margin-right: 12px;
            width: 18px;
            height: 18px;
        }
    }
    
    /* Backdrop */
    .hk-nav-backdrop {
        position: fixed;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background: rgba(0, 0, 0, 0.5);
        z-index: 999;
        opacity: 0;
        visibility: hidden;
        transition: all 0.3s ease;
    }
    
    .hk-nav-backdrop.show {
        opacity: 1;
        visibility: visible;
    }
</style>

<nav class="hk-nav hk-nav-light">
    <a href="javascript:void(0);" id="hk_nav_close" class="hk-nav-close">
        <span class="nav-icon"><i class="fas fa-times"></i></span>
    </a>
    <div class="nicescroll-bar">
        <div class="navbar-nav-wrap">
            <ul class="navbar-nav flex-column">
                <!-- Dashboard - Always show if user can view dashboard -->
                {{-- @canPermission('dashboard', 'view') --}}
                <li class="nav-item">
                    <a class="nav-link {{ request()->routeIs('company.dashboard') || request()->routeIs('admin.dashboard') ? 'active' : '' }}" 
                       href="{{ route('admin.dashboard') }}" 
                       data-tooltip="Dashboard">
                        <span class="nav-icon"><i class="fas fa-chart-line"></i></span>
                        <span class="nav-link-text">Dashboard</span>
                    </a>
                </li>
                {{-- @endcanPermission --}}

                <!-- Companies - Super Admin only for all companies, Company Admin for own company -->
                @isSuperAdmin
                <li class="nav-item">
                    <a class="nav-link {{ request()->routeIs('companies.*') ? 'active' : '' }}" 
                       href="{{ route('companies.index') }}" 
                       data-tooltip="All Companies">
                        <span class="nav-icon"><i class="fas fa-building"></i></span>
                        <span class="nav-link-text">All Companies</span>
                    </a>
                </li>
                @endisSuperAdmin

                @canPermission('companies', 'view')
                @if(!auth()->user()->is_super_admin)
                <li class="nav-item">
                    <a class="nav-link {{ request()->routeIs('companies.*') ? 'active' : '' }}" 
                       href="{{ route('companies.index') }}" 
                       data-tooltip="Company">
                        <span class="nav-icon"><i class="fas fa-building"></i></span>
                        <span class="nav-link-text">Company</span>
                    </a>
                </li>
                @endif
                @endcanPermission

                <!-- Users -->
                @canPermission('users', 'view')
                <li class="nav-item">
                    <a class="nav-link {{ request()->routeIs('users.*') || request()->is('users*') ? 'active' : '' }}" 
                       href="{{ route('users.index') }}" 
                       data-tooltip="Users">
                        <span class="nav-icon"><i class="fas fa-users"></i></span>
                        <span class="nav-link-text">Users</span>
                    </a>
                </li>
                @endcanPermission

                <!-- Roles & Permissions -->
                @canPermission('roles', 'view')
                <li class="nav-item">
                    <a class="nav-link {{ request()->routeIs('roles.*') || request()->is('roles*') ? 'active' : '' }}" 
                       href="{{ route('roles.index') }}" 
                       data-tooltip="Roles & Permissions">
                        <span class="nav-icon"><i class="fas fa-shield-alt"></i></span>
                        <span class="nav-link-text">Roles & Permissions</span>
                    </a>
                </li>
                @endcanPermission

                <!-- Orders -->
                @canPermission('orders', 'view')
                <li class="nav-item">
                    <a class="nav-link {{ request()->routeIs('orders.*') || request()->is('orders*') ? 'active' : '' }}" 
                       href="{{ route('orders.index') }}" 
                       data-tooltip="Orders">
                        <span class="nav-icon"><i class="fas fa-shopping-cart"></i></span>
                        <span class="nav-link-text">Orders</span>
                    </a>
                </li>
                @endcanPermission

                <!-- Manifests -->
                @canPermission('manifests', 'view')
                <li class="nav-item">
                    <a class="nav-link {{ request()->routeIs('manifest.*') || request()->is('manifest*') ? 'active' : '' }}" 
                       href="{{ route('manifest.index') }}" 
                       data-tooltip="Manifests">
                        <span class="nav-icon"><i class="fas fa-file-alt"></i></span>
                        <span class="nav-link-text">Manifests</span>
                    </a>
                </li>
                @endcanPermission

                <!-- Customers -->
                @canPermission('customers', 'view')
                <li class="nav-item">
                    <a class="nav-link {{ request()->routeIs('customers.*') || request()->is('customers*') ? 'active' : '' }}" 
                       href="{{ route('customers.index') }}" 
                       data-tooltip="Customers">
                        <span class="nav-icon"><i class="fas fa-user-check"></i></span>
                        <span class="nav-link-text">Customers</span>
                    </a>
                </li>
                @endcanPermission

                <!-- Equipment -->
                @canPermission('equipment', 'view')
                <li class="nav-item">
                    <a class="nav-link {{ request()->routeIs('equipment.*') || request()->is('equipment*') ? 'active' : '' }}" 
                       href="{{ route('equipment.index') }}" 
                       data-tooltip="Equipment">
                        <span class="nav-icon"><i class="fas fa-truck"></i></span>
                        <span class="nav-link-text">Equipment</span>
                    </a>
                </li>
                @endcanPermission

                <!-- Carriers -->
                @canPermission('carriers', 'view')
                <li class="nav-item">
                    <a class="nav-link {{ request()->routeIs('carriers.*') || request()->is('carriers*') ? 'active' : '' }}" 
                       href="{{ route('carriers.index') }}" 
                       data-tooltip="Carriers">
                        <span class="nav-icon"><i class="fas fa-boxes"></i></span>
                        <span class="nav-link-text">Carriers</span>
                    </a>
                </li>
                @endcanPermission

                <!-- Activity Logs -->
                <!-- @canPermission('activity_logs', 'view') -->
                <li class="nav-item">
                    <a class="nav-link {{ request()->routeIs('logs.*') || request()->is('activity/logs*') ? 'active' : '' }}" 
                       href="{{ route('logs') }}" 
                       data-tooltip="Activity Logs">
                        <span class="nav-icon"><i class="fas fa-list"></i></span>
                        <span class="nav-link-text">Activity Logs</span>
                    </a>
                </li>
                <!-- @endcanPermission -->

                <!-- Profile -->
                <li class="nav-item">
                    <a class="nav-link {{ request()->routeIs('profile.*') ? 'active' : '' }}" 
                       href="{{ route('profile.show') }}" 
                       data-tooltip="Profile">
                        <span class="nav-icon"><i class="fas fa-user"></i></span>
                        <span class="nav-link-text">Profile</span>
                    </a>
                </li>

                <!-- Divider for Super Admin -->
                <!-- @isSuperAdmin -->
                <li class="nav-item" style="margin-top: 20px; padding-top: 20px; border-top: 1px solid #e9ecef;">
                    <div style="padding: 0 20px; color: #6c757d; font-size: 11px; font-weight: 600; text-transform: uppercase; letter-spacing: 0.5px;">
                        System 
                    </div>
                </li>
                <!-- @endisSuperAdmin -->

                <!-- Plugins (Super Admin Only) -->
                <li class="nav-item">
                    <a class="nav-link {{ request()->routeIs('plugins.*') ? 'active' : '' }}" 
                       href="{{ route('plugins.index') }}" 
                       data-tooltip="Plugins">
                        <span class="nav-icon"><i class="fas fa-plug"></i></span>
                        <span class="nav-link-text">Plugins</span>
                    </a>
                </li>

                <!-- System Settings (Super Admin Only) -->
                <!-- @isSuperAdmin
                <li class="nav-item">
                    <a class="nav-link" 
                       href="#" 
                       data-tooltip="System Settings">
                        <span class="nav-icon"><i class="fas fa-cogs"></i></span>
                        <span class="nav-link-text">System Settings</span>
                    </a>
                </li>
                @endisSuperAdmin -->
            </ul>
        </div>
    </div>
</nav>
<div id="hk_nav_backdrop" class="hk-nav-backdrop"></div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Debug: Log current route information
    console.log('Current URL:', window.location.href);
    console.log('Current pathname:', window.location.pathname);
    
    // Check which links have active class
    const activeLinks = document.querySelectorAll('.nav-link.active');
    console.log('Active navigation links:', activeLinks.length);
    
    activeLinks.forEach((link, index) => {
        console.log(`Active link ${index + 1}:`, link.querySelector('.nav-link-text').textContent);
    });
    
    // Smooth page transition helper to prevent scrollbar flicker
    document.addEventListener('click', function(e) {
        const link = e.target.closest('a.nav-link');
        if (link && link.href && !link.href.includes('#')) {
            // Add a small loading state to prevent scrollbar flicker
            document.body.style.overflow = 'hidden';
            setTimeout(() => {
                document.body.style.overflow = '';
            }, 150);
        }
    });

    // Permission debug info (remove in production)
    console.log('User permissions loaded for navigation');
});
</script>