<?php
defined( 'ABSPATH' ) || exit;
?>
<div class="wrap freezer-inventory-wrap">
    <div class="freezer-inventory-container">
        <header class="freezer-inventory-header">
            <h1>🧊 Freezer Inventory Manager</h1>
            <p class="subtitle">Keep track of what's in your freezer</p>
        </header>

        <main class="freezer-inventory-main">
            <section class="form-section">
                <h2>Add New Item</h2>
                <form id="addItemForm">
                    <div class="form-row">
                        <div class="form-group">
                            <label for="itemName">Item Name *</label>
                            <input type="text" id="itemName" name="name" required placeholder="e.g., Chicken Breast">
                        </div>
                        <div class="form-group">
                            <label for="itemCategory">Category *</label>
                            <select id="itemCategory" name="category" required>
                                <option value="">Select category</option>
                                <option value="Meat">Meat</option>
                                <option value="Vegetables">Vegetables</option>
                                <option value="Fruits">Fruits</option>
                                <option value="Prepared Meals">Prepared Meals</option>
                                <option value="Dairy">Dairy</option>
                                <option value="Bread">Bread</option>
                                <option value="Other">Other</option>
                            </select>
                        </div>
                    </div>

                    <div class="form-row">
                        <div class="form-group">
                            <label for="itemQuantity">Quantity *</label>
                            <input type="number" id="itemQuantity" name="quantity" step="0.1" min="0" required placeholder="1.5">
                        </div>
                        <div class="form-group">
                            <label for="itemUnit">Unit *</label>
                            <select id="itemUnit" name="unit" required>
                                <option value="lbs">lbs</option>
                                <option value="oz">oz</option>
                                <option value="pieces">pieces</option>
                                <option value="bags">bags</option>
                                <option value="containers">containers</option>
                                <option value="packages">packages</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label for="itemLocation">Location *</label>
                            <select id="itemLocation" name="freezer_zone" required>
                                <option value="">Select location</option>
                                <?php foreach ( $locations as $loc ) : ?>
                                    <option value="<?php echo esc_attr( $loc ); ?>"><?php echo esc_html( $loc ); ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="itemNotes">Notes (optional)</label>
                        <textarea id="itemNotes" name="notes" rows="2" placeholder="Any additional notes..."></textarea>
                    </div>

                    <button type="submit" class="btn btn-primary">Add to Freezer</button>
                </form>
            </section>

            <section class="filters-section">
                <h2>Inventory</h2>
                <div class="filters">
                    <div class="filter-group">
                        <label for="searchInput">Search:</label>
                        <input type="text" id="searchInput" placeholder="Search by name...">
                    </div>
                    <div class="filter-group">
                        <label for="categoryFilter">Category:</label>
                        <select id="categoryFilter">
                            <option value="">All Categories</option>
                        </select>
                    </div>
                    <div class="filter-group">
                        <label for="zoneFilter">Location:</label>
                        <select id="zoneFilter">
                            <option value="">All Locations</option>
                        </select>
                    </div>
                    <button id="clearFilters" class="btn btn-secondary">Clear Filters</button>
                </div>
            </section>

            <section class="inventory-section">
                <div class="inventory-header">
                    <div id="inventoryStats" class="stats"></div>
                    <button type="button" id="downloadPdfBtn" class="btn btn-pdf">Download PDF</button>
                </div>
                <div id="inventoryList" class="inventory-list">
                    <p class="empty-message">No items in freezer. Add your first item above!</p>
                </div>
            </section>
        </main>
    </div>
</div>
