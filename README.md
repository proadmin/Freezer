# Freezer Inventory Manager

A WordPress plugin to manage your freezer inventory with an editable spreadsheet table, managed locations, and PDF/CSV export.

## Features

- **Editable table** — click any cell to edit it in place, like a spreadsheet
- **Editable dates** — inline date picker for the Date Added column
- **Keyboard navigation** — Tab between cells, Enter to move down, Escape to cancel
- **Add items** with name, category, quantity, unit, raw/cooked, location (freezer/shelf/bin), date, and notes
- **Cascading location dropdowns** — freezer → shelf → bin on add form, filters, and inline editing; bin is optional
- **Managed freezers** — add/delete freezer names from the Freezers admin page
- **Managed locations** — add/delete freezer/shelf/bin combinations from the Locations admin page
- **Item name autocomplete** — dropdown learns new names as you add items; manage the list from the Item Names admin page
- **Delete protection** — freezers, locations, and item names can't be deleted while in use
- **Auto-remove** items when quantity reaches zero
- **Raw / Cooked field** — required preparation status with filtering support
- **Search and filter** by name, category, freezer, shelf, bin, and raw/cooked
- **CSV import/export** — dedicated admin page with column documentation and example CSV
- **PDF export** — print-friendly view in a new tab
- **Frontend shortcode** — embed on any page with `[freezer_inventory]` (without CSV functions)
- **Admin-only access** — requires `manage_options` capability

## Installation

1. Download `freezer-inventory.zip`
2. In WordPress, go to Plugins > Add New > Upload Plugin
3. Upload the zip and activate
4. Find "Freezer Inventory" in the admin sidebar
5. Set up freezers under Freezer Inventory > Freezers
6. Set up locations under Freezer Inventory > Locations
7. Start adding inventory items

### Frontend usage

Add `[freezer_inventory]` to any page or post. The user must be logged in as an administrator.

## Admin Pages

- **Freezer Inventory** — main inventory table with add form, filters, and PDF export
- **Locations** — manage freezer/shelf/bin location combinations
- **Freezers** — manage freezer names used in locations
- **Item Names** — manage the autocomplete list for item names
- **CSV Import / Export** — export inventory as CSV, import from CSV with column docs and example file

## Requirements

- WordPress 5.0+
- PHP 7.4+

## License

GPL v2 or later
