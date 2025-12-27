<!-- Driver Modal with Unique Identifiers -->
<div id="jsm_driverSelectionModal" class="modal" data-manifest-id="{{ $manifest->id }}">
    <div class="jsm-modal-content">
        <span class="jsm-close-btn" onclick="JSM_CloseDriverModal()">&times;</span>
        <h2>Add driver</h2>

        <div class="jsm-driver-container">
            <div class="jsm-driver-list">
                <div class="jsm-list-header">
                    <h3>Drivers</h3>
                    <a href="{{ route('users.create', ['source' => 'manifest.edit', 'manifest_id' => $manifest->id]) }}"
                        class="btn btn-outline">
                        <i data-feather="plus"></i> New driver
                    </a>
                </div>

                <input type="text" class="jsm-search-bar" placeholder="Search" onkeyup="JSM_FilterDrivers()">

                <div id="jsm_driverItems">
                    <!-- Driver items will be populated via AJAX -->
                    <p class="text-center py-4" id="jsm_loadingDrivers">Loading drivers...</p>
                </div>

                <div class="jsm-pagination">
                    <span id="jsm_driver_pagination_info">0-0 of 0</span>
                    <div class="jsm-pagination-controls">
                        <button class="btn btn-outline">&lt;&lt;</button>
                        <button class="btn btn-outline">&lt;</button>
                        <button class="btn btn-outline">&gt;</button>
                        <button class="btn btn-outline">&gt;&gt;</button>
                    </div>
                </div>
            </div>

            <div class="jsm-driver-list">
                <h3>Selected drivers (<span id="jsm_selectedDriverCount">0</span>)</h3>
                <div id="jsm_selectedDrivers">
                    <p class="jsm-no-selection">No drivers selected</p>
                </div>
            </div>
        </div>

        <div class="jsm-modal-footer">
            <button class="btn btn-outline" onclick="JSM_CloseDriverModal()">Cancel</button>
            <button class="btn btn-primary" onclick="JSM_SaveDriverSelection()">Save</button>
        </div>
    </div>
</div>

