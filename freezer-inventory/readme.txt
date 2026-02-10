=== Freezer Inventory Manager ===

Contributors: freezer-inventory
Requires at least: 5.0
Tested up to: 6.4
Requires PHP: 7.4
Stable tag: 1.0.3
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

Manage your freezer inventory with an editable spreadsheet table, categories, locations, and PDF export. Uses the WordPress database for storage.

== Description ==

Freezer Inventory Manager lets you:

* Add items with name, category, quantity, unit, and location
* Edit any field directly in the inventory table (click a cell to edit, like a spreadsheet)
* Navigate between cells with Tab, Enter, and Escape keys
* Items are automatically removed when quantity reaches zero
* Search and filter by name, category, and location
* Open a print-friendly view to save as PDF (Print to PDF in browser)
* Embed the full inventory on any frontend page with the `[freezer_inventory]` shortcode

Locations are fixed: Shelf 1 Bin 1–3, Shelf 2 Bin 1–2, Shelf 2 Bulk, Door Shelf 1–2.

== Installation ==

1. Upload the plugin zip via Plugins > Add New > Upload Plugin, or unzip into wp-content/plugins/
2. Activate "Freezer Inventory Manager" under Plugins
3. Use the "Freezer Inventory" menu in the admin sidebar to manage inventory
4. Optionally, add `[freezer_inventory]` to any page to access the inventory from the frontend

== Frequently Asked Questions ==

= Where is data stored? =

In a custom database table: `wp_freezer_inventory` (prefix may vary). Only administrators can access the data.

= How do I get a PDF? =

Click "Download PDF" on the Freezer Inventory page. A new tab opens with a printable table. Use your browser's Print > Save as PDF to save the file.

= Can I use this on the frontend? =

Yes. Add the shortcode `[freezer_inventory]` to any WordPress page or post. The logged-in user must have administrator privileges to view and edit the inventory.

= How do I edit an item? =

Click any cell in the inventory table to edit it in place. Use Tab to move between cells in a row, Enter to move down a column, and Escape to cancel.

= What happens when quantity reaches zero? =

The item is automatically deleted from the inventory.

== Changelog ==

= 1.0.2 =
* Added build-zip.sh script for auto-incrementing version and rebuilding zip

= 1.0.1 =
* Replaced card layout with editable spreadsheet-style table
* Inline editing: click any cell to edit, Tab/Enter/Escape navigation
* Auto-remove items when quantity reaches zero
* Added `[freezer_inventory]` shortcode for frontend access
* Removed manual Remove button

= 1.0.0 =
* Initial release. Add/remove items, partial quantity, filters, PDF print view, location dropdown.
