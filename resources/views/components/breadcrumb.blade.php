@props(['title' => '', 'subtitle' => '', 'icon' => 'fa-home'])

<div class="breadcrumb-wrapper mb-4">
    <div class="card shadow-sm border-0">
        <div class="card-body py-3">
            <div class="row align-items-center">
                <div class="col">
                    <!-- Breadcrumb Navigation -->
                    <nav aria-label="breadcrumb" class="mb-2">
                        <ol class="breadcrumb mb-0">
                            <li class="breadcrumb-item">
                                <a href="{{ route('company.dashboard') }}" class="text-decoration-none">
                                    <i class="fa fa-home me-1"></i>Dashboard
                                </a>
                            </li>
                            {{ $slot }}
                        </ol>
                    </nav>
                    
                    <!-- Page Title -->
                    @if($title)
                        <div class="page-title">
                            <h4 class="mb-0 fw-bold text-dark">
                                <i class="fa {{ $icon }} text-primary me-2"></i>{{ $title }}
                            </h4>
                            @if($subtitle)
                                <p class="text-muted mb-0 mt-1">{{ $subtitle }}</p>
                            @endif
                        </div>
                    @endif
                </div>
                
                <!-- Action Buttons Slot -->
                @if(isset($actions))
                    <div class="col-auto">
                        {{ $actions }}
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>

<style>
.breadcrumb-wrapper .breadcrumb {
    background: none;
    padding: 0;
    margin: 0;
}

.breadcrumb-item + .breadcrumb-item::before {
    content: "›";
    color: #6c757d;
    font-weight: bold;
}

.breadcrumb-item a {
    color: #5a5c69;
    transition: color 0.2s ease;
}

.breadcrumb-item a:hover {
    color: #007bff;
}

.breadcrumb-item.active {
    color: #495057;
    font-weight: 500;
}

.page-title h4 {
    font-size: 1.5rem;
    line-height: 1.2;
}

.breadcrumb-wrapper .card {
    border-left: 4px solid #007bff;
}

@media (max-width: 768px) {
    .page-title h4 {
        font-size: 1.25rem;
    }
    
    .breadcrumb {
        font-size: 0.875rem;
    }
}
</style>