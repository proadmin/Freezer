# Freezer Inventory Manager

A WordPress plugin to manage your freezer inventory with an editable spreadsheet table, categories, locations, and PDF export.

## Features

- **Editable table** — click any cell to edit it in place, like a spreadsheet
- **Keyboard navigation** — Tab between cells, Enter to move down, Escape to cancel
- **Add items** with name, category, quantity, unit, location, and notes
- **Auto-remove** items when quantity reaches zero
- **Search and filter** by name, category, or location
- **PDF export** — print-friendly view in a new tab
- **Frontend shortcode** — embed on any page with `[freezer_inventory]`
- **Admin-only access** — requires `manage_options` capability

## Installation

1. Download `freezer-inventory.zip`
2. In WordPress, go to Plugins > Add New > Upload Plugin
3. Upload the zip and activate
4. Find "Freezer Inventory" in the admin sidebar

### Frontend usage

Add `[freezer_inventory]` to any page or post. The user must be logged in as an administrator.

## Locations

Fixed locations: Shelf 1 Bin 1–3, Shelf 2 Bin 1–2, Shelf 2 Bulk, Door Shelf 1–2.

## Requirements

- WordPress 5.0+
- PHP 7.4+

## License

GPL v2 or later