<style>
    #jsm_driverSelectionModal.modal {
        display: none;
        visibility: hidden;
        position: fixed;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background: rgba(0, 0, 0, 0);
        transition: all 0.3s ease;
        justify-content: center;
        align-items: flex-start;
        overflow-y: auto;
        z-index: 1000;
    }

    #jsm_driverSelectionModal.modal.show {
        visibility: visible;
        background: rgba(0, 0, 0, 0.5);
    }

    #jsm_driverSelectionModal .jsm-modal-content {
        background: white;
        margin: 5% auto;
        padding: 24px;
        width: 90%;
        max-width: 1000px;
        border-radius: 8px;
        position: relative;
        transform: translateY(-50px);
        opacity: 0;
        transition: all 0.3s ease;
        max-height: 90vh;
        overflow-y: auto;
    }

    #jsm_driverSelectionModal.show .jsm-modal-content {
        transform: translateY(0);
        opacity: 1;
    }

    #jsm_driverSelectionModal .jsm-close-btn {
        position: absolute;
        right: 24px;
        top: 24px;
        font-size: 24px;
        cursor: pointer;
        transition: transform 0.2s ease;
    }

    #jsm_driverSelectionModal .jsm-close-btn:hover {
        transform: scale(1.1);
    }

    #jsm_driverSelectionModal .jsm-driver-container {
        display: grid;
        grid-template-columns: 60% 40%;
        gap: 24px;
        margin-top: 24px;
        max-height: calc(80vh - 200px);
        overflow: visible;
        /* Changed from hidden to visible to allow scrolling */
    }

    #jsm_driverSelectionModal .jsm-driver-list {
        border: 1px solid #e0e0e0;
        border-radius: 8px;
        padding: 16px;
        height: 100%;
        max-height: calc(80vh - 250px);
        /* Added explicit max-height */
        overflow-y: auto;
        /* Ensures vertical scrolling works */
    }

    #jsm_driverSelectionModal .jsm-list-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 16px;
    }

    #jsm_driverSelectionModal .jsm-list-header h3 {
        margin: 0;
    }

    #jsm_driverSelectionModal .jsm-search-bar {
        width: 100%;
        padding: 8px 12px;
        border: 1px solid #e0e0e0;
        border-radius: 4px;
        margin-bottom: 16px;
    }

    #jsm_driverSelectionModal .jsm-filter-section {
        display: flex;
        gap: 8px;
        margin-bottom: 16px;
        flex-wrap: wrap;
        position: sticky;
        top: 0;
        background: white;
        z-index: 1;
        padding: 8px 0;
    }

    #jsm_driverSelectionModal .jsm-driver-item {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding: 12px;
        border: 1px solid #e0e0e0;
        border-radius: 4px;
        margin-bottom: 8px;
        transition: all 0.2s ease;
    }

    #jsm_driverSelectionModal .jsm-driver-item:hover {
        transform: translateY(-2px);
        box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
    }

    #jsm_driverSelectionModal .jsm-status-badge {
        background: #e8f5e9;
        color: #2e7d32;
        padding: 4px 8px;
        border-radius: 4px;
        font-size: 12px;
    }

    #jsm_driverSelectionModal .btn {
        padding: 8px 16px;
        border-radius: 4px;
        cursor: pointer;
        transition: all 0.2s ease;
        font-size: 14px;
    }

    #jsm_driverSelectionModal .btn-primary {
        background: #1a73e8;
        color: white;
        border: none;
    }

    #jsm_driverSelectionModal .btn-primary:hover {
        background: #1557b0;
        transform: translateY(-1px);
    }

    #jsm_driverSelectionModal .btn-outline {
        border: 1px solid #1a73e8;
        color: #1a73e8;
        background: white;
    }

    #jsm_driverSelectionModal .btn-outline:hover {
        background: #f0f7ff;
        transform: translateY(-1px);
    }

    #jsm_driverSelectionModal .jsm-modal-footer {
        display: flex;
        justify-content: flex-end;
        gap: 12px;
        margin-top: 24px;
        padding-top: 16px;
        border-top: 1px solid #e0e0e0;
    }

    #jsm_driverSelectionModal .jsm-pagination {
        text-align: center;
        margin-top: 16px;
        padding: 8px 0;
        border-top: 1px solid #e0e0e0;
    }

    #jsm_driverSelectionModal .jsm-pagination-controls {
        display: inline-flex;
        gap: 4px;
        margin-left: 8px;
    }

    #jsm_driverSelectionModal .jsm-no-selection {
        text-align: center;
        color: #666;
        margin-top: 16px;
    }

    /* Feather icon styles */
    #jsm_driverSelectionModal [data-feather] {
        width: 16px;
        height: 16px;
        vertical-align: middle;
    }
</style>

