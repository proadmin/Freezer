(function() {
    'use strict';

    var API_BASE = typeof freezerInventory !== 'undefined' ? freezerInventory.restUrl : '';
    var NONCE = typeof freezerInventory !== 'undefined' ? freezerInventory.nonce : '';
    var LOCATIONS = typeof freezerInventory !== 'undefined' ? freezerInventory.locations : [];

    var CATEGORIES = ['Meat', 'Vegetables', 'Fruits', 'Prepared Meals', 'Dairy', 'Bread', 'Other'];
    var UNITS = ['lbs', 'oz', 'pieces', 'bags', 'containers', 'packages'];

    function headers() {
        var h = { 'Content-Type': 'application/json' };
        if (NONCE) h['X-WP-Nonce'] = NONCE;
        return h;
    }

    var addItemForm = document.getElementById('addItemForm');
    var inventoryBody = document.getElementById('inventoryBody');
    var searchInput = document.getElementById('searchInput');
    var categoryFilter = document.getElementById('categoryFilter');
    var zoneFilter = document.getElementById('zoneFilter');
    var clearFiltersBtn = document.getElementById('clearFilters');
    var inventoryStats = document.getElementById('inventoryStats');
    var downloadPdfBtn = document.getElementById('downloadPdfBtn');
    var csvFileInput = document.getElementById('csvFileInput');

    var allItems = [];
    var filteredItems = [];
    var editingCell = null; // track currently editing cell to avoid duplicates

    function setupEventListeners() {
        if (addItemForm) addItemForm.addEventListener('submit', handleAddItem);
        if (searchInput) searchInput.addEventListener('input', applyFilters);
        if (categoryFilter) categoryFilter.addEventListener('change', applyFilters);
        if (zoneFilter) zoneFilter.addEventListener('change', applyFilters);
        if (clearFiltersBtn) clearFiltersBtn.addEventListener('click', clearFilters);
        if (downloadPdfBtn) downloadPdfBtn.addEventListener('click', handleDownloadPdf);
        if (csvFileInput) csvFileInput.addEventListener('change', handleCsvImport);
    }

    function loadInventory() {
        fetch(API_BASE + '/items', { headers: headers() })
            .then(function(r) { return r.ok ? r.json() : Promise.reject(new Error('Failed to load')); })
            .then(function(data) {
                allItems = data;
                filteredItems = data.slice();
                updateFilters();
                renderInventory();
                updateStats();
            })
            .catch(function(err) {
                showError('Failed to load inventory: ' + err.message);
            });
    }

    function handleAddItem(e) {
        e.preventDefault();
        var formData = new FormData(addItemForm);
        var itemData = {
            name: formData.get('name'),
            category: formData.get('category'),
            quantity: parseFloat(formData.get('quantity')),
            unit: formData.get('unit'),
            freezer_zone: formData.get('freezer_zone'),
            notes: formData.get('notes') || ''
        };
        if (!itemData.name || !itemData.category || !itemData.freezer_zone) {
            showError('Please fill in all required fields (including Location).');
            return;
        }
        fetch(API_BASE + '/items', {
            method: 'POST',
            headers: headers(),
            body: JSON.stringify(itemData)
        })
            .then(function(r) { return r.json().then(function(j) { return { ok: r.ok, json: j }; }); })
            .then(function(res) {
                if (!res.ok) throw new Error(res.json.error || 'Failed to add item');
                allItems.unshift(res.json);
                filteredItems = allItems.slice();
                addItemForm.reset();
                updateFilters();
                applyFilters();
                updateStats();
                showSuccess('Item added successfully!');
            })
            .catch(function(err) {
                showError('Failed to add item: ' + err.message);
            });
    }

    function removeItem(itemId) {
        fetch(API_BASE + '/items/' + encodeURIComponent(itemId), { method: 'DELETE', headers: headers() })
            .then(function(r) {
                if (!r.ok) throw new Error('Failed to remove item');
                allItems = allItems.filter(function(i) { return i.id !== itemId; });
                filteredItems = filteredItems.filter(function(i) { return i.id !== itemId; });
                updateFilters();
                renderInventory();
                updateStats();
                showSuccess('Item used up and removed.');
            })
            .catch(function(err) { showError(err.message); });
    }

    function saveField(itemId, field, value) {
        // Auto-delete when quantity reaches 0
        if (field === 'quantity' && value <= 0) {
            removeItem(itemId);
            return Promise.resolve();
        }

        var data = {};
        data[field] = value;

        // Update local state optimistically
        var item = allItems.find(function(i) { return i.id === itemId; });
        if (!item) return Promise.reject(new Error('Item not found'));
        var oldValue = item[field];
        item[field] = value;
        var fItem = filteredItems.find(function(i) { return i.id === itemId; });
        if (fItem) fItem[field] = value;

        return fetch(API_BASE + '/items/' + encodeURIComponent(itemId), {
            method: 'PUT',
            headers: headers(),
            body: JSON.stringify(data)
        })
            .then(function(r) { return r.json().then(function(j) { return { ok: r.ok, json: j }; }); })
            .then(function(res) {
                if (!res.ok) {
                    // Revert on failure
                    item[field] = oldValue;
                    if (fItem) fItem[field] = oldValue;
                    throw new Error(res.json.error || 'Failed to update');
                }
                // Sync full item from server
                var idx = allItems.findIndex(function(i) { return i.id === itemId; });
                if (idx !== -1) allItems[idx] = res.json;
                var fidx = filteredItems.findIndex(function(i) { return i.id === itemId; });
                if (fidx !== -1) filteredItems[fidx] = res.json;
                updateStats();
            })
            .catch(function(err) {
                showError('Failed to update: ' + err.message);
                renderInventory();
            });
    }

    function applyFilters() {
        var searchQuery = (searchInput && searchInput.value || '').toLowerCase().trim();
        var categoryValue = categoryFilter ? categoryFilter.value : '';
        var zoneValue = zoneFilter ? zoneFilter.value : '';
        filteredItems = allItems.filter(function(item) {
            var matchSearch = !searchQuery || (item.name || '').toLowerCase().indexOf(searchQuery) !== -1;
            var matchCategory = !categoryValue || item.category === categoryValue;
            var matchZone = !zoneValue || item.freezer_zone === zoneValue;
            return matchSearch && matchCategory && matchZone;
        });
        renderInventory();
        updateStats();
    }

    function clearFilters() {
        if (searchInput) searchInput.value = '';
        if (categoryFilter) categoryFilter.value = '';
        if (zoneFilter) zoneFilter.value = '';
        applyFilters();
    }

    function updateFilters() {
        var categories = [];
        allItems.forEach(function(item) {
            if (item.category && categories.indexOf(item.category) === -1) categories.push(item.category);
        });
        categories.sort();
        if (categoryFilter) {
            categoryFilter.innerHTML = '<option value="">All Categories</option>';
            categories.forEach(function(cat) {
                var opt = document.createElement('option');
                opt.value = cat;
                opt.textContent = cat;
                categoryFilter.appendChild(opt);
            });
        }
        var zones = [];
        allItems.forEach(function(item) {
            if (item.freezer_zone && zones.indexOf(item.freezer_zone) === -1) zones.push(item.freezer_zone);
        });
        zones.sort();
        if (zoneFilter) {
            zoneFilter.innerHTML = '<option value="">All Locations</option>';
            zones.forEach(function(zone) {
                var opt = document.createElement('option');
                opt.value = zone;
                opt.textContent = zone;
                zoneFilter.appendChild(opt);
            });
        }
    }

    function escapeHtml(text) {
        if (!text) return '';
        var div = document.createElement('div');
        div.textContent = text;
        return div.innerHTML;
    }

    // --- Inline editing helpers ---

    function makeEditable(td, item, field, type) {
        td.classList.add('editable-cell');
        td.addEventListener('click', function() {
            startEditing(td, item, field, type);
        });
    }

    function startEditing(td, item, field, type) {
        // Don't re-enter if already editing this cell
        if (td.querySelector('input, select')) return;

        // If another cell is being edited, commit it first
        if (editingCell && editingCell !== td) {
            commitEdit(editingCell);
        }

        editingCell = td;
        var currentValue = item[field];
        td.classList.add('editing');
        td.innerHTML = '';

        var input;
        if (type === 'select-category') {
            input = document.createElement('select');
            CATEGORIES.forEach(function(cat) {
                var opt = document.createElement('option');
                opt.value = cat;
                opt.textContent = cat;
                if (cat === currentValue) opt.selected = true;
                input.appendChild(opt);
            });
        } else if (type === 'select-unit') {
            input = document.createElement('select');
            UNITS.forEach(function(u) {
                var opt = document.createElement('option');
                opt.value = u;
                opt.textContent = u;
                if (u === currentValue) opt.selected = true;
                input.appendChild(opt);
            });
        } else if (type === 'select-location') {
            input = document.createElement('select');
            LOCATIONS.forEach(function(loc) {
                var opt = document.createElement('option');
                opt.value = loc;
                opt.textContent = loc;
                if (loc === currentValue) opt.selected = true;
                input.appendChild(opt);
            });
        } else if (type === 'number') {
            input = document.createElement('input');
            input.type = 'number';
            input.step = '0.001';
            input.min = '0';
            input.value = currentValue != null ? currentValue : '';
        } else {
            input = document.createElement('input');
            input.type = 'text';
            input.value = currentValue != null ? currentValue : '';
        }

        input.className = 'cell-editor';
        input.dataset.itemId = item.id;
        input.dataset.field = field;
        input.dataset.originalValue = currentValue != null ? String(currentValue) : '';

        td.appendChild(input);
        input.focus();
        if (input.select) input.select();

        input.addEventListener('keydown', function(e) {
            if (e.key === 'Enter') {
                e.preventDefault();
                commitEdit(td);
                // Move to next editable row same column
                var tr = td.parentElement;
                var nextRow = tr.nextElementSibling;
                if (nextRow) {
                    var idx = Array.prototype.indexOf.call(tr.children, td);
                    var nextTd = nextRow.children[idx];
                    if (nextTd && nextTd.classList.contains('editable-cell')) {
                        nextTd.click();
                    }
                }
            } else if (e.key === 'Escape') {
                cancelEdit(td, item, field, type);
            } else if (e.key === 'Tab') {
                e.preventDefault();
                commitEdit(td);
                // Move to next editable cell in the row
                var cells = td.parentElement.querySelectorAll('.editable-cell');
                var cellArr = Array.prototype.slice.call(cells);
                var curIdx = cellArr.indexOf(td);
                var nextCell = e.shiftKey ? cellArr[curIdx - 1] : cellArr[curIdx + 1];
                if (nextCell) {
                    nextCell.click();
                }
            }
        });

        // For selects, commit on change
        if (input.tagName === 'SELECT') {
            input.addEventListener('change', function() {
                commitEdit(td);
            });
        }

        input.addEventListener('blur', function() {
            // Small delay to allow click events on other cells to fire first
            setTimeout(function() {
                if (editingCell === td) {
                    commitEdit(td);
                }
            }, 100);
        });
    }

    function commitEdit(td) {
        var input = td.querySelector('input, select');
        if (!input) return;

        var itemId = input.dataset.itemId;
        var field = input.dataset.field;
        var originalValue = input.dataset.originalValue;
        var newValue = input.value;

        if (field === 'quantity') {
            newValue = parseFloat(newValue);
            if (isNaN(newValue) || newValue < 0) {
                newValue = parseFloat(originalValue);
            }
        }

        td.classList.remove('editing');
        editingCell = null;

        // Update display immediately
        if (field === 'quantity') {
            var item = allItems.find(function(i) { return i.id === itemId; });
            td.textContent = newValue + (item ? ' ' + item.unit : '');
        } else if (field === 'category') {
            td.innerHTML = '<span class="category-badge category-badge-' + escapeHtml(String(newValue)).replace(/\s+/g, '-') + '">' + escapeHtml(String(newValue)) + '</span>';
        } else {
            td.textContent = newValue != null ? String(newValue) : '';
        }

        // Only save if value actually changed
        if (String(newValue) !== originalValue) {
            saveField(itemId, field, newValue);
        }
    }

    function cancelEdit(td, item, field, type) {
        td.classList.remove('editing');
        editingCell = null;
        var value = item[field];
        if (field === 'quantity') {
            td.textContent = value + ' ' + item.unit;
        } else if (field === 'category') {
            td.innerHTML = '<span class="category-badge category-badge-' + escapeHtml(String(value)).replace(/\s+/g, '-') + '">' + escapeHtml(String(value)) + '</span>';
        } else {
            td.textContent = value != null ? String(value) : '';
        }
    }

    // --- Rendering ---

    function createItemRow(item) {
        var tr = document.createElement('tr');
        tr.dataset.itemId = item.id;

        // Name
        var tdName = document.createElement('td');
        tdName.textContent = item.name || '';
        makeEditable(tdName, item, 'name', 'text');
        tr.appendChild(tdName);

        // Category
        var tdCategory = document.createElement('td');
        tdCategory.innerHTML = '<span class="category-badge category-badge-' + escapeHtml(item.category).replace(/\s+/g, '-') + '">' + escapeHtml(item.category) + '</span>';
        makeEditable(tdCategory, item, 'category', 'select-category');
        tr.appendChild(tdCategory);

        // Quantity
        var tdQty = document.createElement('td');
        tdQty.textContent = item.quantity + ' ' + (item.unit || '');
        makeEditable(tdQty, item, 'quantity', 'number');
        tr.appendChild(tdQty);

        // Unit
        var tdUnit = document.createElement('td');
        tdUnit.textContent = item.unit || '';
        makeEditable(tdUnit, item, 'unit', 'select-unit');
        tr.appendChild(tdUnit);

        // Location
        var tdLoc = document.createElement('td');
        tdLoc.textContent = item.freezer_zone || '';
        makeEditable(tdLoc, item, 'freezer_zone', 'select-location');
        tr.appendChild(tdLoc);

        // Notes
        var tdNotes = document.createElement('td');
        tdNotes.textContent = item.notes || '';
        tdNotes.className = 'cell-notes';
        makeEditable(tdNotes, item, 'notes', 'text');
        tr.appendChild(tdNotes);

        // Date Added
        var tdDate = document.createElement('td');
        tdDate.className = 'cell-date';
        tdDate.textContent = item.date_added ? new Date(item.date_added).toLocaleDateString('en-US', { year: 'numeric', month: 'short', day: 'numeric' }) : '';
        tr.appendChild(tdDate);

        return tr;
    }

    function renderInventory() {
        if (!inventoryBody) return;
        editingCell = null;
        inventoryBody.innerHTML = '';

        if (filteredItems.length === 0) {
            var tr = document.createElement('tr');
            var td = document.createElement('td');
            td.colSpan = 7;
            td.className = 'empty-message';
            td.textContent = allItems.length === 0 ? 'No items in freezer. Add your first item above!' : 'No items found. Try adjusting your filters.';
            tr.appendChild(td);
            inventoryBody.appendChild(tr);
            return;
        }

        filteredItems.forEach(function(item) {
            inventoryBody.appendChild(createItemRow(item));
        });
    }

    function updateStats() {
        if (!inventoryStats) return;
        if (filteredItems.length === 0 && allItems.length === 0) {
            inventoryStats.innerHTML = '';
            return;
        }
        var catCounts = {};
        filteredItems.forEach(function(item) {
            catCounts[item.category] = (catCounts[item.category] || 0) + 1;
        });
        var catSummary = Object.keys(catCounts).map(function(c) { return c + ': ' + catCounts[c]; }).join(' \u2022 ');
        inventoryStats.innerHTML = '<strong>Showing ' + filteredItems.length + ' of ' + allItems.length + ' items</strong>' + (catSummary ? ' \u2022 ' + catSummary : '');
    }

    function showError(message) {
        var formSection = document.querySelector('.form-section');
        if (!formSection) return;
        var div = document.createElement('div');
        div.className = 'error-message';
        div.textContent = message;
        formSection.insertBefore(div, formSection.firstChild);
        setTimeout(function() { div.remove(); }, 5000);
    }

    function showSuccess(message) {
        var formSection = document.querySelector('.form-section');
        if (!formSection) return;
        var div = document.createElement('div');
        div.className = 'success-message';
        div.textContent = message;
        formSection.insertBefore(div, formSection.firstChild);
        setTimeout(function() { div.remove(); }, 3000);
    }

    function handleCsvImport() {
        var file = csvFileInput.files[0];
        if (!file) return;
        if (!confirm('WARNING: Importing a CSV will replace ALL current inventory items. This cannot be undone.\n\nAre you sure you want to continue?')) {
            csvFileInput.value = '';
            return;
        }
        var formData = new FormData();
        formData.append('file', file);
        var h = {};
        if (NONCE) h['X-WP-Nonce'] = NONCE;
        fetch(API_BASE + '/items/import-csv', {
            method: 'POST',
            headers: h,
            body: formData
        })
            .then(function(r) { return r.json().then(function(j) { return { ok: r.ok, json: j }; }); })
            .then(function(res) {
                csvFileInput.value = '';
                if (!res.ok) throw new Error(res.json.error || 'Import failed');
                showSuccess('Imported ' + res.json.imported + ' items.');
                loadInventory();
            })
            .catch(function(err) {
                csvFileInput.value = '';
                showError('CSV import failed: ' + err.message);
            });
    }

    function handleDownloadPdf() {
        fetch(API_BASE + '/inventory/pdf', { headers: headers() })
            .then(function(r) { return r.json(); })
            .then(function(data) {
                var w = window.open('', '_blank');
                w.document.write(data.html);
                w.document.close();
                w.focus();
            })
            .catch(function() { showError('Failed to load PDF view.'); });
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', function() {
            setupEventListeners();
            loadInventory();
        });
    } else {
        setupEventListeners();
        loadInventory();
    }
})();
