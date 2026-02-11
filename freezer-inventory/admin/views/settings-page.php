<?php
defined( 'ABSPATH' ) || exit;
?>
<div class="freezer-inventory-wrap">
    <div class="freezer-inventory-container">
        <header class="freezer-inventory-header">
            <h1>Settings</h1>
            <p class="subtitle">Manage categories, freezers, locations, and item names</p>
        </header>

        <main class="freezer-inventory-main">

            <div class="settings-tabs">
                <button type="button" class="settings-tab active" data-tab="categories">Categories</button>
                <button type="button" class="settings-tab" data-tab="freezers">Freezers</button>
                <button type="button" class="settings-tab" data-tab="locations">Locations</button>
                <button type="button" class="settings-tab" data-tab="item-names">Item Names</button>
            </div>

            <!-- Categories -->
            <div class="settings-tab-content active" data-tab="categories">
                <section class="form-section" id="categories-section">
                    <h2>Categories</h2>
                    <form id="addCategoryForm">
                        <div class="form-row">
                            <div class="form-group">
                                <label for="categoryName">Category Name *</label>
                                <input type="text" id="categoryName" name="name" required placeholder="e.g., Seafood">
                            </div>
                        </div>
                        <button type="submit" class="btn btn-primary">Add Category</button>
                    </form>
                </section>
                <section class="inventory-section">
                    <div class="inventory-table-wrap">
                        <table class="inventory-table">
                            <thead>
                                <tr>
                                    <th>Category Name</th>
                                    <th>Items</th>
                                    <th class="col-actions">Actions</th>
                                </tr>
                            </thead>
                            <tbody id="categoriesBody">
                                <tr><td colspan="3" class="empty-message">No categories defined.</td></tr>
                            </tbody>
                        </table>
                    </div>
                </section>
            </div>

            <!-- Freezers -->
            <div class="settings-tab-content" data-tab="freezers">
                <section class="form-section" id="freezers-section">
                    <h2>Freezers</h2>
                    <form id="addFreezerForm">
                        <div class="form-row">
                            <div class="form-group">
                                <label for="freezerName">Freezer Name *</label>
                                <input type="text" id="freezerName" name="name" required placeholder="e.g., Garage Freezer">
                            </div>
                        </div>
                        <button type="submit" class="btn btn-primary">Add Freezer</button>
                    </form>
                </section>
                <section class="inventory-section">
                    <div class="inventory-table-wrap">
                        <table class="inventory-table">
                            <thead>
                                <tr>
                                    <th>Freezer Name</th>
                                    <th>Locations</th>
                                    <th class="col-actions">Actions</th>
                                </tr>
                            </thead>
                            <tbody id="freezersBody">
                                <tr><td colspan="3" class="empty-message">No freezers defined.</td></tr>
                            </tbody>
                        </table>
                    </div>
                </section>
            </div>

            <!-- Locations -->
            <div class="settings-tab-content" data-tab="locations">
                <section class="form-section" id="locations-section">
                    <h2>Locations</h2>
                    <form id="addLocationForm">
                        <div class="form-row">
                            <div class="form-group">
                                <label for="locFreezer">Freezer *</label>
                                <select id="locFreezer" name="freezer" required>
                                    <option value="">Select freezer</option>
                                </select>
                            </div>
                            <div class="form-group">
                                <label for="locShelf">Shelf *</label>
                                <input type="text" id="locShelf" name="shelf" required placeholder="e.g., 1">
                            </div>
                            <div class="form-group">
                                <label for="locBin">Bin</label>
                                <input type="text" id="locBin" name="bin" placeholder="e.g., 1 (optional)">
                            </div>
                        </div>
                        <button type="submit" class="btn btn-primary">Add Location</button>
                    </form>
                </section>
                <section class="inventory-section">
                    <div class="inventory-table-wrap">
                        <table class="inventory-table">
                            <thead>
                                <tr>
                                    <th>Freezer</th>
                                    <th>Shelf</th>
                                    <th>Bin</th>
                                    <th>Items</th>
                                    <th class="col-actions">Actions</th>
                                </tr>
                            </thead>
                            <tbody id="locationsBody">
                                <tr><td colspan="5" class="empty-message">No locations defined.</td></tr>
                            </tbody>
                        </table>
                    </div>
                </section>
            </div>

            <!-- Item Names -->
            <div class="settings-tab-content" data-tab="item-names">
                <section class="form-section" id="item-names-section">
                    <h2>Item Names</h2>
                    <form id="addItemNameForm">
                        <div class="form-row">
                            <div class="form-group">
                                <label for="newItemName">Item Name *</label>
                                <input type="text" id="newItemName" name="name" required placeholder="e.g., Chicken Breast">
                            </div>
                        </div>
                        <button type="submit" class="btn btn-primary">Add Item Name</button>
                    </form>
                </section>
                <section class="inventory-section">
                    <div class="inventory-table-wrap">
                        <table class="inventory-table">
                            <thead>
                                <tr>
                                    <th>Item Name</th>
                                    <th>In Inventory</th>
                                    <th class="col-actions">Actions</th>
                                </tr>
                            </thead>
                            <tbody id="itemNamesBody">
                                <tr><td colspan="3" class="empty-message">No item names defined.</td></tr>
                            </tbody>
                        </table>
                    </div>
                </section>
            </div>

        </main>
    </div>
</div>
