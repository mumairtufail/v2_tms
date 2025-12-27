<!-- Add Driver Modal -->
<div class="modal fade" id="addDriverModal" tabindex="-1" aria-labelledby="addDriverModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header border-0 pb-0">
                <h4 class="modal-title fw-bold" id="addDriverModalLabel">Edit Manifest Drivers</h4>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body pt-2">
                <p class="text-muted mb-4">To search for a driver, please enter at least 3 characters for results.</p>
                
                <div class="row g-4">
                    <!-- Left Column - Driver Search -->
                    <div class="col-md-6">
                        <div class="card border">
                            <div class="card-body">
                                <div class="d-flex justify-content-between align-items-center mb-3">
                                    <h5 class="card-title mb-0">Drivers</h5>
                                    <a href="#" class="text-primary text-decoration-none">Create New Driver</a>
                                </div>
                                
                                <!-- Search Input -->
                                <div class="input-group mb-3">
                                    <input type="text" class="form-control" placeholder="Search for a driver" 
                                           aria-label="Search for a driver">
                                    <span class="input-group-text bg-white">
                                        <i class="fas fa-search text-muted"></i>
                                    </span>
                                </div>
                                
                                <!-- Driver List -->
                                <div class="driver-list">
                                    <div class="d-flex justify-content-between align-items-center p-3 border rounded mb-2">
                                        <span>Hardeep Chahal</span>
                                        <button class="btn btn-outline-primary btn-sm px-3">Select</button>
                                    </div>
                                    <div class="d-flex justify-content-between align-items-center p-3 border rounded mb-2">
                                        <span>Manpreet S</span>
                                        <button class="btn btn-outline-primary btn-sm px-3">Select</button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Right Column - Selected Drivers -->
                    <div class="col-md-6">
                        <div class="card border">
                            <div class="card-body">
                                <h5 class="card-title mb-3">Selected drivers (0)</h5>
                                <div class="selected-drivers bg-light rounded p-4 text-center" style="min-height: 200px;">
                                    <span class="text-muted">None selected</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer border-0 pt-4">
                <button type="button" class="btn btn-outline-secondary px-4" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary px-4">Save</button>
            </div>
        </div>
    </div>
</div>

<!-- Add this JavaScript to handle the modal opening -->
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Add click event listener to the ADD DRIVER button
    const addDriverBtn = document.querySelector('.btn-outline-primary[data-action="add-driver"]');
    if (addDriverBtn) {
        addDriverBtn.addEventListener('click', function() {
            const modal = new bootstrap.Modal(document.getElementById('addDriverModal'));
            modal.show();
        });
    }
});
</script>

<style>
.modal-dialog {
    max-width: 800px;
}

.modal-content {
    border: none;
    border-radius: 0.5rem;
}

.driver-list {
    max-height: 300px;
    overflow-y: auto;
}

.driver-list::-webkit-scrollbar {
    width: 6px;
}

.driver-list::-webkit-scrollbar-track {
    background: #f1f1f1;
}

.driver-list::-webkit-scrollbar-thumb {
    background: #ccc;
    border-radius: 3px;
}

.selected-drivers {
    display: flex;
    align-items: center;
    justify-content: center;
    min-height: 200px;
    background-color: #f8f9fa;
}

.btn-outline-primary {
    border-color: #dee2e6;
}

.btn-outline-primary:hover {
    background-color: #f8f9fa;
    border-color: #0d6efd;
}

.input-group-text {
    border-left: none;
}

.form-control:focus + .input-group-text {
    border-color: #86b7fe;
}

.driver-list .btn-outline-primary {
    transition: all 0.2s ease;
}

.driver-list .btn-outline-primary:hover {
    transform: translateY(-1px);
}
</style>