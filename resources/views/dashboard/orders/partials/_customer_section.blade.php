{{-- Customer & Manifest Section - Compact Design --}}
<div class="row g-3 mb-4">
    <!-- Customer Section -->
    <div class="col-md-6">
        <div class="card shadow-sm h-100">
            <div class="card-body p-3">
                <div class="d-flex align-items-center justify-content-between mb-3">
                    <h6 class="card-title mb-0 fw-bold text-primary">
                        <i class="fa fa-user me-2"></i>CUSTOMER
                    </h6>
                    <button type="button" class="btn btn-sm btn-outline-primary" onclick="toggleCustomerSearch()">
                        <i class="fa fa-search me-1"></i><span id="customerToggleText">Change</span>
                    </button>
                </div>
                
                <!-- Customer Search Section (Hidden by default) -->
                <div id="customerSearchSection" class="d-none mb-3">
                    <div class="input-group input-group-sm">
                        <input type="text" class="form-control" id="customerSearchInput" 
                               placeholder="Search customers by name..." autocomplete="off">
                        <button class="btn btn-outline-secondary" type="button" onclick="cancelCustomerSearch()">
                            <i class="fa fa-times"></i>
                        </button>
                    </div>
                    <div id="customerSearchResults" class="search-results mt-2"></div>
                </div>
                
                <!-- Current Customer Display -->
                <div id="currentCustomerDisplay">
                    @if(isset($order->customer))
                        <div class="d-flex align-items-center">
                            <div class="customer-avatar-sm bg-primary me-2">
                                {{ strtoupper(substr($order->customer->name, 0, 2)) }}
                            </div>
                            <div class="flex-grow-1">
                                <h6 class="mb-1 fw-semibold">{{ $order->customer->name }}</h6>
                                <p class="mb-0 text-muted small">
                                    <i class="fa fa-map-marker-alt me-1"></i>
                                    {{ $order->customer->city }}, {{ $order->customer->state }}
                                </p>
                                @if($order->customer->customer_email)
                                    <p class="mb-0 text-muted small">
                                        <i class="fa fa-envelope me-1"></i>{{ $order->customer->customer_email }}
                                    </p>
                                @endif
                            </div>
                        </div>
                    @else
                        <div class="text-center text-muted py-2">
                            <i class="fa fa-user-plus fa-2x mb-2"></i>
                            <p class="mb-0 small">No customer assigned</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
    
    <!-- Manifest Section -->
    {{-- <div class="col-md-6">
        <div class="card shadow-sm h-100">
            <div class="card-body p-3">
                <div class="d-flex align-items-center justify-content-between mb-3">
                    <h6 class="card-title mb-0 fw-bold text-success">
                        <i class="fa fa-clipboard-list me-2"></i>MANIFEST
                    </h6>
                    <button type="button" class="btn btn-sm btn-outline-success" onclick="toggleManifestSearch()">
                        <i class="fa fa-search me-1"></i><span id="manifestToggleText">Assign</span>
                    </button>
                </div>
                
                <!-- Manifest Search Section (Hidden by default) -->
                <div id="manifestSearchSection" class="d-none mb-3">
                    <div class="input-group input-group-sm">
                        <input type="text" class="form-control" id="manifestSearchInput" 
                               placeholder="Search manifests..." autocomplete="off">
                        <button class="btn btn-outline-secondary" type="button" onclick="cancelManifestSearch()">
                            <i class="fa fa-times"></i>
                        </button>
                    </div>
                    <div id="manifestSearchResults" class="search-results mt-2"></div>
                </div>
                
                <!-- Current Manifest Display -->
                <div id="currentManifestDisplay">
                    @if(isset($order->manifest))
                        <div class="d-flex align-items-center">
                            <div class="manifest-icon-sm bg-success text-white me-2">
                                <i class="fa fa-clipboard-list"></i>
                            </div>
                            <div class="flex-grow-1">
                                <h6 class="mb-1 fw-semibold">{{ $order->manifest->code }}</h6>
                                <p class="mb-0 text-muted small">
                                    <i class="fa fa-calendar-alt me-1"></i>
                                    {{ $order->manifest->created_at->format('M d, Y') }}
                                </p>
                                <span class="badge badge-sm bg-{{ $order->manifest->status == 'active' ? 'success' : 'secondary' }}">
                                    {{ ucfirst($order->manifest->status ?? 'Draft') }}
                                </span>
                                @if($order->manifest->drivers && $order->manifest->drivers->count() > 0)
                                    <p class="mb-0 text-muted small">
                                        <i class="fa fa-user me-1"></i>{{ $order->manifest->drivers->first()->name }}
                                    </p>
                                @endif
                            </div>
                        </div>
                    @else
                        <div class="text-center text-muted py-2">
                            <i class="fa fa-clipboard fa-2x mb-2"></i>
                            <p class="mb-0 small">No manifest assigned</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div> --}}
