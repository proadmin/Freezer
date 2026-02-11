# Freezer Inventory Manager

A WordPress plugin to manage your freezer inventory with an editable spreadsheet table, managed locations, and PDF/CSV export.

## Features

- **Editable table** — click any cell to edit it in place, like a spreadsheet
- **Editable dates** — inline date picker for the Date Added column
- **Keyboard navigation** — Tab between cells, Enter to move down, Escape to cancel
- **Add items** with name, category, quantity, unit, raw/cooked, location (freezer/shelf/bin), date, and notes
- **Cascading location dropdowns** — freezer → shelf → bin on add form, filters, and inline editing; bin is optional
- **Managed freezers** — add/delete freezer names from the Settings page
- **Managed locations** — add/delete freezer/shelf/bin combinations from the Settings page
- **Managed categories** — add/delete category presets from the Settings page; new categories auto-created on import
- **Item name autocomplete** — dropdown learns new names as you add items; manage the list from the Settings page
- **Delete protection** — freezers, locations, and item names can't be deleted while in use
- **Auto-remove** items when quantity reaches zero
- **Raw / Cooked field** — required preparation status with filtering support
- **Search and filter** by name, category, freezer, shelf, bin, and raw/cooked
- **CSV import/export** — dedicated admin page with column documentation and example CSV
- **PDF export** — print-friendly view in a new tab
- **Frontend shortcode** — embed on any page with `[freezer_inventory]` — all visitors can view and edit (without CSV functions)
- **Fully responsive** — uses full available width; optimized for mobile, tablet, and desktop
- **Automatic updates** — checks GitHub releases for new versions via the WordPress Plugins page
- **Admin pages** — require `manage_options` capability

## Installation

1. Download `freezer-inventory.zip`
2. In WordPress, go to Plugins > Add New > Upload Plugin
3. Upload the zip and activate
4. Find "Freezer Inventory" in the admin sidebar
5. Set up freezers, locations, categories, and item names under Freezer Inventory > Settings
6. Start adding inventory items

### Frontend usage

Add `[freezer_inventory]` to any page or post. All visitors can view and edit the inventory — no login required.

## Admin Pages

- **Freezer Inventory** — main inventory table with add form, filters, and PDF export
- **Settings** — tabbed page to manage categories, freezers, locations, and item names
- **CSV Import / Export** — export inventory as CSV, import from CSV with column docs and example file

## Requirements

- WordPress 5.0+
- PHP 7.4+

## License

GPL v2 or later
