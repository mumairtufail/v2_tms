<!-- Nav Buttons Component - START -->

<!-- 1. Add this CSS to your main stylesheet or in a <style> tag on the page -->
<style>
    /* Main container for the navigation buttons */
    .task-type-nav {
        display: flex;
        align-items: center;
        background-color: #f8f9fa; /* A light, neutral gray background */
        border: 1px solid #dee2e6;
        border-radius: 0.5rem; /* Rounded corners for the container */
        padding: 0.25rem;
        width: fit-content; /* Container shrinks to fit the buttons */
        margin-bottom: 1.5rem; /* Space below the nav */
    }

    /* Styling for each individual button */
    .task-type-nav .nav-btn {
        display: flex;
        align-items: center;
        gap: 0.5rem; /* Space between icon and text */
        padding: 0.6rem 1rem;
        font-size: 0.9rem;
        font-weight: 500;
        color: #6c757d; /* Default text color (gray) */
        background-color: transparent;
        border: none;
        border-radius: 0.375rem; /* Rounded corners for the button itself */
        cursor: pointer;
        transition: all 0.2s ease-in-out;
        white-space: nowrap;
    }
    
    .task-type-nav .nav-btn:not(.active):hover {
        background-color: #e9ecef; /* Slight highlight on hover for inactive buttons */
    }

    /* Styling for the ACTIVE button */
    .task-type-nav .nav-btn.active {
        color: #3A55B1; /* Blue text color for active state */
        background-color: #ffffff; /* White background to make it pop */
        box-shadow: 0 1px 3px rgba(0, 0, 0, 0.05), 0 1px 2px rgba(0, 0, 0, 0.03);
    }
    
    /* SVG Icon Styling */
    .task-type-nav .nav-btn svg {
        width: 20px;
        height: 20px;
    }

    /* SVG parts for INACTIVE buttons */
    .task-type-nav .nav-btn:not(.active) .outline-node {
        stroke: #adb5bd;
        fill: none;
    }
    .task-type-nav .nav-btn:not(.active) .solid-node {
        stroke: #adb5bd;
        fill: #adb5bd;
    }
    .task-type-nav .nav-btn:not(.active) .connector {
        stroke: #adb5bd;
    }
    
    /* SVG parts for the ACTIVE button */
    .task-type-nav .nav-btn.active .outline-node {
        stroke: currentColor; /* Inherits the blue color */
        fill: #ffffff; /* Make the inside of the node white */
    }
    .task-type-nav .nav-btn.active .solid-node {
        fill: currentColor; /* Inherits the blue color */
        stroke: none;
    }
    .task-type-nav .nav-btn.active .connector {
        stroke: currentColor; /* Inherits the blue color */
    }
</style>