</div>

<style>
.customer-avatar-sm, .manifest-icon-sm {
    width: 35px;
    height: 35px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 12px;
    font-weight: bold;
    color: white;
    text-shadow: 1px 1px 2px rgba(0,0,0,0.3);
}

.search-results {
    max-height: 250px;
    overflow-y: auto;
    border: 1px solid #e9ecef;
    border-radius: 6px;
    background: white;
    box-shadow: 0 2px 8px rgba(0,0,0,0.1);
}

.search-item {
    padding: 8px 12px;
    border-bottom: 1px solid #f8f9fa;
    cursor: pointer;
    transition: all 0.2s ease;
}

.search-item:last-child {
    border-bottom: none;
}

.search-item:hover {
    background-color: #f8f9fa;
}

.search-item.selected {
    background-color: #e7f3ff;
    border-left: 3px solid #007bff;
}

.search-loading {
    padding: 20px;
    text-align: center;
    color: #6c757d;
}

.badge-sm {
    font-size: 0.7rem;
    padding: 0.2rem 0.4rem;
}
</style>

<script>
console.log('Customer & Manifest Section JavaScript loaded');

let customerSearchTimeout;
let manifestSearchTimeout;
let selectedCustomerId = null;
let selectedManifestId = null;

// Customer Search Functions
function toggleCustomerSearch() {
    console.log('toggleCustomerSearch called');
    
    const searchSection = document.getElementById('customerSearchSection');
    const currentDisplay = document.getElementById('currentCustomerDisplay');
    const toggleText = document.getElementById('customerToggleText');
    
    if (searchSection && searchSection.classList.contains('d-none')) {
        searchSection.classList.remove('d-none');
        currentDisplay.classList.add('d-none');
        toggleText.textContent = 'Cancel';
        document.getElementById('customerSearchInput').focus();
        loadCustomers(''); // Load all customers initially
    } else {
        cancelCustomerSearch();
    }
}

function cancelCustomerSearch() {
    console.log('cancelCustomerSearch called');
    
    const searchSection = document.getElementById('customerSearchSection');
    const currentDisplay = document.getElementById('currentCustomerDisplay');
    const toggleText = document.getElementById('customerToggleText');
    
    if (searchSection && currentDisplay && toggleText) {
        searchSection.classList.add('d-none');
        currentDisplay.classList.remove('d-none');
        toggleText.textContent = 'Change';
        document.getElementById('customerSearchInput').value = '';
        document.getElementById('customerSearchResults').innerHTML = '';
        console.log('Customer search cancelled successfully');
    } else {
        console.error('Failed to find required elements for cancelCustomerSearch');
    }
}

function loadCustomers(query) {
    console.log('loadCustomers called with query:', query);
    
    const resultsContainer = document.getElementById('customerSearchResults');
    
    if (!resultsContainer) {
        console.error('customerSearchResults container not found');
        return;
    }
    
    resultsContainer.innerHTML = `
        <div class="search-loading">
            <div class="spinner-border spinner-border-sm text-primary" role="status">
                <span class="visually-hidden">Loading...</span>
            </div>
            <div class="mt-1">Loading customers...</div>
        </div>
    `;

    const url = query ? `/api/customers/search?q=${encodeURIComponent(query)}` : '/api/customers/search';
    console.log('Fetching from URL:', url);
    
    fetch(url)
        .then(response => {
            console.log('Response status:', response.status);
            return response.json();
        })
        .then(customers => {
            console.log('Customers loaded:', customers.length);
            displayCustomers(customers);
        })
        .catch(error => {
            console.error('Error loading customers:', error);
            resultsContainer.innerHTML = `
                <div class="search-loading text-danger">
                    <i class="fa fa-exclamation-triangle"></i>
                    <div class="mt-1">Error loading customers</div>
                </div>
            `;
        });
}

