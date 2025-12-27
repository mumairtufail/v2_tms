<!-- Carrier Modal with Unique Identifiers -->
<div id="cm_carrierSelectionModal" class="modal" data-manifest-id="{{ $manifest->id }}">
  <div class="cm-modal-content">
    <span class="cm-close-btn" onclick="CM_CloseCarrierModal()">&times;</span>
    <h2>Add carrier</h2>

    <div class="cm-carrier-container">
      <div class="cm-carrier-list">
        <div class="cm-list-header">
          <h3>Carriers</h3>
          <a href="{{ route('carriers.create',['source' => 'manifest.edit', 'manifest_id' => $manifest->id]) }}" class="btn btn-outline">
            <i data-feather="plus"></i> New carrier
          </a>
        </div>

        <input type="text" class="cm-search-bar" placeholder="Search" onkeyup="CM_FilterCarriers()">

        <div id="cm_carrierItems">
          <!-- Carrier items will be populated via AJAX -->
          <p class="text-center py-4" id="cm_loadingCarriers">Loading carriers...</p>
        </div>

        <div class="cm-pagination">
          <span id="cm_carrier_pagination_info">0-0 of 0</span>
          <div class="cm-pagination-controls">
            <button class="btn btn-outline">&lt;&lt;</button>
            <button class="btn btn-outline">&lt;</button>
            <button class="btn btn-outline">&gt;</button>
            <button class="btn btn-outline">&gt;&gt;</button>
          </div>
        </div>
      </div>

      <div class="cm-carrier-list">
        <h3>Selected carriers (<span id="cm_selectedCarrierCount">0</span>)</h3>
        <div id="cm_selectedCarriers">
          <p class="cm-no-selection">No carriers selected</p>
        </div>
      </div>
    </div>

    <div class="cm-modal-footer">
      <button class="btn btn-outline" onclick="CM_CloseCarrierModal()">Cancel</button>
      <button class="btn btn-primary" onclick="CM_SaveCarrierSelection()">Save</button>
    </div>
  </div>
</div>

<style>
  #cm_carrierSelectionModal.modal {
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

  #cm_carrierSelectionModal.modal.show {
    visibility: visible;
    background: rgba(0, 0, 0, 0.5);
  }

  #cm_carrierSelectionModal .cm-modal-content {
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

  #cm_carrierSelectionModal.show .cm-modal-content {
    transform: translateY(0);
    opacity: 1;
  }

  #cm_carrierSelectionModal .cm-close-btn {
    position: absolute;
    right: 24px;
    top: 24px;
    font-size: 24px;
    cursor: pointer;
    transition: transform 0.2s ease;
  }

  #cm_carrierSelectionModal .cm-close-btn:hover {
    transform: scale(1.1);
  }

  #cm_carrierSelectionModal .cm-carrier-container {
    display: grid;
    grid-template-columns: 60% 40%;
    gap: 24px;
    margin-top: 24px;
    max-height: calc(80vh - 200px);
    overflow: visible; /* Changed from hidden to visible to allow scrolling */
  }

  #cm_carrierSelectionModal .cm-carrier-list {
    border: 1px solid #e0e0e0;
    border-radius: 8px;
    padding: 16px;
    height: 100%;
    max-height: calc(80vh - 250px); /* Added explicit max-height */
    overflow-y: auto; /* Ensures vertical scrolling works */
  }

  #cm_carrierSelectionModal .cm-list-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 16px;
  }

  #cm_carrierSelectionModal .cm-list-header h3 {
    margin: 0;
  }

  #cm_carrierSelectionModal .cm-search-bar {
    width: 100%;
    padding: 8px 12px;
    border: 1px solid #e0e0e0;
    border-radius: 4px;
    margin-bottom: 16px;
  }

  #cm_carrierSelectionModal .cm-carrier-item {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 12px;
    border: 1px solid #e0e0e0;
    border-radius: 4px;
    margin-bottom: 8px;
    transition: all 0.2s ease;
  }

  #cm_carrierSelectionModal .cm-carrier-item:hover {
    transform: translateY(-2px);
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
  }

  #cm_carrierSelectionModal .cm-status-badge {
    background: #e8f5e9;
    color: #2e7d32;
    padding: 4px 8px;
    border-radius: 4px;
    font-size: 12px;
  }

  #cm_carrierSelectionModal .btn {
    padding: 8px 16px;
    border-radius: 4px;
    cursor: pointer;
    transition: all 0.2s ease;
    font-size: 14px;
  }

  #cm_carrierSelectionModal .btn-primary {
    background: #1a73e8;
    color: white;
    border: none;
  }

  #cm_carrierSelectionModal .btn-primary:hover {
    background: #1557b0;
    transform: translateY(-1px);
  }

  #cm_carrierSelectionModal .btn-outline {
    border: 1px solid #1a73e8;
    color: #1a73e8;
    background: white;
  }

  #cm_carrierSelectionModal .btn-outline:hover {
    background: #f0f7ff;
    transform: translateY(-1px);
  }

  #cm_carrierSelectionModal .cm-modal-footer {
    display: flex;
    justify-content: flex-end;
    gap: 12px;
    margin-top: 24px;
    padding-top: 16px;
    border-top: 1px solid #e0e0e0;
  }

  #cm_carrierSelectionModal .cm-pagination {
    text-align: center;
    margin-top: 16px;
    padding: 8px 0;
    border-top: 1px solid #e0e0e0;
  }

  #cm_carrierSelectionModal .cm-pagination-controls {
    display: inline-flex;
    gap: 4px;
    margin-left: 8px;
  }

  #cm_carrierSelectionModal .cm-no-selection {
    text-align: center;
    color: #666;
    margin-top: 16px;
  }

  /* Feather icon styles */
  #cm_carrierSelectionModal [data-feather] {
    width: 16px;
    height: 16px;
    vertical-align: middle;
  }
