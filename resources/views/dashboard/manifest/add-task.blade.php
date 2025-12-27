<!-- Add Task Modal -->
<div class="modal fade add-task-modal" id="addTaskModal" tabindex="-1" data-bs-backdrop="static" data-bs-keyboard="false">
    <div class="modal-dialog modal-lg modal-dialog-scrollable">
        <div class="modal-content">
            <!-- Modal Header -->
            <div class="modal-header">
                <h4 class="modal-title fw-bold">Add Task</h4>
                <button type="button" class="close" onclick="closeTaskModal()">&times;</button>
            </div>

            <!-- Modal Body -->
            <div class="modal-body">
                <form id="taskForm" class="task-form">
                    <!-- Basic Information Section -->
                    <div class="form-section">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label">MANIFEST FULL ID</label>
                                <input type="text" class="form-control bg-light" value="INVVM3580" readonly>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Type <span class="text-danger">*</span></label>
                                <select class="form-select" required>
                                    <option value="" selected disabled>Type (Required)</option>
                                    <option value="Pack">Pack</option>
                                    <option value="Unpack">Unpack</option>
                                    <option value="Setup">Setup</option>
                                </select>
                            </div>
                        </div>
                    </div>

                    <!-- Time Schedule Section -->
                    <div class="form-section">
                        <div class="row g-3">
                            <div class="col-md-3">
                                <label class="form-label">Task Start Date</label>
                                <input type="date" class="form-control" required>
                            </div>
                            <div class="col-md-3">
                                <label class="form-label">Task Start Time</label>
                                <input type="time" class="form-control" required>
                            </div>
                            <div class="col-md-3">
                                <label class="form-label">Task End Date</label>
                                <input type="date" class="form-control" required>
                            </div>
                            <div class="col-md-3">
                                <label class="form-label">Task End Time</label>
                                <input type="time" class="form-control" required>
                            </div>
                        </div>
                    </div>

                    <!-- Assignment Section -->
                    <div class="form-section">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label">Assignee</label>
                                <div class="search-input-group">
                                    <i class="fas fa-search search-icon"></i>
                                    <input type="text" class="form-control search-control" placeholder="Search for a user">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Trailer ID</label>
                                <input type="text" class="form-control" placeholder="Enter trailer ID">
                            </div>
                        </div>
                    </div>

                    <!-- Additional Details Section -->
                    <div class="form-section">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label">Security ID</label>
                                <input type="text" class="form-control" placeholder="Enter security ID">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Hours</label>
                                <input type="number" class="form-control" placeholder="Enter hours" min="0">
                            </div>
                        </div>
                    </div>

                    <!-- Notes Section -->
                    <div class="form-section">
                        <label class="form-label">Notes</label>
                        <textarea class="form-control" rows="3" placeholder="Add notes here"></textarea>
                    </div>

                    <!-- Upload Section -->
                    <div class="form-section mb-0">
                        <label class="form-label">Upload</label>
                        <div class="upload-area">
                            <button type="button" class="btn btn-link text-primary upload-btn">
                                <i class="fas fa-plus me-1" data-feather="file-text"></i>Upload Document
                            </button>
                            <input type="file" class="d-none" id="taskFileUpload">
                        </div>
                    </div>
                </form>
            </div>

            <!-- Modal Footer -->
            <div class="modal-footer">
            <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
            <button type="button" class="btn btn-primary" onclick="saveTask()">Save</button>
            </div>
        </div>
    </div>
</div>

<style>
    /* Custom close button styling */
.add-task-modal .btn-close {
    filter: invert(1); /* Makes the close icon white */
    opacity: 0.8;
    transition: opacity 0.2s ease-in-out;
}

.add-task-modal .btn-close:hover {
    opacity: 1;
}
/* Modal Specific Styles */
.add-task-modal .modal-content {
    border: none;
    border-radius: 0.75rem;
    box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
}

.add-task-modal .modal-header {
    padding: 1.5rem;
    border-bottom: 1px solid #f0f0f0;
    background-color: #fff;
    border-top-left-radius: 0.75rem;
    border-top-right-radius: 0.75rem;
}

.add-task-modal .modal-body {
    padding: 1.5rem;
}

.add-task-modal .modal-footer {
    padding: 1.25rem 1.5rem;
    border-top: 1px solid #f0f0f0;
    background-color: #fff;
}

/* Form Sections */
.add-task-modal .form-section {
    margin-bottom: 1.5rem;
}

.add-task-modal .form-label {
    display: block;
    margin-bottom: 0.5rem;
    font-size: 0.875rem;
    color: #6c757d;
    font-weight: 500;
}

/* Search Input Styling */
.add-task-modal .search-input-group {
    position: relative;
}

.add-task-modal .search-icon {
    position: absolute;
    left: 1rem;
    top: 50%;
    transform: translateY(-50%);
    color: #adb5bd;
    z-index: 4;
}