function displayCustomers(customers) {
    const resultsContainer = document.getElementById('customerSearchResults');
    
    if (customers.length === 0) {
        resultsContainer.innerHTML = `
            <div class="search-loading">
                <i class="fa fa-users"></i>
                <div class="mt-1">No customers found</div>
            </div>
        `;
        return;
    }

    const customersHtml = customers.map(customer => `
        <div class="search-item" onclick="selectCustomer(${customer.id}, this)">
            <div class="d-flex align-items-center">
                <div class="customer-avatar-sm bg-primary me-2">
                    ${customer.name.substring(0, 2).toUpperCase()}
                </div>
                <div class="flex-grow-1">
                    <div class="fw-semibold">${customer.name}</div>
                    <div class="text-muted small">
                        <i class="fa fa-map-marker-alt me-1"></i>${customer.city || 'N/A'}, ${customer.state || 'N/A'}
                    </div>
                    ${customer.customer_email ? `<div class="text-muted small"><i class="fa fa-envelope me-1"></i>${customer.customer_email}</div>` : ''}
                </div>
            </div>
        </div>
    `).join('');

    resultsContainer.innerHTML = customersHtml;
}

function selectCustomer(customerId, element) {
    selectedCustomerId = customerId;
    
    // Highlight selected item
    document.querySelectorAll('#customerSearchResults .search-item').forEach(item => {
        item.classList.remove('selected');
    });
    element.classList.add('selected');
    
    // Assign customer
    assignCustomerToOrder(customerId);
}

function assignCustomerToOrder(customerId) {
    const resultsContainer = document.getElementById('customerSearchResults');
    const originalContent = resultsContainer.innerHTML;
    
    resultsContainer.innerHTML = `
        <div class="search-loading">
            <div class="spinner-border spinner-border-sm text-success" role="status">
                <span class="visually-hidden">Loading...</span>
            </div>
            <div class="mt-1">Assigning customer...</div>
        </div>
    `;

    fetch(`/orders/{{ isset($order) ? $order->id : '' }}/assign-customer`, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
        },
        body: JSON.stringify({ customer_id: customerId })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            location.reload(); // Reload to show updated customer info
        } else {
            throw new Error(data.message || 'Failed to assign customer');
        }
    })
    .catch(error => {
        console.error('Error assigning customer:', error);
        resultsContainer.innerHTML = originalContent;
        alert('Error assigning customer. Please try again.');
    });
}

// Manifest Search Functions
function toggleManifestSearch() {
    console.log('toggleManifestSearch called');
    
    const searchSection = document.getElementById('manifestSearchSection');
    const currentDisplay = document.getElementById('currentManifestDisplay');
    const toggleText = document.getElementById('manifestToggleText');
    
    if (searchSection && searchSection.classList.contains('d-none')) {
        searchSection.classList.remove('d-none');
        currentDisplay.classList.add('d-none');
        toggleText.textContent = 'Cancel';
        document.getElementById('manifestSearchInput').focus();
        loadManifests(''); // Load all manifests initially
    } else {
        cancelManifestSearch();
    }
}

function cancelManifestSearch() {
    const searchSection = document.getElementById('manifestSearchSection');
    const currentDisplay = document.getElementById('currentManifestDisplay');
    const toggleText = document.getElementById('manifestToggleText');
    
    searchSection.classList.add('d-none');
    currentDisplay.classList.remove('d-none');
    toggleText.textContent = 'Assign';
    document.getElementById('manifestSearchInput').value = '';
    document.getElementById('manifestSearchResults').innerHTML = '';
}

function loadManifests(query) {
    console.log('loadManifests called with query:', query);
    
    const resultsContainer = document.getElementById('manifestSearchResults');
    
    if (!resultsContainer) {
        console.error('manifestSearchResults container not found');
        return;
    }
    
    resultsContainer.innerHTML = `
        <div class="search-loading">
            <div class="spinner-border spinner-border-sm text-success" role="status">
                <span class="visually-hidden">Loading...</span>
            </div>
            <div class="mt-1">Loading manifests...</div>
        </div>
    `;

    const url = query ? `/api/manifests/search?q=${encodeURIComponent(query)}` : '/api/manifests/search';
    console.log('Fetching manifests from URL:', url);
    
    fetch(url)
        .then(response => {
            console.log('Manifest response status:', response.status);
            return response.json();
        })
        .then(manifests => {
            console.log('Manifests loaded:', manifests.length);
            displayManifests(manifests);
        })
        .catch(error => {
            console.error('Error loading manifests:', error);
            resultsContainer.innerHTML = `
                <div class="search-loading text-danger">
                    <i class="fa fa-exclamation-triangle"></i>
                    <div class="mt-1">Error loading manifests</div>
                </div>
            `;
        });
}