</style>

<!-- Script for Carrier Modal with CM prefix for uniqueness -->
<script>
// CM namespace to avoid conflicts
var CM = CM || {};
CM.carriers = {
  allCarriers: [],
  selectedCarriers: new Set(),
  searchTerm: ''
};

// Helper to get manifest ID
CM.getManifestId = function() {
  const modal = document.getElementById('cm_carrierSelectionModal');
  return modal ? modal.getAttribute('data-manifest-id') : null;
};

// Open modal function with CM prefix
window.openAssignModal = function() {
  const modal = document.getElementById('cm_carrierSelectionModal');
  if (!modal) {
    console.error("Carrier modal not found");
    return;
  }
  
  // Show modal
  modal.style.display = 'flex';
  modal.offsetHeight; // Force reflow
  modal.classList.add('show');
  
  // Prevent background scrolling
  document.body.style.overflow = 'hidden';
  
  // Load carriers
  CM.loadCarriers();
};

// Close modal function with CM prefix
window.CM_CloseCarrierModal = function() {
  const modal = document.getElementById('cm_carrierSelectionModal');
  if (!modal) return;
  
  modal.classList.remove('show');
  setTimeout(() => {
    modal.style.display = 'none';
    document.body.style.overflow = ''; // Re-enable scrolling
  }, 300);
};

// Load carriers function
CM.loadCarriers = function() {
  const manifestId = CM.getManifestId();
  if (!manifestId) {
    console.error("No manifest ID found");
    return;
  }
  
  const container = document.getElementById('cm_carrierItems');
  if (!container) {
    console.error("Carrier items container not found");
    return;
  }
  
  container.innerHTML = '<p class="text-center py-4">Loading carriers...</p>';
  
  // Fetch carriers from API
  fetch(`/manifest/${manifestId}/carriers`)
    .then(response => {
      if (!response.ok) {
        throw new Error('Network response was not ok: ' + response.statusText);
      }
      return response.json();
    })
    .then(data => {
      if (data.success) {
        CM.carriers.allCarriers = data.carriers;
        
        // Initialize selected carriers
        CM.carriers.selectedCarriers.clear();
        CM.carriers.allCarriers.forEach(item => {
          if (item.is_assigned) {
            CM.carriers.selectedCarriers.add(item.id);
          }
        });
        
        CM.populateCarriers();
        CM.updateSelectedCarriersList();
        CM.updatePaginationInfo();
      } else {
        container.innerHTML = '<p class="text-center py-4 text-danger">Failed to load carriers</p>';
      }
    })
    .catch(error => {
      console.error('Error fetching carriers:', error);
      container.innerHTML = `<p class="text-center py-4 text-danger">Error: ${error.message}</p>`;
    });
};