<!-- 2. This is the HTML for the navigation buttons. Place it in your form. -->
<div class="task-type-nav" id="taskTypeNav">
    <!-- First Group -->
    <button type="button" class="nav-btn {{ (!isset($order) || $order->order_type == 'point_to_point') ? 'active' : '' }}" data-type="point-to-point">
        <svg viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
            <circle class="outline-node" cx="6" cy="12" r="3" stroke-width="1.5"/>
            <circle class="outline-node" cx="18" cy="12" r="3" stroke-width="1.5"/>
            <line class="connector" x1="9" y1="12" x2="15" y2="12" stroke-width="1.5"/>
        </svg>
        <span>Point-to-point</span>
    </button>
    {{-- <button type="button" class="nav-btn {{ (isset($order) && $order->order_type == 'single_shipper') ? 'active' : '' }}" data-type="single-shipper">
        <svg viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
            <circle class="outline-node" cx="4" cy="12" r="3" stroke-width="1.5"/>
            <circle class="solid-node" cx="20" cy="5" r="3"/>
            <circle class="solid-node" cx="20" cy="12" r="3"/>
            <circle class="solid-node" cx="20" cy="19" r="3"/>
            <path class="connector" d="M7 12L17 5M7 12L17 12M7 12L17 19" stroke-width="1.5"/>
        </svg>
        <span>Single shipper</span>
    </button>
     --}}
    <!-- Second Group -->
    <div class="nav-item-divider mx-1"></div> <!-- Optional visual divider -->
{{-- 
    <button type="button" class="nav-btn {{ (isset($order) && $order->order_type == 'single_consignee') ? 'active' : '' }}" data-type="single-consignee">
        <svg viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
            <rect class="outline-node" x="2.25" y="3.25" width="5.5" height="5.5" rx="1" stroke-width="1.5"/>
            <rect class="outline-node" x="2.25" y="10.25" width="5.5" height="5.5" rx="1" stroke-width="1.5"/>
            <rect class="outline-node" x="2.25" y="17.25" width="5.5" height="5.5" rx="1" stroke-width="1.5"/>
            <rect class="outline-node" x="16.25" y="10.25" width="5.5" height="5.5" rx="1" stroke-width="1.5"/>
            <path class="connector" d="M7.75 6L16.25 11M7.75 13L16.25 13M7.75 20L16.25 15" stroke-width="1.5"/>
        </svg>
        <span>Single consignee</span>
    </button>
   --}}
    <button type="button" class="nav-btn {{ (isset($order) && $order->order_type == 'sequence') ? 'active' : '' }}" data-type="sequence">
        <svg viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
            <circle class="outline-node" cx="4" cy="12" r="3" stroke-width="1.5"/>
            <circle class="solid-node" cx="20" cy="5" r="3"/>
            <circle class="solid-node" cx="20" cy="12" r="3"/>
            <circle class="solid-node" cx="20" cy="19" r="3"/>
            <path class="connector" d="M7 12L17 5M7 12L17 12M7 12L17 19" stroke-width="1.5"/>
        </svg>
        <span>Sequence</span>
    </button>
    
    {{-- <button type="button" class="nav-btn {{ (isset($order) && $order->order_type == 'multi_stop') ? 'active' : '' }}" data-type="multi-stop">
        <svg viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
            <rect class="outline-node" x="1.75" y="4.25" width="4.5" height="4.5" rx="1" stroke-width="1.5"/>
            <rect class="outline-node" x="1.75" y="15.25" width="4.5" height="4.5" rx="1" stroke-width="1.5"/>
            <rect class="outline-node" x="9.75" y="9.75" width="4.5" height="4.5" rx="1" stroke-width="1.5"/>
            <rect class="outline-node" x="17.75" y="4.25" width="4.5" height="4.5" rx="1" stroke-width="1.5"/>
            <rect class="outline-node" x="17.75" y="15.25" width="4.5" height="4.5" rx="1" stroke-width="1.5"/>
            <path class="connector" d="M6.25 6.5L9.75 10.5M6.25 17.5L9.75 13.5M14.25 12L17.75 16.5M14.25 12L17.75 7.5" stroke-width="1.5"/>
        </svg>
        <span>Multi-stop</span>
    </button> --}}
</div>

<!-- 3. Add this JavaScript to your main script file or in a <script> tag at the bottom of your page -->
<script>
    document.addEventListener('DOMContentLoaded', function () {
        const navContainer = document.getElementById('taskTypeNav');
        const navButtons = navContainer.querySelectorAll('.nav-btn');

        // Initialize the correct tab content on page load
        function initializeActiveTab() {
            const activeButton = navContainer.querySelector('.nav-btn.active');
            if (activeButton) {
                const selectedType = activeButton.dataset.type;
                showTabPane(selectedType);
            }
        }

        // Function to show the correct tab pane
        function showTabPane(selectedType) {
            // Hide all tab panes
            document.querySelectorAll('.tab-pane').forEach(pane => {
                pane.classList.remove('show', 'active');
            });
            
            // Show the selected tab pane
            const paneId = selectedType + '-content';
            const selectedPane = document.getElementById(paneId);
            if (selectedPane) {
                selectedPane.classList.add('show', 'active');
            }
        }

        // Initialize on page load
        initializeActiveTab();

        navButtons.forEach(button => {
            button.addEventListener('click', function () {
                // First, remove 'active' class from any currently active button
                const currentActive = navContainer.querySelector('.nav-btn.active');
                if (currentActive) {
                    currentActive.classList.remove('active');
                }
                // Add 'active' class to clicked button
                this.classList.add('active');

                // Show/hide tab panes based on selected type
                const selectedType = this.dataset.type;
                console.log('Selected task type:', selectedType);
                showTabPane(selectedType);
                
                // Update hidden input field for order_type
                const orderTypeInput = document.querySelector('input[name="order_type"]');
                if (orderTypeInput) {
                    orderTypeInput.value = selectedType;
                }
            });
        });
    });
</script>

<!-- Nav Buttons Component - END -->