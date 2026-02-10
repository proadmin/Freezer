<?php
defined( 'ABSPATH' ) || exit;
$example_csv_url = FREEZER_INVENTORY_PLUGIN_URL . 'admin/example-import.csv';
?>
<div class="freezer-inventory-wrap">
    <div class="freezer-inventory-container">
        <header class="freezer-inventory-header">
            <h1>CSV Import / Export</h1>
            <p class="subtitle">Import and export your freezer inventory as CSV files</p>
        </header>

        <main class="freezer-inventory-main">
            <section class="form-section">
                <h2>Export CSV</h2>
                <p>Download your current inventory as a CSV file.</p>
                <button type="button" id="downloadCsvBtn" class="btn btn-csv">Export CSV</button>
            </section>

            <section class="form-section">
                <h2>Import CSV</h2>
                <p><strong>Warning:</strong> Importing a CSV will <strong>replace all</strong> current inventory items. This cannot be undone.</p>
                <p>Your CSV file must include a header row with the following columns:</p>
                <div class="csv-columns-table-wrap">
                    <table class="inventory-table csv-columns-table">
                        <thead>
                            <tr>
                                <th>Column</th>
                                <th>Required</th>
                                <th>Description</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr><td><code>Name</code></td><td>Yes</td><td>Item name (e.g., Chicken Breast)</td></tr>
                            <tr><td><code>Category</code></td><td>Yes</td><td>Meat, Vegetables, Fruits, Prepared Meals, Dairy, Bread, or Other</td></tr>
                            <tr><td><code>Quantity</code></td><td>Yes</td><td>Numeric quantity (e.g., 4)</td></tr>
                            <tr><td><code>Unit</code></td><td>Yes</td><td>lbs, oz, pieces, bags, containers, or packages</td></tr>
                            <tr><td><code>Freezer</code></td><td>Yes</td><td>Freezer name (e.g., Main Freezer)</td></tr>
                            <tr><td><code>Shelf</code></td><td>Yes</td><td>Shelf name (e.g., Shelf 1)</td></tr>
                            <tr><td><code>Bin</code></td><td>No</td><td>Bin name (e.g., Bin 1). Leave blank if not applicable.</td></tr>
                            <tr><td><code>Date Added</code></td><td>No</td><td>Date in YYYY-MM-DD format. Defaults to today if omitted.</td></tr>
                            <tr><td><code>Notes</code></td><td>No</td><td>Any additional notes</td></tr>
                        </tbody>
                    </table>
                </div>
                <p style="margin-top: 15px;">
                    <a href="<?php echo esc_url( $example_csv_url ); ?>" download class="btn btn-secondary">Download Example CSV</a>
                </p>
                <div class="csv-import-area">
                    <label class="btn btn-import" for="csvFileInput">Choose CSV File to Import</label>
                    <input type="file" id="csvFileInput" accept=".csv" style="display:none">
                    <span id="csvFileName" class="csv-file-name"></span>
                </div>
            </section>
        </main>
    </div>
</div>
