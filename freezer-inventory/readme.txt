=== Freezer Inventory Manager ===

Contributors: freezer-inventory
Requires at least: 5.0
Tested up to: 6.4
Requires PHP: 7.4
Stable tag: 2.0.6
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

Manage your freezer inventory with an editable spreadsheet table, categories, managed locations, and PDF/CSV export. Uses the WordPress database for storage.

== Description ==

Freezer Inventory Manager lets you:

* Add items with name, category, quantity, unit, location (freezer/shelf/bin), and notes
* Edit any field directly in the inventory table (click a cell to edit, like a spreadsheet)
* Navigate between cells with Tab, Enter, and Escape keys
* Items are automatically removed when quantity reaches zero
* Search and filter by name, category, freezer, shelf, and bin
* Manage freezers, locations, and item names from dedicated admin pages
* Item name autocomplete dropdown that learns new names as you add items
* Cascading location dropdowns (freezer → shelf → bin)
* Export inventory as CSV or open a print-friendly PDF view
* Import inventory from CSV files
* Embed the full inventory on any frontend page with the `[freezer_inventory]` shortcode

== Installation ==

1. Upload the plugin zip via Plugins > Add New > Upload Plugin, or unzip into wp-content/plugins/
2. Activate "Freezer Inventory Manager" under Plugins
3. Use the "Freezer Inventory" menu in the admin sidebar to manage inventory
4. Set up freezers under Freezer Inventory > Freezers
5. Set up locations (freezer/shelf/bin combinations) under Freezer Inventory > Locations
6. Optionally, manage the item name autocomplete list under Freezer Inventory > Item Names
7. Optionally, add `[freezer_inventory]` to any page to access the inventory from the frontend

== Frequently Asked Questions ==

= Where is data stored? =

In custom database tables: `wp_freezer_inventory`, `wp_freezer_locations`, `wp_freezer_freezers`, and `wp_freezer_item_names` (prefix may vary). Only administrators can access the data.

= How do I get a PDF? =

Click "Download PDF" on the Freezer Inventory page. A new tab opens with a printable table. Use your browser's Print > Save as PDF to save the file.

= How do I export/import CSV? =

Click "Export CSV" to download the inventory as a CSV file. Click "Import CSV" to replace all inventory items from a CSV file (freezer/shelf/bin columns are supported and locations are auto-created).

= Can I use this on the frontend? =

Yes. Add the shortcode `[freezer_inventory]` to any WordPress page or post. The logged-in user must have administrator privileges to view and edit the inventory.

= How do I edit an item? =

Click any cell in the inventory table to edit it in place. Use Tab to move between cells in a row, Enter to move down a column, and Escape to cancel. Location cells open cascading freezer/shelf/bin dropdowns.

= What happens when quantity reaches zero? =

The item is automatically deleted from the inventory.

= Can I delete a freezer, location, or item name? =

You can delete them from their respective admin pages, but only if no inventory items or locations currently reference them.

== Changelog ==

= 2.0.3 =
* Normalized locations into a dedicated table with freezer, shelf, and bin fields
* Cascading location dropdowns (freezer → shelf → bin) on add form, filters, and inline editing
* Added Freezers admin page to manage freezer names
* Added Locations admin page to manage freezer/shelf/bin combinations
* Added Item Names admin page to manage the item name autocomplete list
* Item name autocomplete dropdown on the add form; new names auto-added
* Delete protection: freezers, locations, and item names cannot be deleted while in use
* Added CSV export
* Removed View Frontend button
* Fixed dbDelta migration compatibility

= 1.0.3 =
* Added CSV import to replace all inventory items
* Confirmation prompt warns before overwriting existing data

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
