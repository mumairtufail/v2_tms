<!-- Add Stop Modal -->
<div class="modal fade add-stop-modal" id="addStopModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <form id="stopForm" class="stop-form" action="{{ route('stops.createStop') }}" method="POST">
        @csrf

            <div class="modal-content">
                <!-- Modal Header -->
                <div class="modal-header">
                    <h4 class="modal-title fw-bold">Add Stop</h4>
                    <button type="button" class="close" onclick="closeStopModal()">&times;</button>
                </div>

                <!-- Modal Body -->
                <div class="modal-body">
                    <input type="hidden" name="manifest_id" value="{{ $manifest->id }}">
                    <!-- Location Search Section -->
                    <div class="form-section">
                        <label class="form-label">Location/Terminal</label>
                        <input type="text" class="form-control" name="location"
                            placeholder="Search for a location or terminal">
                    </div>

                    <!-- Company Details Section -->
                    <div class="form-section">
                        <label class="form-label">Company</label>
                        <input type="text" class="form-control" name="company" placeholder="Enter company name">
                    </div>

                    <!-- Address Section -->
                    <div class="form-section">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label">Address 1</label>
                                <input type="text" class="form-control" name="address1" placeholder="Street address">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Address 2</label>
                                <input type="text" class="form-control" name="address2" placeholder="Apt, Suite, Unit">
                            </div>
                        </div>
                    </div>

                    <!-- Location Details Section -->
                    <div class="form-section">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label">City</label>
                                <input type="text" class="form-control" name="city" placeholder="Enter city">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">State/Province</label>
                                <input type="text" class="form-control" name="state" placeholder="Enter state">
                            </div>
                        </div>
                    </div>

                    <!-- Additional Details Section -->
                    <div class="form-section mb-0">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label">Country</label>
                                <input type="text" class="form-control" name="country" placeholder="Enter country">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Postal/ZIP Code</label>
                                <input type="text" class="form-control" name="postal" placeholder="Enter postal code">
                            </div>
                        </div>
                    </div>
    </div>

    <!-- Modal Footer -->
    <div class="modal-footer">
        <button type="button" class="btn btn-outline-secondary" onclick="closeStopModal()">Cancel</button>
        <button type="submit" class="btn btn-primary">Save</button>
    </div>
</div>
</form>

</div>
</div>

<style>
    /* Modal Specific Styles */
    .add-stop-modal .modal-content {
        border: none;
        border-radius: 0.75rem;
        box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
    }

    .add-stop-modal .modal-header {
        padding: 1.5rem;
        border-bottom: 1px solid #f0f0f0;
        background-color: #fff;
        border-top-left-radius: 0.75rem;
        border-top-right-radius: 0.75rem;
    }

    .add-stop-modal .modal-body {
        padding: 1.5rem;
        background-color: #fff;
    }

    .add-stop-modal .modal-footer {
        padding: 1.25rem 1.5rem;
        border-top: 1px solid #f0f0f0;
        background-color: #fff;
        border-bottom-left-radius: 0.75rem;
        border-bottom-right-radius: 0.75rem;
    }

    /* Form Sections */
    .add-stop-modal .form-section {
        margin-bottom: 1.5rem;
    }

    .add-stop-modal .form-label {
        display: block;
        margin-bottom: 0.5rem;
        font-size: 0.875rem;
        color: #6c757d;
        font-weight: 500;
    }

    /* Search Input Styling */
    .add-stop-modal .search-input-group {
        position: relative;
    }

    .add-stop-modal .search-icon {
        position: absolute;
        left: 1rem;
        top: 50%;
        transform: translateY(-50%);
        color: #adb5bd;
        z-index: 4;
    }

    .add-stop-modal .search-control {
        padding-left: 2.5rem;
    }

    /* Form Controls */
    .add-stop-modal .form-control,
    .add-stop-modal .form-select {
        padding: 0.75rem 1rem;
        border-radius: 0.5rem;
        border: 1px solid #dee2e6;
        font-size: 0.9375rem;
        transition: all 0.2s ease-in-out;
    }

    .add-stop-modal .form-control:focus,
    .add-stop-modal .form-select:focus {
        border-color: #86b7fe;
        box-shadow: 0 0 0 0.25rem rgba(13, 110, 253, 0.15);
    }

    /* Button Styling */
    .add-stop-modal .btn {
        padding: 0.75rem 1.5rem;
        font-weight: 500;
        border-radius: 0.5rem;
        transition: all 0.2s ease-in-out;
    }

    .add-stop-modal .btn-primary {
        background-color: #0d6efd;
        border-color: #0d6efd;
    }

    .add-stop-modal .btn-outline-secondary {
        color: #6c757d;
        border-color: #dee2e6;
    }

    .add-stop-modal .btn-outline-secondary:hover {
        background-color: #f8f9fa;
        border-color: #dee2e6;
        color: #495057;
    }

    /* Responsive Adjustments */
    @media (max-width: 768px) {
        .add-stop-modal .modal-dialog {
            margin: 0.5rem;
        }

        .add-stop-modal .modal-header,
        .add-stop-modal .modal-body,
        .add-stop-modal .modal-footer {
            padding: 1rem;
        }
    }
