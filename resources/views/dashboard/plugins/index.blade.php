@extends('layouts.app')

@section('title', 'Plugin Marketplace')

@section('content')
<div class="container-fluid py-4 min-vh-100 bg-light">
    <!-- Header Section -->
    <x-breadcrumb 
        title="Plugins" 
        subtitle="Manage your organization's plugins" 
        icon="fa-plug">
        <li class="breadcrumb-item active" aria-current="page">Plugins</li>
      
    </x-breadcrumb>
    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show shadow-sm border-0 mb-4" role="alert">
            <i class="bi bi-check-circle-fill me-2"></i> {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show shadow-sm border-0 mb-4" role="alert">
            <i class="bi bi-exclamation-triangle-fill me-2"></i> {{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    <!-- Plugins Grid -->
    <div class="row g-4">
        @forelse($availablePlugins as $plugin)
            <div class="col-xl-3 col-lg-4 col-md-6">
                <div class="card h-100 border-0 shadow-sm hover-lift transition-all rounded-3 plugin-card">
                    <div class="card-body p-4 d-flex flex-column">
                        <div class="d-flex justify-content-between align-items-start mb-3">
                            <div class="plugin-icon bg-light rounded-3 p-3 d-flex align-items-center justify-content-center" style="width: 64px; height: 64px;">
                                @if(Str::contains(strtolower($plugin['slug']), 'quickbooks'))
                                    <i class="bi bi-receipt text-success fs-2"></i>
                                @elseif(Str::contains(strtolower($plugin['slug']), 'stripe'))
                                    <i class="bi bi-credit-card text-primary fs-2"></i>
                                @else
                                    <i class="bi bi-puzzle text-primary fs-2"></i>
                                @endif
                            </div>
                            <div class="dropdown">
                                <button class="btn btn-link text-muted p-0" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                                    <i class="bi bi-three-dots-vertical"></i>
                                </button>
                                <ul class="dropdown-menu dropdown-menu-end border-0 shadow">
                                    @if($plugin['is_active'])
                                        <li><a class="dropdown-item" href="{{ route('plugins.settings', $plugin['slug']) }}"><i class="bi bi-gear me-2"></i>Settings</a></li>
                                    @endif
                                    <!-- <li><a class="dropdown-item" href="#"><i class="bi bi-info-circle me-2"></i>Details</a></li> -->
                                </ul>
                            </div>
                        </div>
                        
                        <div class="mb-3">
                            <div class="d-flex align-items-center mb-1">
                                <h5 class="card-title fw-bold mb-0 text-dark">{{ $plugin['name'] }}</h5>
                                @if($plugin['is_active'])
                                    <span class="badge bg-success-subtle text-success ms-2 rounded-pill px-2 py-1" style="font-size: 0.65rem;">ACTIVE</span>
                                @elseif($plugin['is_installed'])
                                    <span class="badge bg-secondary-subtle text-secondary ms-2 rounded-pill px-2 py-1" style="font-size: 0.65rem;">INSTALLED</span>
                                @endif
                            </div>
                            <div class="small text-muted mb-2">by {{ $plugin['author'] }} <span class="mx-1">•</span> v{{ $plugin['version'] }}</div>
                            <p class="card-text text-secondary small line-clamp-3">
                                {{ $plugin['description'] }}
                            </p>
                        </div>
                        
                        <div class="mt-auto pt-3 border-top border-light">
                            <div class="d-grid gap-2">
                                @if(!$plugin['is_installed'])
                                    <form action="{{ route('plugins.activate', $plugin['slug']) }}" method="POST" class="d-grid">
                                        @csrf
                                        <button type="submit" class="btn btn-primary btn-sm fw-semibold py-2">
                                            <i class="bi bi-download me-2"></i>Install Plugin
                                        </button>
                                    </form>
                                @else
                                    <div class="d-flex gap-2">
                                        @if($plugin['is_active'])
                                            <form action="{{ route('plugins.deactivate', $plugin['slug']) }}" method="POST" class="flex-grow-1">
                                                @csrf
                                                <button type="submit" class="btn btn-outline-warning btn-sm w-100 fw-semibold py-2">
                                                    <i class="bi bi-pause-fill me-1"></i> Deactivate
                                                </button>
                                            </form>
                                        @else
                                            <form action="{{ route('plugins.activate', $plugin['slug']) }}" method="POST" class="flex-grow-1">
                                                @csrf
                                                <button type="submit" class="btn btn-outline-success btn-sm w-100 fw-semibold py-2">
                                                    <i class="bi bi-play-fill me-1"></i> Activate
                                                </button>
                                            </form>
                                        @endif
                                        
                                        <form action="{{ route('plugins.uninstall', $plugin['slug']) }}" method="POST" onsubmit="return confirm('Are you sure you want to uninstall this plugin?');">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-light btn-sm text-danger py-2 px-3" data-bs-toggle="tooltip" title="Uninstall">
                                                <i class="bi bi-trash"></i>
                                            </button>
                                        </form>
                                    </div>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        @empty
            <div class="col-12">
                <div class="text-center py-5">
                    <div class="mb-3">
                        <i class="bi bi-box-seam text-muted" style="font-size: 4rem;"></i>
                    </div>
                    <h4 class="text-muted">No plugins found</h4>
                    <p class="text-muted mb-0">Check your <code>app/Plugins</code> directory.</p>
                </div>
            </div>
        @endforelse
    </div>
</div>

<style>
    .hover-lift {
        transition: transform 0.2s ease, box-shadow 0.2s ease;
    }
    .hover-lift:hover {
        transform: translateY(-5px);
        box-shadow: 0 .5rem 1rem rgba(0,0,0,.15)!important;
    }
    .line-clamp-3 {
        display: -webkit-box;
        -webkit-line-clamp: 3;
        -webkit-box-orient: vertical;
        overflow: hidden;
    }
    .bg-success-subtle {
        background-color: #d1e7dd;
    }
    .text-success {
        color: #198754 !important;
    }
    .bg-secondary-subtle {
        background-color: #e2e3e5;
    }
    .text-secondary {
        color: #6c757d !important;
    }
</style>

<script>
    // Simple search functionality
    document.addEventListener('DOMContentLoaded', function() {
        const searchInput = document.querySelector('input[placeholder="Search plugins..."]');
        const pluginCards = document.querySelectorAll('.plugin-card');

        if(searchInput) {
            searchInput.addEventListener('keyup', function(e) {
                const searchTerm = e.target.value.toLowerCase();
                
                pluginCards.forEach(card => {
                    const title = card.querySelector('.card-title').textContent.toLowerCase();
                    const desc = card.querySelector('.card-text').textContent.toLowerCase();
                    const parentCol = card.closest('.col-xl-3'); // Adjust selector based on column class
                    
                    if(title.includes(searchTerm) || desc.includes(searchTerm)) {
                        parentCol.style.display = '';
                    } else {
                        parentCol.style.display = 'none';
                    }
                });
            });
        }
    });
</script>
@endsection
