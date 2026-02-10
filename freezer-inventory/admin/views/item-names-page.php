<?php
defined( 'ABSPATH' ) || exit;
?>
<div class="freezer-inventory-wrap">
    <div class="freezer-inventory-container">
        <header class="freezer-inventory-header">
            <h1>Item Names</h1>
            <p class="subtitle">Manage the list of item names available in the add-item dropdown</p>
        </header>

        <main class="freezer-inventory-main">
            <section class="form-section">
                <h2>Add New Item Name</h2>
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
                <h2>All Item Names</h2>
                <div class="inventory-table-wrap">
                    <table id="itemNamesTable" class="inventory-table">
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
        </main>
    </div>
</div>
