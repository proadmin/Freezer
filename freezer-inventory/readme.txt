=== Freezer Inventory Manager ===

Contributors: freezer-inventory
Requires at least: 5.0
Tested up to: 6.4
Requires PHP: 7.4
Stable tag: 1.0.0
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

Manage your freezer inventory with categories, locations, partial quantities, and PDF export. Uses the WordPress database for storage.

== Description ==

Freezer Inventory Manager lets you:

* Add items with name, category, quantity, unit, and location
* Use partial quantities (reduce amount when you use some)
* Search and filter by category and location
* Remove items or let them be removed when quantity reaches zero
* Open a print-friendly view to save as PDF (Print to PDF in browser)

Locations are fixed: Shelf 1 Bin 1–3, Shelf 2 Bin 1–2, Shelf 2 Bulk, Door Shelf 1–2.

== Installation ==

1. Upload the plugin zip via Plugins → Add New → Upload Plugin, or unzip into wp-content/plugins/
2. Activate "Freezer Inventory Manager" under Plugins
3. Use the "Freezer Inventory" menu in the admin sidebar to manage inventory

== Frequently Asked Questions ==

= Where is data stored? =

In a custom database table: `wp_freezer_inventory` (prefix may vary). Only administrators can access the data.

= How do I get a PDF? =

Click "Download PDF" on the Freezer Inventory page. A new tab opens with a printable table. Use your browser’s Print → Save as PDF to save the file.

== Changelog ==

= 1.0.0 =
* Initial release. Add/remove items, partial quantity, filters, PDF print view, location dropdown.
