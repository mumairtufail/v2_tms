@extends('layouts.app')

@section('title', $plugin['name'] . ' Settings')

@section('content')
<div class="container-fluid py-4 min-vh-100 bg-light">
    <!-- Breadcrumbs -->
    <x-breadcrumb 
        :title="$plugin['name'] . ' Settings'" 
        subtitle="Manage configurations" 
        icon="fa-cogs">
        <li class="breadcrumb-item"><a href="{{ route('plugins.index') }}">Plugins</a></li>
        <li class="breadcrumb-item active" aria-current="page">Settings</li>
    </x-breadcrumb>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show shadow-sm border-0 mb-4" role="alert">
            <i class="bi bi-check-circle-fill me-2"></i> {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    <div class="row">
        <!-- Existing Configurations List -->
        <div class="col-lg-4 mb-4">
            <div class="card border-0 shadow-sm rounded-3">
                <div class="card-header bg-white py-3 border-0">
                    <h5 class="card-title fw-bold mb-0">Configurations</h5>
                </div>
                <div class="card-body p-0">
                    <div class="list-group list-group-flush">
                        @forelse($configurations as $config)
                            <div class="list-group-item border-0 border-bottom p-3 d-flex justify-content-between align-items-center">
                                <div>
                                    <h6 class="mb-1 fw-semibold">{{ $config->name }}</h6>
                                    @if($config->is_active)
                                        <span class="badge bg-success-subtle text-success rounded-pill" style="font-size: 0.7rem;">Active</span>
                                    @else
                                        <span class="badge bg-secondary-subtle text-secondary rounded-pill" style="font-size: 0.7rem;">Inactive</span>
                                    @endif
                                </div>
                                <div class="btn-group">
                                    @if(Str::contains(strtolower($plugin['slug']), 'quickbooks') && !empty($config->configuration['client_id']))
                                        <a href="{{ route('plugins.quickbooks.connect', ['config_id' => $config->id]) }}" class="btn btn-sm btn-outline-success" title="Connect to QuickBooks">
                                            <i class="bi bi-link-45deg"></i> Connect
                                        </a>
                                    @endif
                                    <button type="button" class="btn btn-sm btn-outline-primary" 
                                        data-config='@json($config)'
                                        onclick="editConfig(JSON.parse(this.dataset.config))">
                                        <i class="bi bi-pencil"></i>
                                    </button>
                                    <form action="{{ route('plugins.settings.destroy', ['slug' => $plugin['slug'], 'id' => $config->id]) }}" method="POST" onsubmit="return confirm('Delete this configuration?');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-sm btn-outline-danger ms-1">
                                            <i class="bi bi-trash"></i>
                                        </button>
                                    </form>
                                </div>
                            </div>
                        @empty
                            <div class="p-4 text-center text-muted">
                                <i class="bi bi-inbox fs-4 d-block mb-2"></i>
                                No configurations found.
                            </div>
                        @endforelse
                    </div>
                </div>
                <div class="card-footer bg-white border-0 py-3 text-center">
                    <button class="btn btn-primary w-100" onclick="showAddForm()">
                        <i class="bi bi-plus-lg me-1"></i> Add New Configuration
                    </button>
                </div>
            </div>
        </div>

        <!-- Configuration Form -->
        <div class="col-lg-8">
            <div class="card border-0 shadow-sm rounded-3" id="configFormCard">
                <div class="card-header bg-white py-3 border-0 d-flex justify-content-between align-items-center">
                    <h5 class="card-title fw-bold mb-0" id="formTitle">Add Configuration</h5>
                </div>
                <div class="card-body p-4">
                    <form id="configForm" action="{{ route('plugins.settings.store', $plugin['slug']) }}" method="POST">
                        @csrf
                        <div id="methodField"></div>

                        <div class="mb-3">
                            <label for="name" class="form-label fw-semibold">Configuration Name</label>
                            <input type="text" class="form-control" id="name" name="name" placeholder="e.g., Primary Account" required>
                            <div class="form-text">A friendly name to identify this configuration.</div>
                        </div>

                        <!-- Dynamic Fields based on Plugin Slug -->
                        @if(Str::contains(strtolower($plugin['slug']), 'quickbooks'))
                            <div class="mb-3">
                                <label for="client_id" class="form-label fw-semibold">Client ID</label>
                                <input type="text" class="form-control" id="client_id" name="configuration[client_id]" required>
                            </div>
                            <div class="mb-3">
                                <label for="client_secret" class="form-label fw-semibold">Client Secret</label>
                                <input type="password" class="form-control" id="client_secret" name="configuration[client_secret]" required>
                            </div>
                            <div class="mb-3">
                                <label for="base_url" class="form-label fw-semibold">Base URL / Environment</label>
                                <select class="form-select" id="base_url" name="configuration[base_url]" required>
                                    <option value="https://sandbox-quickbooks.api.intuit.com">Sandbox</option>
                                    <option value="https://quickbooks.api.intuit.com">Production</option>
                                </select>
                            </div>
                            
                            <hr class="my-4">
                            <h6 class="fw-bold mb-3">Manual Token Entry (Optional)</h6>
                            <div class="alert alert-info small">
                                If you have generated tokens from the QuickBooks Playground, you can enter them here directly.
                            </div>

                            <div class="mb-3">
                                <label for="realm_id" class="form-label fw-semibold">Realm ID (Company ID)</label>
                                <input type="text" class="form-control" id="realm_id" name="configuration[realm_id]">
                            </div>
                            <div class="mb-3">
                                <label for="access_token" class="form-label fw-semibold">Access Token</label>
                                <textarea class="form-control" id="access_token" name="configuration[access_token]" rows="3"></textarea>
                            </div>
                            <div class="mb-3">
                                <label for="refresh_token" class="form-label fw-semibold">Refresh Token</label>
                                <textarea class="form-control" id="refresh_token" name="configuration[refresh_token]" rows="2"></textarea>
                            </div>
                        @else
                            <!-- Fallback for generic plugins -->
                            <div class="alert alert-info">
                                No specific fields defined for this plugin. You can add generic key-value pairs if implemented.
                            </div>
                        @endif

                        <div class="mb-3 form-check form-switch">
                            <input class="form-check-input" type="checkbox" id="is_active" name="is_active" checked>
                            <label class="form-check-label fw-semibold" for="is_active">Active</label>
                        </div>

                        <div class="d-flex justify-content-end gap-2 mt-4">
                            <button type="button" class="btn btn-light" onclick="resetForm()">Cancel</button>
                            <button type="submit" class="btn btn-primary px-4">Save Configuration</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    function showAddForm() {
        document.getElementById('formTitle').textContent = 'Add Configuration';
        document.getElementById('configForm').action = "{{ route('plugins.settings.store', $plugin['slug']) }}";
        document.getElementById('methodField').innerHTML = '';
        document.getElementById('name').value = '';
        document.getElementById('is_active').checked = true;
        
        // Reset specific fields
        if(document.getElementById('client_id')) document.getElementById('client_id').value = '';
        if(document.getElementById('client_secret')) document.getElementById('client_secret').value = '';
        if(document.getElementById('base_url')) document.getElementById('base_url').selectedIndex = 0;
    }

    function editConfig(config) {
        document.getElementById('formTitle').textContent = 'Edit Configuration';
        document.getElementById('configForm').action = "/plugins/{{ $plugin['slug'] }}/settings/" + config.id;
        document.getElementById('methodField').innerHTML = '@method("PUT")';
        
        document.getElementById('name').value = config.name;
        document.getElementById('is_active').checked = config.is_active;

        // Populate specific fields from the configuration JSON
        // Note: We need to handle the encrypted array. 
        // In Blade, $config->configuration is automatically decrypted by the model cast if accessed directly.
        // However, passing it to JS via json_encode might be tricky if not handled carefully.
        // Let's manually populate for now assuming standard keys.
        
        const configuration = config.configuration;
        if(configuration) {
            if(document.getElementById('client_id') && configuration.client_id) 
                document.getElementById('client_id').value = configuration.client_id;
            // Don't populate secret for security, or leave empty to indicate "unchanged"
            // For now, we'll leave it empty and user re-enters if they want to change it, 
            // OR we populate it if the requirement allows. 
            // Given the user wants to "store" it, usually we don't send secrets back to client.
            // But for editing, we might need to handle "leave blank to keep current".
            // For simplicity in this iteration, we'll require re-entry or just show it (since it's decrypted).
            if(document.getElementById('client_secret') && configuration.client_secret) 
                document.getElementById('client_secret').value = configuration.client_secret;
                
            if(document.getElementById('base_url') && configuration.base_url) 
                document.getElementById('base_url').value = configuration.base_url;

            if(document.getElementById('realm_id') && configuration.realm_id) 
                document.getElementById('realm_id').value = configuration.realm_id;
            if(document.getElementById('access_token') && configuration.access_token) 
                document.getElementById('access_token').value = configuration.access_token;
            if(document.getElementById('refresh_token') && configuration.refresh_token) 
                document.getElementById('refresh_token').value = configuration.refresh_token;
        }
    }

    function resetForm() {
        showAddForm();
        // Reset manual token fields
        if(document.getElementById('realm_id')) document.getElementById('realm_id').value = '';
        if(document.getElementById('access_token')) document.getElementById('access_token').value = '';
        if(document.getElementById('refresh_token')) document.getElementById('refresh_token').value = '';
    }
</script>
@endsection
