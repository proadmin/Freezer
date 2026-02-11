<?php
defined( 'ABSPATH' ) || exit;
?>
<div class="freezer-inventory-wrap">
    <div class="freezer-inventory-container">
        <header class="freezer-inventory-header">
            <h1>Settings</h1>
            <p class="subtitle">Manage freezers, locations, and categories</p>
        </header>

        <main class="freezer-inventory-main">

            <!-- Freezers -->
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

            <!-- Locations -->
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
                            <input type="text" id="locShelf" name="shelf" required placeholder="e.g., Shelf 1">
                        </div>
                        <div class="form-group">
                            <label for="locBin">Bin</label>
                            <input type="text" id="locBin" name="bin" placeholder="e.g., Bin 1 (optional)">
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

            <!-- Categories -->
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

        </main>
    </div>
</div>
