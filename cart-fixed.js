document.addEventListener('DOMContentLoaded', function() {
    const checkboxes = document.querySelectorAll('.cart-checkbox');
    const selectedSummary = document.getElementById('selectedSummary');
    const selectedCountEl = document.getElementById('selectedCount');
    const selectedSubtotalEl = document.getElementById('selectedSubtotal');
    const selectedTotalEl = document.getElementById('selectedTotal');
    const proceedBtn = document.getElementById('proceedBtn');
    const selectAllBtn = document.getElementById('selectAllBtn');
    const checkoutForm = document.getElementById('checkoutForm');

    let selectedTotal = 0;
    let selectedCount = 0;

    // Checkbox change handler
    checkboxes.forEach(checkbox => {
        checkbox.addEventListener('change', updateSelected);
    });

    function updateSelected() {
        selectedTotal = 0;
        selectedCount = 0;

        checkboxes.forEach(checkbox => {
            if (checkbox.checked) {
                selectedTotal += parseFloat(checkbox.dataset.total);
                selectedCount += parseInt(checkbox.dataset.qty);
            }
        });

        // Update displays
        selectedCountEl.textContent = selectedCount;
        selectedSubtotalEl.textContent = '₹' + selectedTotal.toFixed(2);
        selectedTotalEl.textContent = '₹' + selectedTotal.toFixed(2);

        // Show/hide summary and enable button
        if (selectedCount > 0) {
            selectedSummary.style.display = 'block';
            proceedBtn.disabled = false;
        } else {
            selectedSummary.style.display = 'none';
            proceedBtn.disabled = true;
        }
    }

    // Select All button
    if (selectAllBtn) {
        selectAllBtn.addEventListener('click', function() {
            checkboxes.forEach(cb => cb.checked = true);
            updateSelected();
        });
    }

    // Form submit validation
    if (checkoutForm) {
        checkoutForm.addEventListener('submit', function(e) {
            const checkedBoxes = document.querySelectorAll('.cart-checkbox:checked');
            if (checkedBoxes.length === 0) {
                e.preventDefault();
                alert('Please select at least one product to proceed to checkout.');
                return false;
            }
        });
    }
});
