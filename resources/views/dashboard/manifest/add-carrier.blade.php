
<!-- Add Carrier Modal -->
<div id="addCarrierModal" class="modal add-carrier-modal">
  <div class="modal-content">
    <span class="close" onclick="closeAssignModal()">&times;</span>
    <h2>Assign Carrier</h2>
    
    <!-- Tab Navigation -->
    <ul class="tab-nav">
      <li class="tab-item active" onclick="showTab('assignCarrier')">Assign Carrier</li>
      <li class="tab-item" onclick="showTab('assignDriver')">Assign Driver</li>
      <li class="tab-item" onclick="showTab('postLoadBoard')">Post to Load Board</li>
    </ul>
    
    <!-- Tab Content -->
    <div class="tab-content">
      <!-- Assign Carrier Tab -->
      <div id="assignCarrier" class="tab-panel active">
        <p>To search for a carrier, please enter at least 3 characters for results.</p>
        <div class="input-group">
          <input type="text" id="carrierSearch" placeholder="Search Carrier Name" onkeyup="filterCarriers()">
          <button class="btn btn-outline" onclick="#">+ Create Carrier</button>
        </div>
        <div id="carrierList" class="list-container">
          <p>No results found.</p>
        </div>
      </div>

      <!-- Assign Driver Tab -->
      <div id="assignDriver" class="tab-panel">
        <p>To search for a driver, please enter at least 3 characters for results.</p>
        <div class="driver-container">
          <div class="driver-list">
            <h3>Available Drivers</h3>
            <input type="text" id="driverSearch" placeholder="Search Driver Name" onkeyup="filterDrivers()">
            <div id="driverList" class="list-container">
              <p>No drivers found.</p>
            </div>
          </div>
          <div class="driver-selected">
            <h3>Selected Drivers (<span id="selectedDriverCount">0</span>)</h3>
            <div id="selectedDriverList">
              <p>No drivers selected.</p>
            </div>
          </div>
        </div>
      </div>

      <!-- Post to Load Board Tab -->
      <div id="postLoadBoard" class="tab-panel">
        <h3>Select Load Board</h3>
        <label>
          <input type="radio" name="loadBoard" value="TruckStop"> TruckStop
        </label>
        <label>
          <input type="radio" name="loadBoard" value="DAT"> DAT
        </label>
        <p>
          Connect your load board service to post loads directly. Contact your administrator to set this up.
        </p>
      </div>
    </div>

    <div class="modal-footer">
      <button class="btn btn-outline" onclick="closeAssignModal()">Cancel</button>
      <button class="btn btn-primary" onclick="saveCarrier()">Save</button>
    </div>
  </div>
</div>

<script>
    // Open the Add Carrier Modal
function openAssignModal() {
  const modal = document.getElementById('addCarrierModal');
  modal.style.display = 'flex';
}

// Close the Add Carrier Modal
function closeAssignModal() {
  const modal = document.getElementById('addCarrierModal');
  modal.style.display = 'none';
}

    // Tab Switching
function showTab(tabId) {
  document.querySelectorAll('.tab-panel').forEach(panel => {
    panel.classList.remove('active');
  });
  document.getElementById(tabId).classList.add('active');

  document.querySelectorAll('.tab-item').forEach(item => {
    item.classList.remove('active');
  });
  document.querySelector(`[onclick="showTab('${tabId}')"]`).classList.add('active');
}

// Open Modal
function openAddCarrierModal() {
  document.getElementById('addCarrierModal').style.display = 'flex';
}

// Close Modal
function closeAddCarrierModal() {
  document.getElementById('addCarrierModal').style.display = 'none';
}

// Carrier Data
const carriers = ['Carrier 1', 'Carrier 2', 'Carrier 3'];
const drivers = ['Driver A', 'Driver B', 'Driver C'];
const selectedDrivers = new Set();

// Filter Carriers
function filterCarriers() {
  const query = document.getElementById('carrierSearch').value.toLowerCase();
  const list = document.getElementById('carrierList');
  const results = carriers.filter(carrier => carrier.toLowerCase().includes(query));

  list.innerHTML = results.length
    ? results.map(carrier => `<p>${carrier}</p>`).join('')
    : '<p>No results found.</p>';
}

// Filter Drivers
function filterDrivers() {
  const query = document.getElementById('driverSearch').value.toLowerCase();
  const list = document.getElementById('driverList');
  const results = drivers.filter(driver => driver.toLowerCase().includes(query));

  list.innerHTML = results.length
    ? results.map(driver => `
        <div>
          ${driver} <button onclick="selectDriver('${driver}')">Select</button>
        </div>
      `).join('')
    : '<p>No drivers found.</p>';
}

// Select Driver
function selectDriver(driver) {
  selectedDrivers.add(driver);
  updateSelectedDrivers();
}

// Remove Driver
function removeDriver(driver) {
  selectedDrivers.delete(driver);
  updateSelectedDrivers();
}

// Update Selected Drivers
function updateSelectedDrivers() {
  const list = document.getElementById('selectedDriverList');
  const count = document.getElementById('selectedDriverCount');

  count.textContent = selectedDrivers.size;
  list.innerHTML = selectedDrivers.size
    ? Array.from(selectedDrivers).map(driver => `
        <div>
          ${driver} <button onclick="removeDriver('${driver}')">Remove</button>
        </div>
      `).join('')
    : '<p>No drivers selected.</p>';
}

// Save
function saveCarrier() {
  console.log('Carrier saved!');
  closeAddCarrierModal();
}

    </script>

<style>
    .add-carrier-modal {
  display: none;
  position: fixed;
  top: 0;
  left: 0;
  width: 100%;
  height: 100%;
  background: rgba(0, 0, 0, 0.5);
  justify-content: center;
  align-items: center;
  z-index: 1000;
}

.add-carrier-modal .modal-content {
  background: #fff;
  padding: 20px;
  border-radius: 8px;
  width: 80%;
  max-width: 800px;
  position: relative;
}

.add-carrier-modal .close {
  position: absolute;
  top: 10px;
  right: 10px;
  font-size: 20px;
  cursor: pointer;
}

/* Tab Styles */
.add-carrier-modal .tab-nav {
  display: flex;
  border-bottom: 1px solid #ddd;
}

.add-carrier-modal .tab-item {
  padding: 10px 20px;
  cursor: pointer;
  border-bottom: 2px solid transparent;
  transition: border-color 0.3s ease;
}

.add-carrier-modal .tab-item.active {
  border-color: #007bff;
}

.add-carrier-modal .tab-content {
  padding: 20px;
}

.add-carrier-modal .tab-panel {
  display: none;
}

.add-carrier-modal .tab-panel.active {
  display: block;
}

/* Utility */
.add-carrier-modal.hidden {
  display: none;
}

</style>