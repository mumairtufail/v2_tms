<!-- /HK Wrapper -->
<!-- Core JavaScript - Order is important -->
<!-- jQuery -->
<script src="{{ asset('vendors/jquery/dist/jquery.min.js') }}"></script>

<!-- Popper.js for Bootstrap 5 -->
<script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.8/dist/umd/popper.min.js"></script>

<!-- Bootstrap 5 JavaScript -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.min.js"></script>

<!-- Slimscroll JavaScript -->
<script src="{{ asset('dist/js/jquery.slimscroll.js') }}"></script>

<!-- Fancy Dropdown JS -->
<script src="{{ asset('dist/js/dropdown-bootstrap-extended.js') }}"></script>

<!-- FeatherIcons JavaScript -->
<script src="{{ asset('dist/js/feather.min.js') }}"></script>

<!-- Toggles JavaScript -->
<script src="{{ asset('vendors/jquery-toggles/toggles.min.js') }}"></script>
<script src="{{ asset('dist/js/toggle-data.js') }}"></script>

<!-- Toastr JS -->
<script src="{{ asset('vendors/jquery-toast-plugin/dist/jquery.toast.min.js') }}"></script>

<!-- Counter Animation JavaScript -->
<script src="{{ asset('vendors/waypoints/lib/jquery.waypoints.min.js') }}"></script>
<script src="{{ asset('vendors/jquery.counterup/jquery.counterup.min.js') }}"></script>

<!-- Morris Charts JavaScript -->
<script src="{{ asset('vendors/raphael/raphael.min.js') }}"></script>
<script src="{{ asset('vendors/morris.js/morris.min.js') }}"></script>

<!-- Easy pie chart JS -->
<script src="{{ asset('vendors/easy-pie-chart/dist/jquery.easypiechart.min.js') }}"></script>

<!-- Flot Charts JavaScript -->
<script src="{{ asset('vendors/flot/excanvas.min.js') }}"></script>
<script src="{{ asset('vendors/flot/jquery.flot.js') }}"></script>
<script src="{{ asset('vendors/flot/jquery.flot.pie.js') }}"></script>
<script src="{{ asset('vendors/flot/jquery.flot.resize.js') }}"></script>
<script src="{{ asset('vendors/flot/jquery.flot.time.js') }}"></script>
<script src="{{ asset('vendors/flot/jquery.flot.stack.js') }}"></script>
<script src="{{ asset('vendors/flot/jquery.flot.crosshair.js') }}"></script>
<script src="{{ asset('vendors/jquery.flot.tooltip/js/jquery.flot.tooltip.min.js') }}"></script>

<!-- EChartJS JavaScript -->
<script src="{{ asset('vendors/echarts/dist/echarts-en.min.js') }}"></script>

<!-- Init JavaScript -->
<script src="{{ asset('dist/js/init.js') }}"></script>
<script src="{{ asset('dist/js/dashboard2-data.js') }}"></script>
<!-- SweetAlert2 CDN -->
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<!-- CSRF Token Setup -->
<script>
    $.ajaxSetup({
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        }
    });
</script>
<script>
    document.querySelectorAll('.delete-button').forEach(button => {
        button.addEventListener('click', function() {
            Swal.fire({
                title: 'Are you sure?',
                text: "You won't be able to revert this!",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#d33', // Red color for danger
                cancelButtonColor: '#3085d6',
                confirmButtonText: 'Yes, delete it!'
            }).then((result) => {
                if (result.isConfirmed) {
                    // Submit the form
                    this.closest('form').submit();
                }
            });
        });
    });


    
</script>

