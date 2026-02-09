#!/bin/bash
# Build freezer-inventory.zip with auto-incremented patch version.
set -e
cd "$(dirname "$0")"

PLUGIN_FILE="freezer-inventory/freezer-inventory.php"
README_FILE="freezer-inventory/readme.txt"

# Read current version
CURRENT=$(grep -m1 "^ \* Version:" "$PLUGIN_FILE" | sed 's/.*Version: *//')
MAJOR=$(echo "$CURRENT" | cut -d. -f1)
MINOR=$(echo "$CURRENT" | cut -d. -f2)
PATCH=$(echo "$CURRENT" | cut -d. -f3)

NEW_PATCH=$((PATCH + 1))
NEW_VERSION="$MAJOR.$MINOR.$NEW_PATCH"

echo "Bumping version: $CURRENT -> $NEW_VERSION"

# Update plugin header
sed -i '' "s/^ \* Version: .*/ * Version: $NEW_VERSION/" "$PLUGIN_FILE"

# Update PHP constant
sed -i '' "s/define( 'FREEZER_INVENTORY_VERSION', '.*'/define( 'FREEZER_INVENTORY_VERSION', '$NEW_VERSION'/" "$PLUGIN_FILE"

# Update readme.txt stable tag
sed -i '' "s/^Stable tag: .*/Stable tag: $NEW_VERSION/" "$README_FILE"

# Rebuild zip
rm -f freezer-inventory.zip
zip -r freezer-inventory.zip freezer-inventory/

echo "Built freezer-inventory.zip (v$NEW_VERSION)"
