<!-- Modal -->
<div id="equipmentModal" class="modal">
    <div class="modal-content">
        <span class="close" onclick="closeModal()">&times;</span>
        <h2>Add equipment</h2>

        <div class="equipment-container">
            <div class="equipment-list">
                <div class="list-header">
                    <h3>Equipment</h3>
                    <a href="{{ route('equipment.create', ['source' => 'manifest.edit', 'manifest_id' => $manifest->id]) }}"
                        class="btn btn-outline">
                        <i data-feather="plus"></i> New equipment
                    </a>
                </div>

                <input type="text" class="search-bar" placeholder="Search" onkeyup="filterEquipment()">


                <div id="equipmentItems">
                    <!-- Equipment items will be populated via AJAX -->
                    <p class="text-center py-4" id="loadingEquipment">Loading equipment...</p>
                </div>

                <div class="pagination">
                    <span id="pagination-info">0-0 of 0</span>
                    <div class="pagination-controls">
                        <button class="btn btn-outline">&lt;&lt;</button>
                        <button class="btn btn-outline">&lt;</button>
                        <button class="btn btn-outline">&gt;</button>
                        <button class="btn btn-outline">&gt;&gt;</button>
                    </div>
                </div>
            </div>

            <div class="equipment-list">
                <h3>Selected equipment (<span id="selectedCount">0</span>)</h3>
                <div id="selectedEquipment">
                    <p class="no-selection">No equipment selected</p>
                </div>
            </div>
        </div>

        <div class="modal-footer">
            <button class="btn btn-outline" onclick="closeModal()">Cancel</button>
            <button class="btn btn-primary" onclick="saveSelection()">Save</button>
        </div>
    </div>
</div>

<style>
    #equipmentModal.modal {
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

    #equipmentModal.modal.show {
        visibility: visible;
        background: rgba(0, 0, 0, 0.5);
    }

    #equipmentModal .modal-content {
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
    }

    #equipmentModal.show .modal-content {
        transform: translateY(0);
        opacity: 1;
    }

    #equipmentModal .close {
        position: absolute;
        right: 24px;
        top: 24px;
        font-size: 24px;
        cursor: pointer;
        transition: transform 0.2s ease;
    }

    #equipmentModal .close:hover {
        transform: scale(1.1);
    }

    #equipmentModal .equipment-container {
        display: grid;
        grid-template-columns: 60% 40%;
        gap: 24px;
        margin-top: 24px;
        max-height: calc(80vh - 200px);
        overflow: visible;
        /* Changed from hidden to visible to allow scrolling */
    }

    #equipmentModal .equipment-list {
        border: 1px solid #e0e0e0;
        border-radius: 8px;
        padding: 16px;
        height: 100%;
        max-height: calc(80vh - 250px);
        /* Added explicit max-height */
        overflow-y: auto;
        /* Ensures vertical scrolling works */
    }

    #equipmentModal .list-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 16px;
    }

    #equipmentModal .list-header h3 {
        margin: 0;
    }

    #equipmentModal .search-bar {
        width: 100%;
        padding: 8px 12px;
        border: 1px solid #e0e0e0;
        border-radius: 4px;
        margin-bottom: 16px;
    }

    #equipmentModal .filter-section {
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

    #equipmentModal .equipment-item {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding: 12px;
        border: 1px solid #e0e0e0;
        border-radius: 4px;
        margin-bottom: 8px;
        transition: all 0.2s ease;
    }

    #equipmentModal .equipment-item:hover {
        transform: translateY(-2px);
        box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
    }

    #equipmentModal .status-badge {
        background: #e8f5e9;
        color: #2e7d32;
        padding: 4px 8px;
        border-radius: 4px;
        font-size: 12px;
    }

    #equipmentModal .btn {
        padding: 8px 16px;
        border-radius: 4px;
        cursor: pointer;
        transition: all 0.2s ease;
        font-size: 14px;
    }

    #equipmentModal .btn-primary {
        background: #1a73e8;
        color: white;
        border: none;
    }

    #equipmentModal .btn-primary:hover {
        background: #1557b0;
        transform: translateY(-1px);
    }

    #equipmentModal .btn-outline {
        border: 1px solid #1a73e8;
        color: #1a73e8;
        background: white;
    }

    #equipmentModal .btn-outline:hover {
        background: #f0f7ff;
        transform: translateY(-1px);
    }

    #equipmentModal .modal-footer {
        display: flex;
        justify-content: flex-end;
        gap: 12px;
        margin-top: 24px;
        padding-top: 16px;
        border-top: 1px solid #e0e0e0;
    }

    #equipmentModal .pagination {
        text-align: center;
        margin-top: 16px;
        padding: 8px 0;
        border-top: 1px solid #e0e0e0;
    }

    #equipmentModal .pagination-controls {
        display: inline-flex;
        gap: 4px;
        margin-left: 8px;
    }

    #equipmentModal .no-selection {
        text-align: center;
        color: #666;
        margin-top: 16px;
    }

    /* Feather icon styles */
    #equipmentModal [data-feather] {
        width: 16px;
        height: 16px;
        vertical-align: middle;
    }

    /* Toast notification styles */
    .toast-container {
        position: fixed;
        top: 20px;
        right: 20px;
        z-index: 9999;
        min-width: 300px;
        max-width: 400px;
    }

    .toast {
        background: #ffffff;
        border: none;
        border-radius: 12px;
        overflow: hidden;
        margin-bottom: 1rem;
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
        opacity: 0;
        transform: translateX(100%);
        animation: slideIn 0.3s forwards;
    }

    .toast.success {
        border-left: 4px solid #28a745;
    }

    .toast.error {
        border-left: 4px solid #dc3545;
    }

    .toast-content {
        display: flex;
        align-items: center;
        padding: 16px;
    }

    .toast-icon {
        margin-right: 12px;
        font-size: 20px;
    }

    .toast-message {
        flex-grow: 1;
        font-size: 14px;
    }

    .toast-close {
        background: none;
        border: none;
        font-size: 20px;
        cursor: pointer;
        opacity: 0.7;
    }

    .toast-progress {
        height: 3px;
        width: 100%;
        animation: progress 3s linear forwards;
    }

    .toast-progress.success {
        background-color: #28a745;
    }

    .toast-progress.error {
        background-color: #dc3545;
    }

    @keyframes slideIn {
        from {
            transform: translateX(100%);
            opacity: 0;
        }

        to {
            transform: translateX(0);
            opacity: 1;
        }
    }

    @keyframes slideOut {
        from {
            transform: translateX(0);
            opacity: 1;
        }

        to {
            transform: translateX(100%);
            opacity: 0;
        }
    }

    @keyframes progress {
        0% {
            width: 100%;
        }

        100% {
            width: 0%;
        }
    }
