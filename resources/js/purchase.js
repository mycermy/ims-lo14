// Assuming you have assigned IDs or classes to your input fields
const quantityInput = document.getElementById('purchase.[{index}].[quantity]');
const unitPriceInput = document.getElementById('purchase.[{index}].[unit_price]');
const subTotalInput = document.getElementById('purchase.[{index}].[sub_total]');

// Listen for changes in quantity and unit price
quantityInput.addEventListener('input', updateSubTotal);
unitPriceInput.addEventListener('input', updateSubTotal);

function updateSubTotal() {
    const quantity = parseFloat(quantityInput.value);
    const unitPrice = parseFloat(unitPriceInput.value);
    const subTotal = quantity * unitPrice;

    // Update the sub total field
    subTotalInput.value = subTotal.toFixed(2); // Adjust decimal places as needed
}
