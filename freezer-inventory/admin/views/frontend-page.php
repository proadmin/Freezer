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
                            </select>
                        </div>
                    </div>

                    <div class="form-row">
                        <div class="form-group">
                            <label for="itemQuantity">Quantity *</label>
                            <input type="number" id="itemQuantity" name="quantity" step="1" min="0" required placeholder="1">
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
                            <label for="itemPreparation">Raw / Cooked *</label>
                            <select id="itemPreparation" name="preparation" required>
                                <option value="">Select</option>
                                <option value="Raw">Raw</option>
                                <option value="Cooked">Cooked</option>
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
                    <div class="filter-group">
                        <label for="preparationFilter">Raw / Cooked:</label>
                        <select id="preparationFilter">
                            <option value="">All</option>
                            <option value="Raw">Raw</option>
                            <option value="Cooked">Cooked</option>
                        </select>
                    </div>
                    <button id="clearFilters" class="btn btn-secondary">Clear Filters</button>
                </div>
            </section>

            <section class="inventory-section">
                <div class="inventory-header">
                    <div id="inventoryStats" class="stats"></div>
                </div>
                <div class="inventory-table-wrap">
                    <table id="inventoryTable" class="inventory-table">
                        <thead>
                            <tr>
                                <th>Name</th>
                                <th>Category</th>
                                <th>Qty</th>
                                <th>Unit</th>
                                <th>Raw / Cooked</th>
                                <th>Location</th>
                                <th>Notes</th>
                                <th>Date Added</th>
                            </tr>
                        </thead>
                        <tbody id="inventoryBody">
                            <tr><td colspan="8" class="empty-message">No items in freezer. Add your first item above!</td></tr>
                        </tbody>
                    </table>
                </div>
                <div class="inventory-footer">
                    <button type="button" id="downloadPdfBtn" class="btn btn-pdf">Download PDF</button>
                </div>
            </section>
        </main>
    </div>
</div>
