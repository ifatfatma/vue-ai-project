
<!-- jQuery -->
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

<!-- Vendor / Plugins JS -->
<script src="{{ asset('admin/assets/vendors/js/vendor.bundle.base.js') }}"></script>
<script src="{{ asset('admin/assets/vendors/js/vendor.bundle.addons.js') }}"></script>

<!-- Bootstrap 5 -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

<!-- Admin Template JS -->
<script src="{{ asset('admin/assets/js/shared/off-canvas.js') }}"></script>
<script src="{{ asset('admin/assets/js/shared/misc.js') }}"></script>
<script src="{{ asset('admin/assets/js/demo_1/dashboard.js') }}"></script>

<!-- Select2 -->
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>

<!-- Select2 Initialization -->
<script>
    $(document).ready(function () {
        if ($.fn.select2) {
            $('.select2-user').select2({
                theme: 'bootstrap-5',
                placeholder: '-- Filter User by Name/Email --',
                allowClear: true,
                width: '100%'
            });
        }
    });
</script>