// Populate carriers list - now shows all carriers including selected ones
CM.populateCarriers = function() {
  const container = document.getElementById('cm_carrierItems');
  if (!container) {
    console.error("Carrier items container not found");
    return;
  }

  // Apply search filter
  const filteredCarriers = CM.carriers.allCarriers.filter(item => {
    const carrierName = item.carrier_name || '';
    const dotId = item.dot_id ? item.dot_id.toString() : '';
    const docketNumber = item.docket_number ? item.docket_number.toString() : '';
    const searchTerm = CM.carriers.searchTerm || '';
    
    return carrierName.toLowerCase().includes(searchTerm.toLowerCase()) || 
           dotId.toLowerCase().includes(searchTerm.toLowerCase()) ||
           docketNumber.toLowerCase().includes(searchTerm.toLowerCase());
  });
  
  if (filteredCarriers.length === 0) {
    container.innerHTML = '<p class="text-center py-4">No carriers found</p>';
    return;
  }
  
  container.innerHTML = filteredCarriers.map(item => {
    const isSelected = CM.carriers.selectedCarriers.has(item.id);
    const dotId = item.dot_id ? `DOT: ${item.dot_id}` : 'No DOT';
    
    return `
      <div class="cm-carrier-item">
        <div>
          <span class="cm-status-badge">Carrier</span>
          <span style="margin-left: 10px;">${item.carrier_name}</span>
        </div>
        <div>
          <span style="color: #666; margin-right: 10px;">${dotId}</span>
          <button class="btn ${isSelected ? 'btn-primary' : 'btn-outline'}" 
                  onclick="CM_ToggleCarrier(${item.id})">
            ${isSelected ? 'Selected' : 'Select'}
          </button>
        </div>
      </div>
    `;
  }).join('');
};

// Toggle carrier selection with CM prefix
window.CM_ToggleCarrier = function(id) {
  if (CM.carriers.selectedCarriers.has(id)) {
    CM.carriers.selectedCarriers.delete(id);
  } else {
    CM.carriers.selectedCarriers.add(id);
  }
  CM.populateCarriers();
  CM.updateSelectedCarriersList();
};

// Update selected carriers list
CM.updateSelectedCarriersList = function() {
  const container = document.getElementById('cm_selectedCarriers');
  const countElement = document.getElementById('cm_selectedCarrierCount');
  
  if (!container || !countElement) {
    console.error("Selected carriers container or count element not found");
    return;
  }
  
  countElement.textContent = CM.carriers.selectedCarriers.size;
  
  if (CM.carriers.selectedCarriers.size === 0) {
    container.innerHTML = '<p class="cm-no-selection">No carriers selected</p>';
    return;
  }

  const selectedItems = CM.carriers.allCarriers.filter(item => 
    CM.carriers.selectedCarriers.has(item.id)
  );
  
  container.innerHTML = selectedItems.map(item => {
    return `
      <div class="cm-carrier-item">
        <span>${item.carrier_name}</span>
        <button class="btn btn-outline" onclick="CM_ToggleCarrier(${item.id})">Remove</button>
      </div>
    `;
  }).join('');
};

// Update pagination info
CM.updatePaginationInfo = function() {
  const infoElement = document.getElementById('cm_carrier_pagination_info');
  if (!infoElement) return;
  
  const count = CM.carriers.allCarriers.length;
  
  if (count > 0) {
    infoElement.textContent = `1-${count} of ${count}`;
  } else {
    infoElement.textContent = '0-0 of 0';
  }
};

// Filter carriers with CM prefix
window.CM_FilterCarriers = function() {
  const searchInput = document.querySelector('#cm_carrierSelectionModal .cm-search-bar');
  CM.carriers.searchTerm = searchInput ? searchInput.value.toLowerCase() : '';
  CM.populateCarriers();
};

// Save carrier selection with CM prefix
window.CM_SaveCarrierSelection = function() {
  const manifestId = CM.getManifestId();
  if (!manifestId) {
    console.error("No manifest ID found");
    showToast('Missing manifest ID', 'error');
    return;
  }
  
  // Show loading state
  const saveButton = document.querySelector('#cm_carrierSelectionModal .btn-primary');
  if (!saveButton) {
    console.error("Save button not found");
    return;
  }
  
  const originalText = saveButton.textContent;
  saveButton.textContent = 'Saving...';
  saveButton.disabled = true;
  
  // Prepare data
  const data = {
    carrier_ids: Array.from(CM.carriers.selectedCarriers)
  };
  
  console.log("Sending data to API:", data);
  
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
  fetch(`/manifest/${manifestId}/carriers`, {
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
      showToast('Carriers assigned successfully!', 'success');
      
      // Close modal
      CM_CloseCarrierModal();
      
      // Optionally reload the page to show updated assignments
      // window.location.reload();
    } else {
      throw new Error(data.message || 'Unknown error');
    }
  })
  .catch(error => {
    console.error('Error assigning carriers:', error);
    showToast('Error assigning carriers: ' + error.message, 'error');
  })
  .finally(() => {
    // Restore button state
    saveButton.textContent = originalText;
    saveButton.disabled = false;
  });
};

// DOM ready event
document.addEventListener('DOMContentLoaded', function() {
  // Close modal when clicking outside
  const modal = document.getElementById('cm_carrierSelectionModal');
  if (modal) {
    modal.addEventListener('click', function(event) {
      if (event.target === this) {
        CM_CloseCarrierModal();
      }
    });
  }
});
</script>