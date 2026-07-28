<?php
// dashboards/custodian/modules/labels.php
// Inherits standard database connection $conn from parent custodian dashboard shell

// Query complete store catalog to map retail metadata for label sheets
$labels_query = "
    SELECT p.product_id, p.product_name, p.category, p.price, p.size, p.availability,
           i.current_stock
    FROM products p
    INNER JOIN inventory i ON p.product_id = i.product_id
    WHERE p.availability = 'Available'
    ORDER BY p.category ASC, p.product_name ASC
";
$labels_result = $conn->query($labels_query);
?>

<!-- Print-Specific Stylesheet Rules overrides screen views upon execution -->
<style>
    /* Screen View Utility rules */
    .no-print-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 20px;
        background: #f8f9fa;
        padding: 15px;
        border-radius: 6px;
        border: 1px solid #e2e8f0;
    }
    
    .label-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(220px, 1fr));
        gap: 15px;
    }
    
    .barcode-card {
        background: #fff;
        border: 1px dashed #cbd5e1;
        border-radius: 6px;
        padding: 12px;
        position: relative;
        box-sizing: border-box;
    }
    
    .barcode-mock {
        font-family: 'Courier New', Courier, monospace;
        background: #000;
        color: #fff;
        letter-spacing: 4px;
        text-align: center;
        padding: 4px;
        font-size: 0.8rem;
        margin-top: 10px;
        font-weight: bold;
    }

    /* Print Mode Layout Override Engine Rules */
    @media print {
        body * {
            visibility: hidden;
        }
        .label-grid, .label-grid * {
            visibility: visible;
        }
        .label-grid {
            position: absolute;
            left: 0;
            top: 0;
            width: 100%;
            grid-template-columns: repeat(3, 1fr) !important;
            gap: 10px !important;
            background: #fff !important;
        }
        .barcode-card {
            border: 1px solid #000 !important;
            page-break-inside: avoid;
        }
        aside, header, .no-print-header, button {
            display: none !important;
        }
    }
</style>

<div class="labels-container" style="background: white; padding: 25px; border-radius: 8px; border: 1px solid #e0e0e0;">
    
    <!-- Action Top Bar Controls -->
    <div class="no-print-header">
        <div>
            <h3 style="margin: 0; font-size: 1.2rem; font-weight: 600;">Product Retail Tag & Barcode Generator</h3>
            <p style="margin: 4px 0 0 0; font-size: 0.8rem; color: #64748b;">Select or arrange labels ready for standard retail display sheet execution.</p>
        </div>
        <button onclick="window.print();" style="background: #0f172a; color: white; border: none; padding: 10px 20px; font-size: 0.9rem; font-weight: bold; border-radius: 4px; cursor: pointer; display: flex; align-items: center; gap: 8px;">
            🖨️ Print Label Sheet
        </button>
    </div>

    <!-- Active Label Grid Generator Panel -->
    <div class="label-grid">
        <?php if ($labels_result->num_rows === 0): ?>
            <div style="grid-column: 1/-1; padding: 30px; text-align: center; color: #94a3b8; font-style: italic;">
                No matching product inventory records available to print.
            </div>
        <?php else: ?>
            <?php while ($prod = $labels_result->fetch_assoc()): ?>
                <div class="barcode-card">
                    <!-- Category Tracking Badge -->
                    <span style="font-size: 0.65rem; text-transform: uppercase; font-weight: bold; color: #64748b; display: block; margin-bottom: 2px;">
                        <?= htmlspecialchars($prod['category']) ?>
                    </span>
                    
                    <!-- Core Product Label Identification String -->
                    <strong style="font-size: 0.95rem; color: #0f172a; display: block; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;">
                        <?= htmlspecialchars($prod['product_name']) ?>
                    </strong>
                    
                    <!-- Layout Properties Line Info -->
                    <div style="display: flex; justify-content: space-between; font-size: 0.75rem; color: #475569; margin-top: 4px;">
                        <span>Size: <?= htmlspecialchars($prod['size'] ?: 'Standard') ?></span>
                        <span>Stock: <strong><?= $prod['current_stock'] ?> units</strong></span>
                    </div>
                    
                    <!-- Retail Shelf Currency Evaluation Block -->
                    <div style="margin-top: 8px; font-size: 1.15rem; font-weight: 800; color: #16a34a;">
                        ₱<?= number_format($prod['price'], 2) ?>
                    </div>
                    
                    <!-- Pseudo Barcode Scannable Grid Block Segment representation -->
                    <div class="barcode-mock">
                        ||||| <?= str_pad($prod['product_id'], 5, '0', STR_PAD_LEFT) ?> |||||
                    </div>
                </div>
            <?php endwhile; ?>
        <?php endif; ?>
    </div>
</div>
