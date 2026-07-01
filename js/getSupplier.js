document.addEventListener('DOMContentLoaded', function () {
    const supplierSelect = document.getElementById('supplier_select');
    const productSelect = document.getElementById('product_select');
    const wholesaleInput = document.getElementById('wholesale_cost');
    const boxquantity = document.getElementById('unit');
    const expectedInput = document.getElementById('expected_date')

    let currentSupplierData = [];

    // Step 1: When Supplier changes, fetch their specific products
    supplierSelect.addEventListener('change', async function () {
        const supplierId = this.value;
        productSelect.innerHTML = '<option value="">-- Loading Products... --</option>';

        if (!supplierId) {
            productSelect.disabled = true;
            return;
        }

        try {
            const response = await fetch(`get_supplier_products.php?supplier_id=${supplierId}`);
            currentSupplierData = await response.json();

            productSelect.innerHTML = '<option value="">-- Choose Product --</option>';
            currentSupplierData.forEach(item => {
                const opt = document.createElement('option');
                opt.value = item.product_id;
                opt.textContent = `${item.product_name}`;
                productSelect.appendChild(opt);
            });

            productSelect.disabled = false;
        } catch (error) {
            console.error('Error fetching products:', error);
        }
    });

    // Step 2: When Product changes, auto-fill the wholesale cost
    productSelect.addEventListener('change', function () {
        const selectedId = this.value;
        const productInfo = currentSupplierData.find(p => p.product_id == selectedId);

        if (productInfo) {
            wholesaleInput.value = productInfo.wholesale_cost;
            boxquantity.value = productInfo.qty_per_unit;
            const leadTime = parseInt(productInfo.lead_time_days) || 0;
            const deliveryDate = new Date();
            deliveryDate.setDate(deliveryDate.getDate() + leadTime);
            console.log(deliveryDate.getDate());
            const yyyy = deliveryDate.getFullYear();
            const mm = String(deliveryDate.getMonth() + 1).padStart(2, '0');
            const dd = String(deliveryDate.getDate()).padStart(2, '0');
            expectedInput.value = `${yyyy}-${mm}-${dd}`;    
        }
    });
});
