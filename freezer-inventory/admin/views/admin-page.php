<?php
defined( 'ABSPATH' ) || exit;
?>
<div class="freezer-inventory-wrap">
    <div class="freezer-inventory-container">
        <header class="freezer-inventory-header">
            <h1>Freezer Inventory Manager</h1>
            <p class="subtitle">Keep track of what's in your freezer</p>
        </header>

        <main class="freezer-inventory-main">
            <section class="form-section">
                <h2>Add New Item</h2>
                <form id="addItemForm">
                    <div class="form-row">
                        <div class="form-group">
                            <label for="itemName">Item Name *</label>
                            <input type="text" id="itemName" name="name" list="itemNameList" required placeholder="e.g., Chicken Breast">
                            <datalist id="itemNameList"></datalist>
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
                    </div>

                    <div class="form-row">
                        <div class="form-group">
                            <label for="itemFreezer">Freezer *</label>
                            <select id="itemFreezer" required>
                                <option value="">Select freezer</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label for="itemShelf">Shelf *</label>
                            <select id="itemShelf" required>
                                <option value="">Select shelf</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label for="itemBin">Bin</label>
                            <select id="itemBin">
                                <option value="">Select bin</option>
                            </select>
                        </div>
                    </div>

                    <div class="form-row">
                        <div class="form-group">
                            <label for="itemDate">Date Added *</label>
                            <input type="date" id="itemDate" name="date_added" required>
                        </div>
                        <div class="form-group">
                            <label for="itemNotes">Notes (optional)</label>
                            <textarea id="itemNotes" name="notes" rows="2" placeholder="Any additional notes..."></textarea>
                        </div>
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
                        <label for="freezerFilter">Freezer:</label>
                        <select id="freezerFilter">
                            <option value="">All Freezers</option>
                        </select>
                    </div>
                    <div class="filter-group">
                        <label for="shelfFilter">Shelf:</label>
                        <select id="shelfFilter">
                            <option value="">All Shelves</option>
                        </select>
                    </div>
                    <div class="filter-group">
                        <label for="binFilter">Bin:</label>
                        <select id="binFilter">
                            <option value="">All Bins</option>
                        </select>
                    </div>
                    <button id="clearFilters" class="btn btn-secondary">Clear Filters</button>
                </div>
            </section>

            <section class="inventory-section">
                <div class="inventory-header">
                    <div id="inventoryStats" class="stats"></div>
                    <button type="button" id="downloadPdfBtn" class="btn btn-pdf">Download PDF</button>
                    <button type="button" id="downloadCsvBtn" class="btn btn-csv">Export CSV</button>
                    <label class="btn btn-import" for="csvFileInput">Import CSV</label>
                    <input type="file" id="csvFileInput" accept=".csv" style="display:none">
                </div>
                <div class="inventory-table-wrap">
                    <table id="inventoryTable" class="inventory-table">
                        <thead>
                            <tr>
                                <th>Name</th>
                                <th>Category</th>
                                <th>Qty</th>
                                <th>Unit</th>
                                <th>Location</th>
                                <th>Notes</th>
                                <th>Date Added</th>
                            </tr>
                        </thead>
                        <tbody id="inventoryBody">
                            <tr><td colspan="7" class="empty-message">No items in freezer. Add your first item above!</td></tr>
                        </tbody>
                    </table>
                </div>
            </section>
        </main>
    </div>
</div>