<!-- Script for Driver Modal with JSM prefix for uniqueness -->
<script>
    // JSM namespace to avoid conflicts
    var JSM = JSM || {};
    JSM.drivers = {
        allDrivers: [],
        selectedDrivers: new Set(),
        searchTerm: ''
    };

    // Helper to get manifest ID
    JSM.getManifestId = function() {
        const modal = document.getElementById('jsm_driverSelectionModal');
        return modal ? modal.getAttribute('data-manifest-id') : null;
    };

    // Open modal function with JSM prefix
    window.JSM_OpenDriverModal = function() {
        const modal = document.getElementById('jsm_driverSelectionModal');
        if (!modal) {
            console.error("Driver modal not found");
            return;
        }

        // Show modal
        modal.style.display = 'flex';
        modal.offsetHeight; // Force reflow
        modal.classList.add('show');

        // Prevent background scrolling
        document.body.style.overflow = 'hidden';

        // Load drivers
        JSM.loadDrivers();
    };

    // Close modal function with JSM prefix
    window.JSM_CloseDriverModal = function() {
        const modal = document.getElementById('jsm_driverSelectionModal');
        if (!modal) return;

        modal.classList.remove('show');
        setTimeout(() => {
            modal.style.display = 'none';
            document.body.style.overflow = ''; // Re-enable scrolling
        }, 300);
    };

    // Load drivers function
    JSM.loadDrivers = function() {
        const manifestId = JSM.getManifestId();
        if (!manifestId) {
            console.error("No manifest ID found");
            return;
        }

        const container = document.getElementById('jsm_driverItems');
        if (!container) {
            console.error("Driver items container not found");
            return;
        }

        container.innerHTML = '<p class="text-center py-4">Loading drivers...</p>';

        // Fetch drivers from API
        fetch(`/manifest/${manifestId}/drivers`)
            .then(response => {
                if (!response.ok) {
                    throw new Error('Network response was not ok: ' + response.statusText);
                }
                return response.json();
            })
            .then(data => {
                if (data.success) {
                    JSM.drivers.allDrivers = data.drivers;

                    // Initialize selected drivers
                    JSM.drivers.selectedDrivers.clear();
                    JSM.drivers.allDrivers.forEach(item => {
                        if (item.is_assigned) {
                            JSM.drivers.selectedDrivers.add(item.id);
                        }
                    });

                    JSM.populateDrivers();
                    JSM.updateSelectedDriversList();
                    JSM.updatePaginationInfo();
                } else {
                    container.innerHTML = '<p class="text-center py-4 text-danger">Failed to load drivers</p>';
                }
            })
            .catch(error => {
                console.error('Error fetching drivers:', error);
                container.innerHTML = `<p class="text-center py-4 text-danger">Error: ${error.message}</p>`;
            });
    };

    // Populate drivers list - now shows all drivers including selected ones
    JSM.populateDrivers = function() {
        const container = document.getElementById('jsm_driverItems');
        if (!container) {
            console.error("Driver items container not found");
            return;
        }

        // Apply search filter
        const filteredDrivers = JSM.drivers.allDrivers.filter(item => {
            const fullName = `${item.f_name || ''}`.trim();
            const email = item.email || '';
            const searchTerm = JSM.drivers.searchTerm || '';

            return fullName.toLowerCase().includes(searchTerm.toLowerCase()) ||
                email.toLowerCase().includes(searchTerm.toLowerCase());
        });

        if (filteredDrivers.length === 0) {
            container.innerHTML = '<p class="text-center py-4">No drivers found</p>';
            return;
        }

        container.innerHTML = filteredDrivers.map(item => {
            // Format driver name from f_name and l_name
            const fullName = `${item.f_name || ''}`.trim() || 'N/A';
            const email = item.email || 'N/A';
            const isSelected = JSM.drivers.selectedDrivers.has(item.id);

            return `
      <div class="jsm-driver-item">
        <div>
          <span class="jsm-status-badge">Driver</span>
          <span style="margin-left: 10px;">${fullName}</span>
        </div>
        <div>
          <span style="color: #666; margin-right: 10px;">${email}</span>
          <button class="btn ${isSelected ? 'btn-primary' : 'btn-outline'}" 
                  onclick="JSM_ToggleDriver(${item.id})">
            ${isSelected ? 'Selected' : 'Select'}
          </button>
        </div>
      </div>
    `;
        }).join('');
    };

    // Toggle driver selection with JSM prefix
    window.JSM_ToggleDriver = function(id) {
        if (JSM.drivers.selectedDrivers.has(id)) {
            JSM.drivers.selectedDrivers.delete(id);
        } else {
            JSM.drivers.selectedDrivers.add(id);
        }
        JSM.populateDrivers();
        JSM.updateSelectedDriversList();
    };

    // Update selected drivers list
    JSM.updateSelectedDriversList = function() {
        const container = document.getElementById('jsm_selectedDrivers');
        const countElement = document.getElementById('jsm_selectedDriverCount');

        if (!container || !countElement) {
            console.error("Selected drivers container or count element not found");
            return;
        }

        countElement.textContent = JSM.drivers.selectedDrivers.size;

        if (JSM.drivers.selectedDrivers.size === 0) {
            container.innerHTML = '<p class="jsm-no-selection">No drivers selected</p>';
            return;
        }

        const selectedItems = JSM.drivers.allDrivers.filter(item =>
            JSM.drivers.selectedDrivers.has(item.id)
        );

        container.innerHTML = selectedItems.map(item => {
            // Format driver name from f_name and l_name
            const fullName = `${item.f_name || ''} ${item.l_name || ''}`.trim() || 'N/A';

            return `
      <div class="jsm-driver-item">
        <span>${fullName}</span>
        <button class="btn btn-outline" onclick="JSM_ToggleDriver(${item.id})">Remove</button>
      </div>
    `;
        }).join('');
    };

    // Update pagination info
    JSM.updatePaginationInfo = function() {
        const infoElement = document.getElementById('jsm_driver_pagination_info');
        if (!infoElement) return;

        const count = JSM.drivers.allDrivers.length;

        if (count > 0) {
            infoElement.textContent = `1-${count} of ${count}`;
        } else {
            infoElement.textContent = '0-0 of 0';
        }
    };

    // Filter drivers with JSM prefix
    window.JSM_FilterDrivers = function() {
        const searchInput = document.querySelector('#jsm_driverSelectionModal .jsm-search-bar');
        JSM.drivers.searchTerm = searchInput ? searchInput.value.toLowerCase() : '';
        JSM.populateDrivers();
    };

    // Save driver selection with JSM prefix
    window.JSM_SaveDriverSelection = function() {
        const manifestId = JSM.getManifestId();
        if (!manifestId) {
            console.error("No manifest ID found");
            showToast('Missing manifest ID', 'error');
            return;
        }

        // Show loading state
        const saveButton = document.querySelector('#jsm_driverSelectionModal .btn-primary');
        if (!saveButton) {
            console.error("Save button not found");
            return;
        }

        const originalText = saveButton.textContent;
        saveButton.textContent = 'Saving...';
        saveButton.disabled = true;

        // Prepare data - ensure IDs are properly formatted and valid
        const driverIds = Array.from(JSM.drivers.selectedDrivers);
        const validDriverIds = driverIds.filter(id => {
            // Check if this ID exists in our loaded drivers
            return JSM.drivers.allDrivers.some(driver => driver.id == id);
        }).map(id => parseInt(id)); // Ensure they're integers

        if (validDriverIds.length === 0) {
            showToast('Please select at least one valid driver', 'error');
            saveButton.textContent = originalText;
            saveButton.disabled = false;
            return;
        }

        const data = {
            driver_ids: validDriverIds
        };

        console.log("Selected driver set:", JSM.drivers.selectedDrivers);
        console.log("Raw driver IDs:", driverIds);
        console.log("Valid driver IDs:", validDriverIds);
        console.log("Sending data to API:", data);
        console.log("Available drivers:", JSM.drivers.allDrivers.map(d => ({id: d.id, name: d.f_name, type: typeof d.id})));

        // Check if CSRF token is available
        const csrfToken = document.querySelector('meta[name="csrf-token"]');
        if (!csrfToken) {
            console.error("CSRF token not found");
            showToast('CSRF token missing, please refresh the page', 'error');
            saveButton.textContent = originalText;
            saveButton.disabled = false;
            return;
        }

        // Send data to API
        fetch(`/manifest/${manifestId}/drivers`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': csrfToken.getAttribute('content')
                },
                body: JSON.stringify(data)
            })
            .then(response => {
                console.log("Response status:", response.status);
                return response.json().then(data => {
                    if (!response.ok) {
                        throw new Error(data.message || 'Network response was not ok');
                    }
                    return data;
                });
            })
            .then(data => {
                console.log("API response data:", data);
                if (data.success) {
                    // Show success toast
                    showToast('Drivers assigned successfully!', 'success');

                    // Close modal
                    JSM_CloseDriverModal();

                    // Optionally reload the page to show updated assignments
                    // window.location.reload();
                } else {
                    throw new Error(data.message || 'Unknown error');
                }
            })
            .catch(error => {
                console.error('Error assigning drivers:', error);
                showToast('Error assigning drivers: ' + error.message, 'error');
            })
            .finally(() => {
                // Restore button state
                saveButton.textContent = originalText;
                saveButton.disabled = false;
            });
    };

    // DOM ready event
    document.addEventListener('DOMContentLoaded', function() {
        // Add click handler for driver button
        const driverBtn = document.querySelector('[data-action="add-driver"]');
        if (driverBtn) {
            driverBtn.addEventListener('click', function(event) {
                event.preventDefault();
                JSM_OpenDriverModal();
            });
        }

        // Close modal when clicking outside
        const modal = document.getElementById('jsm_driverSelectionModal');
        if (modal) {
            modal.addEventListener('click', function(event) {
                if (event.target === this) {
                    JSM_CloseDriverModal();
                }
            });
        }
    });
</script>