</style>

<script>
    // Add this close modal function
    function closeModal() {
        const modal = document.getElementById('addStopModal');
        modal.classList.remove('show');
        setTimeout(() => {
            modal.style.display = 'none';
            document.body.style.overflow = '';
        }, 300);
    }

    function closeStopModal() {
        const modal = document.getElementById('addStopModal');
        modal.style.display = 'none';
        document.body.classList.remove('modal-open');
        document.querySelector('.modal-backdrop').remove();
    }

    // Modify the existing event listener
    document.addEventListener('DOMContentLoaded', function () {
        // Close modal when clicking outside
        window.onclick = function (event) {
            const modal = document.getElementById('addStopModal');
            if (event.target === modal) {
                closeStopModal();
            }
        }

        // Stop button handler with enhanced error handling
        const addStopBtn = document.querySelector('button[data-action="add-stop"]');
        if (addStopBtn) {
            addStopBtn.addEventListener('click', function () {
                try {
                    const modal = new bootstrap.Modal(document.getElementById('addStopModal'));
                    modal.show();
                } catch (error) {
                    console.error('Error opening modal:', error);
                }
            });
        }

        // Add close button functionality
        const stopModal = document.getElementById('addStopModal');
        const closeButtons = stopModal.querySelectorAll('[data-bs-dismiss="modal"]');

        closeButtons.forEach(button => {
            button.addEventListener('click', function () {
                const modal = bootstrap.Modal.getInstance(stopModal);
                if (modal) {
                    modal.hide();
                }
            });
        });

        // Handle close button click
        document.querySelector('#addStopModal .close').addEventListener('click', closeStopModal);
    });

    function saveStop() {
        // Get form data
        const form = document.getElementById('stopForm');
        const formData = new FormData(form);

        // Basic validation
        let isValid = true;
        const requiredFields = ['company', 'address1', 'city', 'country', 'postal'];

        requiredFields.forEach(field => {
            const input = form.querySelector(`[name="${field}"]`);
            if (input && !input.value.trim()) {
                isValid = false;
                input.classList.add('is-invalid');
            }
        });

        if (!isValid) {
            return;
        }

        // Process form data
        try {
            // Add your API call or data processing here
            const modal = bootstrap.Modal.getInstance(document.getElementById('addStopModal'));
            modal.hide();

            // Optional: Show success message
            showNotification('Stop added successfully');
        } catch (error) {
            console.error('Error saving stop:', error);
            showNotification('Error saving stop', 'error');
        }
    }

    function showNotification(message, type = 'success') {
        // Implement your notification system here
        console.log(`${type}: ${message}`);
    }
</script>