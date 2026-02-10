<?php
defined( 'ABSPATH' ) || exit;
?>
<div class="freezer-inventory-wrap">
    <div class="freezer-inventory-container">
        <header class="freezer-inventory-header">
            <h1>Freezers</h1>
            <p class="subtitle">Manage your freezer units</p>
        </header>

        <main class="freezer-inventory-main">
            <section class="form-section">
                <h2>Add New Freezer</h2>
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
                <h2>All Freezers</h2>
                <div class="inventory-table-wrap">
                    <table id="freezersTable" class="inventory-table">
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
        </main>
    </div>
</div>
