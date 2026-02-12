=== Freezer Inventory Manager ===

Contributors: freezer-inventory
Requires at least: 5.0
Tested up to: 6.4
Requires PHP: 7.4
Stable tag: 1.0.0
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

Manage your freezer inventory with an editable spreadsheet table, categories, managed locations, and PDF/CSV export. Uses the WordPress database for storage.

== Description ==

Freezer Inventory Manager lets you:

* Add items with name, category, quantity, unit, raw/cooked, location (freezer/shelf/bin), month added, and notes
* Edit any field directly in the inventory table (click a cell to edit, like a spreadsheet)
* Editable month field with inline month picker
* Navigate between cells with Tab, Enter, and Escape keys
* Items are automatically removed when quantity reaches zero
* Search and filter by name, category, freezer, and raw/cooked
* Manage freezers, locations, and categories from a single Settings page
* Managed categories — add/delete category presets from the Settings page; new categories auto-created on import
* Item name autocomplete dropdown that learns new names as you add items; manage the list from the Settings page
* Cascading location dropdowns (freezer → shelf → bin); bin is optional
* Dedicated CSV Import / Export admin page with column documentation and example CSV
* Print-friendly PDF view
* Embed the full inventory on any frontend page with the `[freezer_inventory]` shortcode — all visitors can view and edit (without CSV functions)
* Fully responsive layout — uses full available width on all screen sizes
* Automatic updates from GitHub releases via the WordPress Plugins page

== Installation ==

1. Upload the plugin zip via Plugins > Add New > Upload Plugin, or unzip into wp-content/plugins/
2. Activate "Freezer Inventory Manager" under Plugins
3. Use the "Freezer Inventory" menu in the admin sidebar to manage inventory
4. Set up freezers, locations, categories, and item names under Freezer Inventory > Settings
5. Optionally, add `[freezer_inventory]` to any page to access the inventory from the frontend

== Frequently Asked Questions ==

= Where is data stored? =

In custom database tables: `wp_freezer_inventory`, `wp_freezer_locations`, `wp_freezer_freezers`, `wp_freezer_item_names`, and `wp_freezer_categories` (prefix may vary). Admin pages require `manage_options`. The frontend shortcode is accessible to all visitors.

= How do I get a PDF? =

Click "Download PDF" on the Freezer Inventory page. A new tab opens with a printable table. Use your browser's Print > Save as PDF to save the file.

= How do I export/import CSV? =

Go to Freezer Inventory > CSV Import / Export. Click "Export CSV" to download the inventory. Click "Choose CSV File to Import" to replace all inventory items from a CSV file. The page includes column documentation and a downloadable example CSV. Freezer/shelf/bin columns are supported and locations are auto-created.

= Can I use this on the frontend? =

Yes. Add the shortcode `[freezer_inventory]` to any WordPress page or post. All visitors can view and edit the inventory — no login required.

= How do I edit an item? =

Click any cell in the inventory table to edit it in place, including the month. Use Tab to move between cells in a row, Enter to move down a column, and Escape to cancel. Location cells open cascading freezer/shelf/bin dropdowns. Month cells open a month picker.

= What happens when quantity reaches zero? =

The item is automatically deleted from the inventory.

= Can I delete a freezer, location, category, or item name? =

You can delete them from the Settings page, but only if no inventory items or locations currently reference them.

== Changelog ==

= 1.0.0 =
* Official v1.0.0 release — stable, production-ready version
* Previous releases renumbered to v0.x.x (beta versions)

= 0.0.18 =
* Updated plugin author to ProAdmin with link to https://www.proadmin.com

= 0.0.17 =
* Fixed plugin updater — use read-time filter so update notice appears reliably on the Plugins page

= 0.0.16 =
* Removed hardcoded category badge colors — all categories now use a uniform neutral style

= 0.0.15 =
* Fixed plugin updater not showing upgrade notice on the Plugins page

= 0.0.14 =
* Removed Shelf and Bin filter selectors from inventory — filter by freezer, category, and raw/cooked
* Converted all dates to month/year format — add form uses month picker, table shows "Mon YYYY"
* CSV and PDF exports now use month/year format
* CSV import accepts both "Month Added" and legacy "Date Added" headers

= 0.0.13 =
* Improved frontend shortcode responsiveness — full width on all devices and screen sizes
* Tighter layout on small screens (480px and below) for maximum table space

= 0.0.12 =
* Consolidated Item Names into the Settings page as a tabbed interface (Categories, Freezers, Locations, Item Names)
* Removed Item Names as a separate admin submenu page

= 2.0.11 =
* Plugin update check now runs on every visit to the Plugins page instead of every 12 hours

= 2.1.0 =
* Made plugin fully responsive — uses full available width instead of 1200px max
* Form inputs and filters now fill their containers properly on all screen sizes
* Frontend shortcode embed gets cleaner styling (no borders/shadows) for better theme integration
* Improved mobile layout — full-width buttons, stacked filters, tighter spacing

= 2.0.9 =
* Frontend shortcode now works for all visitors (no login required) with full read-write access
* Combined Freezers and Locations into a single Settings admin page with Categories at the top
* Added managed categories — add/delete category presets from the Settings page
* Categories are now stored in the database instead of being hardcoded
* Category dropdowns (add form, filter, inline editing) populated dynamically from the database
* New categories are auto-created when importing CSV items with unknown categories
* Removed "Shelf" and "Bin" prefixes from location shelf/bin values; existing data migrated automatically
* Added GitHub-based automatic plugin updater — check for updates from the WordPress Plugins page

= 2.0.7 =
* Added Raw / Cooked (preparation) field — required on add form, filterable, inline-editable
* Preparation column included in CSV export/import, PDF, and example CSV

= 2.0.6 =
* Moved CSV import/export to a dedicated admin page with column documentation and downloadable example CSV
* Made Date Added field editable via inline date picker
* Added Date Added field to the add item form, auto-populated with today's date
* Changed quantity step to whole numbers
* Made bin field optional on all forms
* Auto-create locations when adding items with new freezer/shelf/bin combinations
* Created separate frontend view for the shortcode (without CSV import/export)
* Freezer dropdown in add form now pulls from managed freezers table

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