</style>

<script>
    // Global variables
    const manifestId = {{ $manifest->id }};
    let allEquipment = [];
    const selectedEquipment = new Set();
    let searchTerm = '';

    // Toast notification function
    function showToast(message, type = 'success') {
        // Create toast container if it doesn't exist
        let container = document.querySelector('.toast-container');
        if (!container) {
            container = document.createElement('div');
            container.className = 'toast-container';
            document.body.appendChild(container);
        }

        // Create toast element
        const toast = document.createElement('div');
        toast.className = `toast ${type}`;

        // Create toast content
        const content = document.createElement('div');
        content.className = 'toast-content';

        // Add appropriate icon
        const icon = document.createElement('span');
        icon.className = 'toast-icon';
        icon.innerHTML = type === 'success' ?
            '<i class="bi bi-check-circle-fill" style="color: #28a745;"></i>' :
            '<i class="bi bi-x-circle-fill" style="color: #dc3545;"></i>';

        // Add message
        const messageElement = document.createElement('span');
        messageElement.className = 'toast-message';
        messageElement.textContent = message;

        // Add close button
        const closeButton = document.createElement('button');
        closeButton.className = 'toast-close';
        closeButton.innerHTML = '&times;';
        closeButton.onclick = function() {
            removeToast(toast);
        };

        // Add progress bar
        const progress = document.createElement('div');
        progress.className = `toast-progress ${type}`;

        // Assemble toast
        content.appendChild(icon);
        content.appendChild(messageElement);
        content.appendChild(closeButton);
        toast.appendChild(content);
        toast.appendChild(progress);

        // Add to container
        container.appendChild(toast);

        // Auto remove after 3 seconds
        setTimeout(() => {
            removeToast(toast);
        }, 3000);
    }

    function removeToast(toast) {
        toast.style.animation = 'slideOut 0.3s forwards';
        setTimeout(() => {
            if (toast.parentNode) {
                toast.parentNode.removeChild(toast);
            }
        }, 300);
    }

    function openModal() {
        const modal = document.getElementById('equipmentModal');
        modal.style.display = 'flex';
        // Trigger reflow
        modal.offsetHeight;
        modal.classList.add('show');
        loadEquipment();
        document.body.style.overflow = 'hidden';
    }

    function closeModal() {
        const modal = document.getElementById('equipmentModal');
        modal.classList.remove('show');
        setTimeout(() => {
            modal.style.display = 'none';
            document.body.style.overflow = '';
        }, 300);
        // Don't clear selection on close
    }

    function loadEquipment() {
        const container = document.getElementById('equipmentItems');
        container.innerHTML = '<p class="text-center py-4">Loading equipment...</p>';

        // Fetch equipment from API
        fetch(`/manifest/${manifestId}/equipment`)
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    allEquipment = data.equipment;

                    // Initialize selected equipment from what's already assigned
                    selectedEquipment.clear();
                    allEquipment.forEach(item => {
                        if (item.is_assigned) {
                            selectedEquipment.add(item.id);
                        }
                    });

                    populateEquipment();
                    updateSelectedList();
                    updatePaginationInfo();
                } else {
                    container.innerHTML = '<p class="text-center py-4 text-danger">Failed to load equipment</p>';
                }
            })
            .catch(error => {
                console.error('Error fetching equipment:', error);
                container.innerHTML = '<p class="text-center py-4 text-danger">Error loading equipment</p>';
            });
    }

    function populateEquipment() {
        const container = document.getElementById('equipmentItems');

        // Filter equipment based on search term
        const filteredEquipment = allEquipment.filter(item =>
            item.name.toLowerCase().includes(searchTerm.toLowerCase()) ||
            (item.type && item.type.toLowerCase().includes(searchTerm.toLowerCase()))
        );

        if (filteredEquipment.length === 0) {
            container.innerHTML = '<p class="text-center py-4">No equipment found</p>';
            return;
        }

        container.innerHTML = filteredEquipment.map(item => `
    <div class="equipment-item">
      <div>
        <span class="status-badge">${item.status || 'AVL'}</span>
        <span style="margin-left: 10px;">${item.name}</span>
      </div>
      <div>
        <span style="color: #666; margin-right: 10px;">${item.type || 'Not specified'}</span>
        <button class="btn ${selectedEquipment.has(item.id) ? 'btn-primary' : 'btn-outline'}" 
                onclick="toggleEquipment(${item.id})">
          ${selectedEquipment.has(item.id) ? 'Selected' : 'Select'}
        </button>
      </div>
    </div>
  `).join('');
    }

    function toggleEquipment(id) {
        if (selectedEquipment.has(id)) {
            selectedEquipment.delete(id);
        } else {
            selectedEquipment.add(id);
        }
        populateEquipment();
        updateSelectedList();
    }

    function updateSelectedList() {
        const container = document.getElementById('selectedEquipment');
        const countElement = document.getElementById('selectedCount');

        countElement.textContent = selectedEquipment.size;

        if (selectedEquipment.size === 0) {
            container.innerHTML = '<p class="no-selection">No equipment selected</p>';
            return;
        }

        const selectedItems = allEquipment.filter(item => selectedEquipment.has(item.id));

        container.innerHTML = selectedItems.map(item => `
    <div class="equipment-item">
      <span>${item.name}</span>
      <button class="btn btn-outline" onclick="toggleEquipment(${item.id})">Remove</button>
    </div>
  `).join('');
    }

    function updatePaginationInfo() {
        const infoElement = document.getElementById('pagination-info');
        if (allEquipment.length > 0) {
            infoElement.textContent = `1-${allEquipment.length} of ${allEquipment.length}`;
        } else {
            infoElement.textContent = '0-0 of 0';
        }
    }

    function saveSelection() {
        // Show loading state
        const saveButton = document.querySelector('#equipmentModal .btn-primary');
        const originalText = saveButton.textContent;
        saveButton.textContent = 'Saving...';
        saveButton.disabled = true;

        // Prepare data for API
        const data = {
            equipment_ids: Array.from(selectedEquipment)
        };

        // Send data to API
        fetch(`/manifest/${manifestId}/equipment`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                },
                body: JSON.stringify(data)
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    // Show success toast notification
                    showToast('Equipment assigned successfully!', 'success');

                    // Close modal
                    closeModal();

                    // Optionally reload the page to show updated assignments
                    // window.location.reload();
                } else {
                    showToast('Failed to assign equipment: ' + data.message, 'error');
                }
            })
            .catch(error => {
                console.error('Error assigning equipment:', error);
                showToast('Error assigning equipment. Please try again.', 'error');
            })
            .finally(() => {
                // Restore button state
                saveButton.textContent = originalText;
                saveButton.disabled = false;
            });
    }

    function filterEquipment() {
        searchTerm = document.querySelector('.search-bar').value.toLowerCase();
        populateEquipment();
    }

    function toggleFilter(type) {
        // You can implement filter dropdowns here
        // This is a placeholder for future filter functionality
    }

    // Close modal when clicking outside
    window.addEventListener('click', function(event) {
        const modal = document.getElementById('equipmentModal');
        if (event.target === modal) {
            closeModal();
        }
    });
</script>