.add-task-modal .search-control {
    padding-left: 2.5rem;
}

/* Form Controls */
.add-task-modal .form-control,
.add-task-modal .form-select {
    padding: 0.75rem 1rem;
    border-radius: 0.5rem;
    border: 1px solid #dee2e6;
    font-size: 0.9375rem;
    transition: all 0.2s ease-in-out;
}

.add-task-modal .form-control:focus,
.add-task-modal .form-select:focus {
    border-color: #86b7fe;
    box-shadow: 0 0 0 0.25rem rgba(13, 110, 253, 0.15);
}

.add-task-modal .form-control[readonly] {
    background-color: #f8f9fa;
}

/* Upload Button */
.add-task-modal .upload-btn {
    text-decoration: none;
    padding: 0;
}

.add-task-modal .upload-btn:hover {
    text-decoration: underline;
}

/* Buttons */
.add-task-modal .btn {
    padding: 0.75rem 1.5rem;
    font-weight: 500;
    border-radius: 0.5rem;
    transition: all 0.2s ease-in-out;
}

.add-task-modal .btn-primary {
    background-color: #0d6efd;
    border-color: #0d6efd;
}

.add-task-modal .btn-outline-secondary {
    color: #6c757d;
    border-color: #dee2e6;
}

.add-task-modal .btn-outline-secondary:hover {
    background-color: #f8f9fa;
    border-color: #dee2e6;
    color: #495057;
}

/* Responsive Adjustments */
@media (max-width: 768px) {
    .add-task-modal .modal-dialog {
        margin: 0.5rem;
    }
    
    .add-task-modal .modal-header,
    .add-task-modal .modal-body,
    .add-task-modal .modal-footer {
        padding: 1rem;
    }
    
    .add-task-modal .row {
        row-gap: 1rem;
    }
}

/* Ensure correct stacking and scrolling */
.modal.fade.show {
    display: block;
    background-color: rgba(0, 0, 0, 0.5);
}

.modal-open {
    overflow: hidden;
}

/* Add a style for the .close button if desired */
.close {
    float: right;
    font-size: 1.5rem;
    font-weight: 700;
    line-height: 1;
    color: #000;
    opacity: 0.5;
    background: none;
    border: none;
    cursor: pointer;
}

.close:hover {
    opacity: 0.75;
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // File upload handling
    const uploadBtn = document.querySelector('.upload-btn');
    const fileInput = document.getElementById('taskFileUpload');

    if (uploadBtn && fileInput) {
        uploadBtn.addEventListener('click', () => {
            fileInput.click();
        });

        fileInput.addEventListener('change', handleFileUpload);
    }
});

function handleFileUpload(event) {
    const file = event.target.files[0];
    if (file) {
        // Handle file upload logic here
        console.log('File selected:', file.name);
    }
}

function saveTask() {
    const form = document.getElementById('taskForm');
    
    // Basic validation
    if (!form.checkValidity()) {
        form.reportValidity();
        return;
    }
    
    // Collect form data
    const formData = new FormData(form);
    
    try {
        // Add your API call here
        console.log('Saving task...', Object.fromEntries(formData));
        
        // Close modal
        const modal = bootstrap.Modal.getInstance(document.getElementById('addTaskModal'));
        modal.hide();
        
        // Show success message
        showNotification('Task saved successfully');
    } catch (error) {
        console.error('Error saving task:', error);
        showNotification('Error saving task', 'error');
    }
}

function showNotification(message, type = 'success') {
    // Implement your notification system here
    console.log(`${type}: ${message}`);
}

// Add a closeTaskModal function similar to closeStopModal
function closeTaskModal() {
    const modal = document.getElementById('addTaskModal');
    modal.style.display = 'none';
    document.body.classList.remove('modal-open');
    const backdrop = document.querySelector('.modal-backdrop');
    if (backdrop) backdrop.remove();
}
</script>

<script>
// Function to open task modal
function openTaskModal() {
    const taskModal = new bootstrap.Modal(document.getElementById('addTaskModal'));
    taskModal.show();
}

// Initialize modals and event listeners when document is ready
document.addEventListener('DOMContentLoaded', function() {
    // Initialize all Bootstrap modals
    const modals = document.querySelectorAll('.modal');
    modals.forEach(modal => {
        new bootstrap.Modal(modal);
    });

    // Initialize task modal button click
    const addTaskBtn = document.querySelector('[data-bs-target="#addTaskModal"]');
    if (addTaskBtn) {
        addTaskBtn.addEventListener('click', openTaskModal);
    }

    // File upload handling
    const uploadBtn = document.querySelector('.upload-btn');
    const fileInput = document.getElementById('taskFileUpload');

    if (uploadBtn && fileInput) {
        uploadBtn.addEventListener('click', () => {
            fileInput.click();
        });

        fileInput.addEventListener('change', handleFileUpload);
    }
});
</script>