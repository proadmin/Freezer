(function() {
    'use strict';

    var API_BASE = typeof freezerInventory !== 'undefined' ? freezerInventory.restUrl : '';
    var NONCE = typeof freezerInventory !== 'undefined' ? freezerInventory.nonce : '';
    var LOCATIONS = typeof freezerInventory !== 'undefined' ? freezerInventory.locations : [];
    var FREEZERS = typeof freezerInventory !== 'undefined' ? freezerInventory.freezers : [];
    var ITEM_NAMES = typeof freezerInventory !== 'undefined' ? freezerInventory.itemNames : [];

    var CATEGORIES_RAW = typeof freezerInventory !== 'undefined' ? freezerInventory.categories : [];
    var CATEGORIES = CATEGORIES_RAW.map(function(c) { return c.name; });
    var UNITS = ['lbs', 'oz', 'pieces', 'bags', 'containers', 'packages'];
    var PREPARATIONS = ['Raw', 'Cooked'];

    function headers() {
        var h = { 'Content-Type': 'application/json' };
        if (NONCE) h['X-WP-Nonce'] = NONCE;
        return h;
    }

    // --- Location cascade helpers ---

    function getUniqueFreezers() {
        var seen = {};
        var result = [];
        LOCATIONS.forEach(function(loc) {
            if (!seen[loc.freezer]) {
                seen[loc.freezer] = true;
                result.push(loc.freezer);
            }
        });
        return result.sort();
    }

    function getShelvesForFreezer(freezer) {
        var seen = {};
        var result = [];
        LOCATIONS.forEach(function(loc) {
            if (loc.freezer === freezer && !seen[loc.shelf]) {
                seen[loc.shelf] = true;
                result.push(loc.shelf);
            }
        });
        return result.sort();
    }

    function getBinsForFreezerShelf(freezer, shelf) {
        var seen = {};
        var result = [];
        LOCATIONS.forEach(function(loc) {
            if (loc.freezer === freezer && loc.shelf === shelf && !seen[loc.bin]) {
                seen[loc.bin] = true;
                result.push(loc.bin);
            }
        });
        return result.sort();
    }

    function getLocationId(freezer, shelf, bin) {
        for (var i = 0; i < LOCATIONS.length; i++) {
            if (LOCATIONS[i].freezer === freezer && LOCATIONS[i].shelf === shelf && LOCATIONS[i].bin === bin) {
                return LOCATIONS[i].id;
            }
        }
        return null;
    }

    function getLocationById(id) {
        for (var i = 0; i < LOCATIONS.length; i++) {
            if (LOCATIONS[i].id == id) return LOCATIONS[i];
        }
        return null;
    }

    function locationLabel(item) {
        var parts = [];
        if (item.freezer) parts.push(item.freezer);
        if (item.shelf) parts.push(item.shelf);
        if (item.bin) parts.push(item.bin);
        return parts.join(' / ') || '';
    }

    // --- DOM refs ---

    var addItemForm = document.getElementById('addItemForm');
    var inventoryBody = document.getElementById('inventoryBody');
    var searchInput = document.getElementById('searchInput');
    var categoryFilter = document.getElementById('categoryFilter');
    var freezerFilter = document.getElementById('freezerFilter');
    var shelfFilter = document.getElementById('shelfFilter');
    var binFilter = document.getElementById('binFilter');
    var preparationFilter = document.getElementById('preparationFilter');
    var clearFiltersBtn = document.getElementById('clearFilters');
    var inventoryStats = document.getElementById('inventoryStats');
    var downloadPdfBtn = document.getElementById('downloadPdfBtn');
    var downloadCsvBtn = document.getElementById('downloadCsvBtn');
    var csvFileInput = document.getElementById('csvFileInput');

    var itemFreezer = document.getElementById('itemFreezer');
    var itemShelf = document.getElementById('itemShelf');
    var itemBin = document.getElementById('itemBin');
    var itemDate = document.getElementById('itemDate');

    var allItems = [];
    var filteredItems = [];
    var editingCell = null;

    function setupEventListeners() {
        if (addItemForm) addItemForm.addEventListener('submit', handleAddItem);
        if (searchInput) searchInput.addEventListener('input', applyFilters);
        if (categoryFilter) categoryFilter.addEventListener('change', applyFilters);
        if (freezerFilter) freezerFilter.addEventListener('change', function() {
            populateFilterShelves();
            populateFilterBins();
            applyFilters();
        });
        if (shelfFilter) shelfFilter.addEventListener('change', function() {
            populateFilterBins();
            applyFilters();
        });
        if (binFilter) binFilter.addEventListener('change', applyFilters);
        if (preparationFilter) preparationFilter.addEventListener('change', applyFilters);
        if (clearFiltersBtn) clearFiltersBtn.addEventListener('click', clearFilters);
        if (downloadPdfBtn) downloadPdfBtn.addEventListener('click', handleDownloadPdf);
        if (downloadCsvBtn) downloadCsvBtn.addEventListener('click', handleDownloadCsv);
        if (csvFileInput) csvFileInput.addEventListener('change', handleCsvImport);

        // Cascade for add form
        if (itemFreezer) {
            populateAddFreezers();
            itemFreezer.addEventListener('change', function() {
                populateAddShelves();
                populateAddBins();
            });
        }
        if (itemShelf) {
            itemShelf.addEventListener('change', function() {
                populateAddBins();
            });
        }

        populateAddCategories();
        populateItemNameList();
        setDefaultDate();
    }

    function setDefaultDate() {
        if (!itemDate) return;
        var today = new Date();
        var yyyy = today.getFullYear();
        var mm = String(today.getMonth() + 1).padStart(2, '0');
        var dd = String(today.getDate()).padStart(2, '0');
        itemDate.value = yyyy + '-' + mm + '-' + dd;
    }

    // --- Category select ---

    function populateAddCategories() {
        var sel = document.getElementById('itemCategory');
        if (!sel) return;
        sel.innerHTML = '<option value="">Select category</option>';
        CATEGORIES.forEach(function(cat) {
            var opt = document.createElement('option');
            opt.value = cat;
            opt.textContent = cat;
            sel.appendChild(opt);
        });
    }

    // --- Item name datalist ---

    function populateItemNameList() {
        var datalist = document.getElementById('itemNameList');
        if (!datalist) return;
        datalist.innerHTML = '';
        ITEM_NAMES.forEach(function(n) {
            var opt = document.createElement('option');
            opt.value = n.name;
            datalist.appendChild(opt);
        });
    }

    function refreshItemNames() {
        fetch(API_BASE + '/item-names', { headers: headers() })
            .then(function(r) { return r.ok ? r.json() : []; })
            .then(function(data) {
                ITEM_NAMES = data;
                populateItemNameList();
            })
            .catch(function() {});
    }

    // --- Add form cascade ---

    function populateAddFreezers() {
        if (!itemFreezer) return;
        itemFreezer.innerHTML = '<option value="">Select freezer</option>';
        FREEZERS.forEach(function(f) {
            var opt = document.createElement('option');
            opt.value = f.name;
            opt.textContent = f.name;
            itemFreezer.appendChild(opt);
        });
        populateAddShelves();
    }

    function populateAddShelves() {
        if (!itemShelf) return;
        itemShelf.innerHTML = '<option value="">Select shelf</option>';
        var freezer = itemFreezer ? itemFreezer.value : '';
        if (!freezer) return;
        getShelvesForFreezer(freezer).forEach(function(s) {
            var opt = document.createElement('option');
            opt.value = s;
            opt.textContent = s;
            itemShelf.appendChild(opt);
        });
        populateAddBins();
    }

    function populateAddBins() {
        if (!itemBin) return;
        itemBin.innerHTML = '<option value="">(none)</option>';
        var freezer = itemFreezer ? itemFreezer.value : '';
        var shelf = itemShelf ? itemShelf.value : '';
        if (!freezer || !shelf) return;
        getBinsForFreezerShelf(freezer, shelf).forEach(function(b) {
            if (!b) return;
            var opt = document.createElement('option');
            opt.value = b;
            opt.textContent = b;
            itemBin.appendChild(opt);
        });
    }

    // --- Filter cascade ---

    function populateFilterFreezers() {
        if (!freezerFilter) return;
        var val = freezerFilter.value;
        freezerFilter.innerHTML = '<option value="">All Freezers</option>';
        var freezers = {};
        allItems.forEach(function(item) {
            if (item.freezer) freezers[item.freezer] = true;
        });
        Object.keys(freezers).sort().forEach(function(f) {
            var opt = document.createElement('option');
            opt.value = f;
            opt.textContent = f;
            freezerFilter.appendChild(opt);
        });
        freezerFilter.value = val;
    }

    function populateFilterShelves() {
        if (!shelfFilter) return;
        var val = shelfFilter.value;
        shelfFilter.innerHTML = '<option value="">All Shelves</option>';
        var freezer = freezerFilter ? freezerFilter.value : '';
        var shelves = {};
        allItems.forEach(function(item) {
            if (item.shelf && (!freezer || item.freezer === freezer)) {
                shelves[item.shelf] = true;
            }
        });
        Object.keys(shelves).sort().forEach(function(s) {
            var opt = document.createElement('option');
            opt.value = s;
            opt.textContent = s;
            shelfFilter.appendChild(opt);
        });
        shelfFilter.value = val;
    }

    function populateFilterBins() {
        if (!binFilter) return;
        var val = binFilter.value;
        binFilter.innerHTML = '<option value="">All Bins</option>';
        var freezer = freezerFilter ? freezerFilter.value : '';
        var shelf = shelfFilter ? shelfFilter.value : '';
        var bins = {};
        allItems.forEach(function(item) {
            if (item.bin && (!freezer || item.freezer === freezer) && (!shelf || item.shelf === shelf)) {
                bins[item.bin] = true;
            }
        });
        Object.keys(bins).sort().forEach(function(b) {
            var opt = document.createElement('option');
            opt.value = b;
            opt.textContent = b;
            binFilter.appendChild(opt);
        });
        binFilter.value = val;
    }

    // --- Data ---

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
        var freezer = itemFreezer ? itemFreezer.value : '';
        var shelf = itemShelf ? itemShelf.value : '';
        var bin = itemBin ? itemBin.value : '';

        if (!freezer || !shelf) {
            showError('Please select a Freezer and Shelf.');
            return;
        }

        var locId = getLocationId(freezer, shelf, bin);

        var itemData = {
            name: formData.get('name'),
            category: formData.get('category'),
            quantity: parseFloat(formData.get('quantity')),
            unit: formData.get('unit'),
            preparation: formData.get('preparation') || '',
            date_added: formData.get('date_added') || '',
            notes: formData.get('notes') || ''
        };
        if (locId) {
            itemData.location_id = locId;
        } else {
            itemData.freezer = freezer;
            itemData.shelf = shelf;
            itemData.bin = bin;
        }

        if (!itemData.name || !itemData.category) {
            showError('Please fill in all required fields.');
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
                setDefaultDate();
                populateAddFreezers();
                updateFilters();
                applyFilters();
                updateStats();
                showSuccess('Item added successfully!');
                refreshItemNames();
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
        if (field === 'quantity' && value <= 0) {
            removeItem(itemId);
            return Promise.resolve();
        }

        var data = {};
        data[field] = value;

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
                    item[field] = oldValue;
                    if (fItem) fItem[field] = oldValue;
                    throw new Error(res.json.error || 'Failed to update');
                }
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
        var freezerValue = freezerFilter ? freezerFilter.value : '';
        var shelfValue = shelfFilter ? shelfFilter.value : '';
        var binValue = binFilter ? binFilter.value : '';
        var preparationValue = preparationFilter ? preparationFilter.value : '';

        filteredItems = allItems.filter(function(item) {
            var matchSearch = !searchQuery || (item.name || '').toLowerCase().indexOf(searchQuery) !== -1;
            var matchCategory = !categoryValue || item.category === categoryValue;
            var matchFreezer = !freezerValue || item.freezer === freezerValue;
            var matchShelf = !shelfValue || item.shelf === shelfValue;
            var matchBin = !binValue || item.bin === binValue;
            var matchPreparation = !preparationValue || item.preparation === preparationValue;
            return matchSearch && matchCategory && matchFreezer && matchShelf && matchBin && matchPreparation;
        });
        renderInventory();
        updateStats();
    }

    function clearFilters() {
        if (searchInput) searchInput.value = '';
        if (categoryFilter) categoryFilter.value = '';
        if (freezerFilter) freezerFilter.value = '';
        if (shelfFilter) shelfFilter.value = '';
        if (binFilter) binFilter.value = '';
        if (preparationFilter) preparationFilter.value = '';
        populateFilterShelves();
        populateFilterBins();
        applyFilters();
    }

    function updateFilters() {
        if (categoryFilter) {
            var prev = categoryFilter.value;
            categoryFilter.innerHTML = '<option value="">All Categories</option>';
            CATEGORIES.forEach(function(cat) {
                var opt = document.createElement('option');
                opt.value = cat;
                opt.textContent = cat;
                categoryFilter.appendChild(opt);
            });
            categoryFilter.value = prev;
        }
        populateFilterFreezers();
        populateFilterShelves();
        populateFilterBins();
    }

    function formatDate(dateStr) {
        if (!dateStr) return '';
        return new Date(dateStr).toLocaleDateString('en-US', { year: 'numeric', month: 'short', day: 'numeric' });
    }

    function toInputDate(dateStr) {
        if (!dateStr) return '';
        var d = new Date(dateStr);
        var yyyy = d.getFullYear();
        var mm = String(d.getMonth() + 1).padStart(2, '0');
        var dd = String(d.getDate()).padStart(2, '0');
        return yyyy + '-' + mm + '-' + dd;
    }

    function escapeHtml(text) {
        if (!text) return '';
        var div = document.createElement('div');
        div.textContent = text;
        return div.innerHTML;
    }

    // --- Inline editing ---

    function makeEditable(td, item, field, type) {
        td.classList.add('editable-cell');
        td.addEventListener('click', function() {
            startEditing(td, item, field, type);
        });
    }

    function startEditing(td, item, field, type) {
        if (td.querySelector('input, select, .cascade-editor')) return;
        if (editingCell && editingCell !== td) {
            commitEdit(editingCell);
        }
        editingCell = td;
        td.classList.add('editing');
        td.innerHTML = '';

        if (type === 'select-location-cascade') {
            startLocationCascadeEdit(td, item);
            return;
        }

        var currentValue = item[field];
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
        } else if (type === 'select-preparation') {
            input = document.createElement('select');
            PREPARATIONS.forEach(function(p) {
                var opt = document.createElement('option');
                opt.value = p;
                opt.textContent = p;
                if (p === currentValue) opt.selected = true;
                input.appendChild(opt);
            });
        } else if (type === 'number') {
            input = document.createElement('input');
            input.type = 'number';
            input.step = '1';
            input.min = '0';
            input.value = currentValue != null ? currentValue : '';
        } else if (type === 'date') {
            input = document.createElement('input');
            input.type = 'date';
            input.value = toInputDate(currentValue);
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
                var cells = td.parentElement.querySelectorAll('.editable-cell');
                var cellArr = Array.prototype.slice.call(cells);
                var curIdx = cellArr.indexOf(td);
                var nextCell = e.shiftKey ? cellArr[curIdx - 1] : cellArr[curIdx + 1];
                if (nextCell) nextCell.click();
            }
        });

        if (input.tagName === 'SELECT') {
            input.addEventListener('change', function() {
                commitEdit(td);
            });
        }

        input.addEventListener('blur', function() {
            setTimeout(function() {
                if (editingCell === td) commitEdit(td);
            }, 100);
        });
    }

    function startLocationCascadeEdit(td, item) {
        var container = document.createElement('div');
        container.className = 'cascade-editor';
        container.dataset.itemId = item.id;
        container.dataset.originalLocationId = item.location_id || '';

        var selF = document.createElement('select');
        selF.className = 'cell-editor';
        selF.innerHTML = '<option value="">Freezer</option>';
        getUniqueFreezers().forEach(function(f) {
            var opt = document.createElement('option');
            opt.value = f;
            opt.textContent = f;
            if (f === item.freezer) opt.selected = true;
            selF.appendChild(opt);
        });

        var selS = document.createElement('select');
        selS.className = 'cell-editor';

        var selB = document.createElement('select');
        selB.className = 'cell-editor';

        function populateShelves() {
            selS.innerHTML = '<option value="">Shelf</option>';
            var freezer = selF.value;
            if (!freezer) return;
            getShelvesForFreezer(freezer).forEach(function(s) {
                var opt = document.createElement('option');
                opt.value = s;
                opt.textContent = s;
                if (s === item.shelf && freezer === item.freezer) opt.selected = true;
                selS.appendChild(opt);
            });
        }

        function populateBins() {
            selB.innerHTML = '<option value="">(none)</option>';
            var freezer = selF.value;
            var shelf = selS.value;
            if (!freezer || !shelf) return;
            getBinsForFreezerShelf(freezer, shelf).forEach(function(b) {
                if (!b) return;
                var opt = document.createElement('option');
                opt.value = b;
                opt.textContent = b;
                if (b === item.bin && freezer === item.freezer && shelf === item.shelf) opt.selected = true;
                selB.appendChild(opt);
            });
        }

        populateShelves();
        populateBins();

        selF.addEventListener('change', function() {
            populateShelves();
            populateBins();
        });
        selS.addEventListener('change', function() {
            populateBins();
        });
        selB.addEventListener('change', function() {
            var locId = getLocationId(selF.value, selS.value, selB.value);
            if (locId) commitLocationEdit(td, item, locId);
        });

        container.appendChild(selF);
        container.appendChild(selS);
        container.appendChild(selB);
        td.appendChild(container);
        selF.focus();

        container.addEventListener('keydown', function(e) {
            if (e.key === 'Escape') {
                cancelEdit(td, item, 'location_id', 'select-location-cascade');
            }
        });

        var blurTimeout;
        function handleBlur() {
            clearTimeout(blurTimeout);
            blurTimeout = setTimeout(function() {
                if (editingCell === td) {
                    var active = document.activeElement;
                    if (active && container.contains(active)) return;
                    var locId = getLocationId(selF.value, selS.value, selB.value);
                    if (locId) {
                        commitLocationEdit(td, item, locId);
                    } else {
                        cancelEdit(td, item, 'location_id', 'select-location-cascade');
                    }
                }
            }, 150);
        }
        selF.addEventListener('blur', handleBlur);
        selS.addEventListener('blur', handleBlur);
        selB.addEventListener('blur', handleBlur);
        selF.addEventListener('focus', function() { clearTimeout(blurTimeout); });
        selS.addEventListener('focus', function() { clearTimeout(blurTimeout); });
        selB.addEventListener('focus', function() { clearTimeout(blurTimeout); });
    }

    function commitLocationEdit(td, item, newLocationId) {
        td.classList.remove('editing');
        editingCell = null;
        var loc = getLocationById(newLocationId);
        td.textContent = loc ? locationLabel(loc) : '';

        var oldLocationId = item.location_id;
        if (newLocationId != oldLocationId) {
            if (loc) {
                item.freezer = loc.freezer;
                item.shelf = loc.shelf;
                item.bin = loc.bin;
                item.location_id = newLocationId;
            }
            saveField(item.id, 'location_id', newLocationId);
        }
    }

    function commitEdit(td) {
        var cascade = td.querySelector('.cascade-editor');
        if (cascade) {
            var selects = cascade.querySelectorAll('select');
            var freezer = selects[0] ? selects[0].value : '';
            var shelf = selects[1] ? selects[1].value : '';
            var bin = selects[2] ? selects[2].value : '';
            var locId = getLocationId(freezer, shelf, bin);
            var itemId = cascade.dataset.itemId;
            var item = allItems.find(function(i) { return i.id === itemId; });
            if (locId && item) {
                commitLocationEdit(td, item, locId);
            } else if (item) {
                cancelEdit(td, item, 'location_id', 'select-location-cascade');
            }
            return;
        }

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

        if (field === 'quantity') {
            var item = allItems.find(function(i) { return i.id === itemId; });
            td.textContent = newValue + (item ? ' ' + item.unit : '');
        } else if (field === 'category') {
            td.innerHTML = '<span class="category-badge category-badge-' + escapeHtml(String(newValue)).replace(/\s+/g, '-') + '">' + escapeHtml(String(newValue)) + '</span>';
        } else if (field === 'date_added') {
            td.textContent = newValue ? formatDate(newValue) : '';
        } else {
            td.textContent = newValue != null ? String(newValue) : '';
        }

        var compareOriginal = field === 'date_added' ? toInputDate(originalValue) : originalValue;
        if (String(newValue) !== compareOriginal) {
            saveField(itemId, field, newValue);
        }
    }

    function cancelEdit(td, item, field, type) {
        td.classList.remove('editing');
        editingCell = null;
        if (type === 'select-location-cascade') {
            td.textContent = locationLabel(item);
        } else if (field === 'quantity') {
            td.textContent = item[field] + ' ' + item.unit;
        } else if (field === 'category') {
            var value = item[field];
            td.innerHTML = '<span class="category-badge category-badge-' + escapeHtml(String(value)).replace(/\s+/g, '-') + '">' + escapeHtml(String(value)) + '</span>';
        } else if (field === 'date_added') {
            td.textContent = formatDate(item[field]);
        } else {
            var val = item[field];
            td.textContent = val != null ? String(val) : '';
        }
    }

    // --- Rendering ---

    function createItemRow(item) {
        var tr = document.createElement('tr');
        tr.dataset.itemId = item.id;

        var tdName = document.createElement('td');
        tdName.textContent = item.name || '';
        makeEditable(tdName, item, 'name', 'text');
        tr.appendChild(tdName);

        var tdCategory = document.createElement('td');
        tdCategory.innerHTML = '<span class="category-badge category-badge-' + escapeHtml(item.category).replace(/\s+/g, '-') + '">' + escapeHtml(item.category) + '</span>';
        makeEditable(tdCategory, item, 'category', 'select-category');
        tr.appendChild(tdCategory);

        var tdQty = document.createElement('td');
        tdQty.textContent = item.quantity + ' ' + (item.unit || '');
        makeEditable(tdQty, item, 'quantity', 'number');
        tr.appendChild(tdQty);

        var tdUnit = document.createElement('td');
        tdUnit.textContent = item.unit || '';
        makeEditable(tdUnit, item, 'unit', 'select-unit');
        tr.appendChild(tdUnit);

        var tdPrep = document.createElement('td');
        tdPrep.textContent = item.preparation || '';
        makeEditable(tdPrep, item, 'preparation', 'select-preparation');
        tr.appendChild(tdPrep);

        var tdLoc = document.createElement('td');
        tdLoc.textContent = locationLabel(item);
        makeEditable(tdLoc, item, 'location_id', 'select-location-cascade');
        tr.appendChild(tdLoc);

        var tdNotes = document.createElement('td');
        tdNotes.textContent = item.notes || '';
        tdNotes.className = 'cell-notes';
        makeEditable(tdNotes, item, 'notes', 'text');
        tr.appendChild(tdNotes);

        var tdDate = document.createElement('td');
        tdDate.className = 'cell-date';
        tdDate.textContent = formatDate(item.date_added);
        makeEditable(tdDate, item, 'date_added', 'date');
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
            td.colSpan = 8;
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

    function handleDownloadCsv() {
        fetch(API_BASE + '/items/export-csv', { headers: headers() })
            .then(function(r) { return r.json(); })
            .then(function(data) {
                var blob = new Blob([data.csv], { type: 'text/csv;charset=utf-8;' });
                var url = URL.createObjectURL(blob);
                var a = document.createElement('a');
                a.href = url;
                a.download = data.filename || 'freezer-inventory.csv';
                document.body.appendChild(a);
                a.click();
                document.body.removeChild(a);
                URL.revokeObjectURL(url);
            })
            .catch(function() { showError('Failed to export CSV.'); });
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
