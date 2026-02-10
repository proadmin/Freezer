<?php
defined( 'ABSPATH' ) || exit;
?>
<div class="freezer-inventory-wrap">
    <div class="freezer-inventory-container">
        <header class="freezer-inventory-header">
            <h1>Freezer Locations</h1>
            <p class="subtitle">Manage freezer, shelf, and bin locations</p>
        </header>

        <main class="freezer-inventory-main">
            <section class="form-section">
                <h2>Add New Location</h2>
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
                            <label for="locBin">Bin *</label>
                            <input type="text" id="locBin" name="bin" required placeholder="e.g., Bin 1">
                        </div>
                    </div>
                    <button type="submit" class="btn btn-primary">Add Location</button>
                </form>
            </section>

            <section class="inventory-section">
                <h2>All Locations</h2>
                <div class="inventory-table-wrap">
                    <table id="locationsTable" class="inventory-table">
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
        </main>
    </div>
</div>
