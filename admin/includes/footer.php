<?php
/**
 * Admin Panel Footer
 * Embrace Modesty E-Commerce
 */
?>

    </div> <!-- End Content Wrapper -->
</main>

<!-- Bootstrap JS -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<!-- jQuery -->
<script src="https://code.jquery.com/jquery-3.7.0.min.js"></script>
<!-- DataTables -->
<link rel="stylesheet" href="https://cdn.datatables.net/1.13.7/css/dataTables.bootstrap5.min.css">
<script src="https://cdn.datatables.net/1.13.7/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.13.7/js/dataTables.bootstrap5.min.js"></script>
<!-- Chart.js -->
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.js"></script>

<script>
    // DataTables initialization - disabled due to persistent column mismatch issues
    // The error "_DT_CellIndex" occurs when table structure doesn't match DataTables expectations
    $(document).ready(function() {
        console.log('DataTables initialization disabled for this page');
        /*
        // Keeping this code here for reference if we want to re-enable later
        // after fixing the underlying column alignment issues
        var $dataTable = $('.data-table');
        if ($dataTable.length > 0) {
            $dataTable.DataTable({
                responsive: true,
                pageLength: 10,
                lengthMenu: [10, 25, 50, 100]
            });
        }
        */
    });

    // Confirm delete
    function confirmDelete(message) {
        return confirm(message || 'Are you sure you want to delete this item?');
    }

    // Auto-hide alerts
    setTimeout(function() {
        $('.alert').fadeOut('slow');
    }, 5000);

    // Image preview
    function previewImage(input, previewId) {
        if (input.files && input.files[0]) {
            var reader = new FileReader();
            reader.onload = function(e) {
                document.getElementById(previewId).src = e.target.result;
            }
            reader.readAsDataURL(input.files[0]);
        }
    }
</script>

</body>
</html>