function displayManifests(manifests) {
    console.log('displayManifests called with:', manifests);
    const resultsContainer = document.getElementById('manifestSearchResults');
    
    if (!Array.isArray(manifests)) {
        console.error('Manifests is not an array:', manifests);
        resultsContainer.innerHTML = `
            <div class="search-loading text-danger">
                <i class="fa fa-exclamation-triangle"></i>
                <div class="mt-1">Invalid manifest data received</div>
            </div>
        `;
        return;
    }
    
    if (manifests.length === 0) {
        resultsContainer.innerHTML = `
            <div class="search-loading">
                <i class="fa fa-clipboard-list"></i>
                <div class="mt-1">No manifests found</div>
            </div>
        `;
        return;
    }

    const manifestsHtml = manifests.map(manifest => {
        // Handle both code and manifest_number fields
        const manifestNumber = manifest.manifest_number || manifest.code || 'N/A';
        const status = manifest.status || 'draft';
        const driverName = manifest.driver?.name || 'No driver assigned';
        const createdAt = manifest.created_at ? new Date(manifest.created_at).toLocaleDateString() : 'N/A';
        
        return `
            <div class="search-item" onclick="selectManifest(${manifest.id}, this)">
                <div class="d-flex align-items-center">
                    <div class="manifest-icon-sm bg-success text-white me-2">
                        <i class="fa fa-clipboard-list"></i>
                    </div>
                    <div class="flex-grow-1">
                        <div class="d-flex align-items-center justify-content-between">
                            <div class="fw-semibold">${manifestNumber}</div>
                            <span class="badge badge-sm bg-${status === 'active' ? 'success' : 'secondary'}">
                                ${status.charAt(0).toUpperCase() + status.slice(1)}
                            </span>
                        </div>
                        <div class="text-muted small">
                            <i class="fa fa-calendar-alt me-1"></i>${createdAt}
                        </div>
                        <div class="text-muted small">
                            <i class="fa fa-user me-1"></i>${driverName}
                        </div>
                    </div>
                </div>
            </div>
        `;
    }).join('');

    resultsContainer.innerHTML = manifestsHtml;
}

function selectManifest(manifestId, element) {
    selectedManifestId = manifestId;
    
    // Highlight selected item
    document.querySelectorAll('#manifestSearchResults .search-item').forEach(item => {
        item.classList.remove('selected');
    });
    element.classList.add('selected');
    
    // Assign manifest
    assignManifestToOrder(manifestId);
}

function assignManifestToOrder(manifestId) {
    const resultsContainer = document.getElementById('manifestSearchResults');
    const originalContent = resultsContainer.innerHTML;
    
    resultsContainer.innerHTML = `
        <div class="search-loading">
            <div class="spinner-border spinner-border-sm text-success" role="status">
                <span class="visually-hidden">Loading...</span>
            </div>
            <div class="mt-1">Assigning manifest...</div>
        </div>
    `;

    fetch(`/orders/{{ isset($order) ? $order->id : '' }}/assign-manifest`, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
        },
        body: JSON.stringify({ manifest_id: manifestId })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            location.reload(); // Reload to show updated manifest info
        } else {
            throw new Error(data.message || 'Failed to assign manifest');
        }
    })
    .catch(error => {
        console.error('Error assigning manifest:', error);
        resultsContainer.innerHTML = originalContent;
        alert('Error assigning manifest. Please try again.');
    });
}

// Search event listeners
document.addEventListener('DOMContentLoaded', function() {
    console.log('DOMContentLoaded fired for customer & manifest section');
    
    const customerSearchInput = document.getElementById('customerSearchInput');
    const manifestSearchInput = document.getElementById('manifestSearchInput');
    
    console.log('Search inputs found:', {
        customerSearchInput: !!customerSearchInput,
        manifestSearchInput: !!manifestSearchInput
    });
    
    if (customerSearchInput) {
        customerSearchInput.addEventListener('input', function() {
            clearTimeout(customerSearchTimeout);
            customerSearchTimeout = setTimeout(() => {
                loadCustomers(this.value);
            }, 300);
        });
        console.log('Customer search input listener added');
    }
    
    if (manifestSearchInput) {
        manifestSearchInput.addEventListener('input', function() {
            clearTimeout(manifestSearchTimeout);
            manifestSearchTimeout = setTimeout(() => {
                loadManifests(this.value);
            }, 300);
        });
        console.log('Manifest search input listener added');
    }
    
    // Test function availability
    console.log('Functions available:', {
        toggleCustomerSearch: typeof toggleCustomerSearch,
        toggleManifestSearch: typeof toggleManifestSearch
    });
});
</script>