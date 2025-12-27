<!-- Modal -->
<div id="equipmentModal" class="modal">
  <div class="modal-content">
    <span class="close" onclick="closeModal()">&times;</span>
    <h2>Add equipment</h2>

    <div class="equipment-container">
      <div class="equipment-list">
        <div class="list-header">
          <h3>Equipment</h3>
          <a href="{{ route('equipment.create')}}" class="btn btn-outline">
            <i data-feather="plus"></i> New equipment
          </a>
        </div>

        <input type="text" class="search-bar" placeholder="Search" onkeyup="filterEquipment()">

        <div class="filter-section">
          <button class="btn btn-outline" onclick="toggleFilter('type')">
            Types: All <i data-feather="chevron-down"></i>
          </button>
          <button class="btn btn-outline" onclick="toggleFilter('status')">
            Status: Available <i data-feather="chevron-down"></i>
          </button>
        </div>

        <div id="equipmentItems">
          <!-- Equipment items populate here -->
        </div>

        <div class="pagination">
          1-20 of 25
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
    overflow: hidden;
  }

  #equipmentModal .equipment-list {
    border: 1px solid #e0e0e0;
    border-radius: 8px;
    padding: 16px;
    height: 100%;
    overflow-y: auto;
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
</style>

<script>
  // Sample equipment data
const equipment = [
  { id: 1, name: 'Default Trailer', type: 'Trailer', status: 'AVL' },
  { id: 2, name: 'Default Vehicle', type: 'Vehicle', status: 'AVL' },
  { id: 3, name: 'INAU145100', type: 'Trailer', status: 'AVL' },
  { id: 4, name: 'INAU145101', type: 'Trailer', status: 'AVL' },
  { id: 5, name: 'INAU145102', type: 'Trailer', status: 'AVL' }
];

const selectedEquipment = new Set();

function openModal() {
  const modal = document.getElementById('equipmentModal');
  modal.style.display = 'flex';
  // Trigger reflow
  modal.offsetHeight;
  modal.classList.add('show');
  populateEquipment();
  document.body.style.overflow = 'hidden';
}

function closeModal() {
  const modal = document.getElementById('equipmentModal');
  modal.classList.remove('show');
  setTimeout(() => {
    modal.style.display = 'none';
    document.body.style.overflow = '';
  }, 300);
  selectedEquipment.clear();
  updateSelectedList();
}

function populateEquipment() {
  const container = document.getElementById('equipmentItems');
  container.innerHTML = equipment.map(item => `
    <div class="equipment-item">
      <div>
        <span class="status-badge">${item.status}</span>
        <span style="margin-left: 10px;">${item.name}</span>
      </div>
      <div>
        <span style="color: #666; margin-right: 10px;">${item.type}</span>
        <button class="btn btn-outline" onclick="selectEquipment(${item.id})">Select</button>
      </div>
    </div>
  `).join('');
}

function selectEquipment(id) {
  const item = equipment.find(e => e.id === id);
  selectedEquipment.add(id);
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

  container.innerHTML = Array.from(selectedEquipment).map(id => {
    const item = equipment.find(e => e.id === id);
    return `
      <div class="equipment-item">
        <span>${item.name}</span>
        <button class="btn btn-outline" onclick="removeSelection(${id})">Remove</button>
      </div>
    `;
  }).join('');
}

function removeSelection(id) {
  selectedEquipment.delete(id);
  updateSelectedList();
}

function saveSelection() {
  // Handle saving the selection
  closeModal();
}

function filterEquipment() {
  const searchTerm = document.querySelector('.search-bar').value.toLowerCase();
  const filteredEquipment = equipment.filter(item => 
    item.name.toLowerCase().includes(searchTerm) ||
    item.type.toLowerCase().includes(searchTerm)
  );
  
  const container = document.getElementById('equipmentItems');
  container.innerHTML = filteredEquipment.map(item => `
    <div class="equipment-item">
      <div>
        <span class="status-badge">${item.status}</span>
        <span style="margin-left: 10px;">${item.name}</span>
      </div>
      <div>
        <span style="color: #666; margin-right: 10px;">${item.type}</span>
        <button class="btn btn-outline" onclick="selectEquipment(${item.id})">Select</button>
      </div>
    </div>
  `).join('');
}

function toggleFilter(type) {
  // Handle filter toggling
  // You can implement dropdown menus for filters here
}

function showNewEquipmentForm() {
  // Handle showing new equipment form
  // You can implement a new form or modal here
}

// Close modal when clicking outside
window.onclick = function(event) {
  const modal = document.getElementById('equipmentModal');
  if (event.target === modal) {
    closeModal();
  }
}

// Initialize Feather icons
document.addEventListener('DOMContentLoaded', function() {
  feather.replace();
});
</script>