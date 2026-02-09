(function() {
    'use strict';

    var API_BASE = typeof freezerInventory !== 'undefined' ? freezerInventory.restUrl : '';
    var NONCE = typeof freezerInventory !== 'undefined' ? freezerInventory.nonce : '';

    function headers() {
        var h = { 'Content-Type': 'application/json' };
        if (NONCE) h['X-WP-Nonce'] = NONCE;
        return h;
    }

    var addItemForm = document.getElementById('addItemForm');
    var inventoryList = document.getElementById('inventoryList');
    var searchInput = document.getElementById('searchInput');
    var categoryFilter = document.getElementById('categoryFilter');
    var zoneFilter = document.getElementById('zoneFilter');
    var clearFiltersBtn = document.getElementById('clearFilters');
    var inventoryStats = document.getElementById('inventoryStats');
    var downloadPdfBtn = document.getElementById('downloadPdfBtn');

    var allItems = [];
    var filteredItems = [];

    function setupEventListeners() {
        if (addItemForm) addItemForm.addEventListener('submit', handleAddItem);
        if (searchInput) searchInput.addEventListener('input', applyFilters);
        if (categoryFilter) categoryFilter.addEventListener('change', applyFilters);
        if (zoneFilter) zoneFilter.addEventListener('change', applyFilters);
        if (clearFiltersBtn) clearFiltersBtn.addEventListener('click', clearFilters);
        if (downloadPdfBtn) downloadPdfBtn.addEventListener('click', handleDownloadPdf);
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

    function useQuantity(item, useInput) {
        var amount = parseFloat(useInput.value);
        if (isNaN(amount) || amount <= 0) {
            showError('Enter a valid amount to use (greater than 0).');
            return;
        }
        var currentQty = item.quantity;
        if (amount > currentQty) {
            showError('Cannot use more than ' + currentQty + ' ' + item.unit + '.');
            return;
        }
        var newQty = Math.round((currentQty - amount) * 1000) / 1000;
        if (newQty <= 0) {
            fetch(API_BASE + '/items/' + encodeURIComponent(item.id), { method: 'DELETE', headers: headers() })
                .then(function(r) {
                    if (!r.ok) throw new Error('Failed to remove item');
                    allItems = allItems.filter(function(i) { return i.id !== item.id; });
                    filteredItems = filteredItems.filter(function(i) { return i.id !== item.id; });
                    updateFilters();
                    renderInventory();
                    updateStats();
                    showSuccess('Item used up and removed.');
                })
                .catch(function(err) { showError(err.message); });
            return;
        }
        fetch(API_BASE + '/items/' + encodeURIComponent(item.id), {
            method: 'PUT',
            headers: headers(),
            body: JSON.stringify({ quantity: newQty })
        })
            .then(function(r) { return r.json().then(function(j) { return { ok: r.ok, json: j }; }); })
            .then(function(res) {
                if (!res.ok) throw new Error(res.json.error || 'Failed to update');
                var idx = allItems.findIndex(function(i) { return i.id === item.id; });
                if (idx !== -1) allItems[idx] = res.json;
                var fidx = filteredItems.findIndex(function(i) { return i.id === item.id; });
                if (fidx !== -1) filteredItems[fidx] = res.json;
                useInput.value = '';
                renderInventory();
                updateStats();
                showSuccess('Quantity updated to ' + newQty + ' ' + item.unit + '.');
            })
            .catch(function(err) { showError(err.message); });
    }

    function deleteItem(itemId) {
        if (!confirm('Are you sure you want to remove this item from your freezer?')) return;
        fetch(API_BASE + '/items/' + encodeURIComponent(itemId), { method: 'DELETE', headers: headers() })
            .then(function(r) {
                if (!r.ok) throw new Error('Failed to delete item');
                allItems = allItems.filter(function(i) { return i.id !== itemId; });
                filteredItems = filteredItems.filter(function(i) { return i.id !== itemId; });
                updateFilters();
                renderInventory();
                updateStats();
                showSuccess('Item removed successfully!');
            })
            .catch(function(err) { showError(err.message); });
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

    function createItemCard(item) {
        var dateStr = item.date_added ? new Date(item.date_added).toLocaleDateString('en-US', { year: 'numeric', month: 'short', day: 'numeric' }) : '';
        var notesHtml = item.notes ? '<div class="item-notes">' + escapeHtml(item.notes) + '</div>' : '';
        return '<div class="item-card" data-item-id="' + escapeHtml(item.id) + '">' +
            '<h3>' + escapeHtml(item.name) + '</h3>' +
            '<span class="category-badge category-badge-' + escapeHtml(item.category).replace(/\s+/g, '-') + '">' + escapeHtml(item.category) + '</span>' +
            '<div class="item-details">' +
            '<div class="item-detail"><strong>Quantity:</strong> <span class="item-quantity-display">' + item.quantity + ' ' + escapeHtml(item.unit) + '</span></div>' +
            '<div class="item-detail"><strong>Location:</strong> <span>' + escapeHtml(item.freezer_zone) + '</span></div>' +
            notesHtml +
            '</div>' +
            '<div class="item-use-quantity">' +
            '<label for="use-qty-' + escapeHtml(item.id) + '">Use:</label>' +
            '<input type="number" id="use-qty-' + escapeHtml(item.id) + '" class="use-qty-input" min="0" step="0.1" placeholder="0">' +
            '<span class="use-qty-unit">' + escapeHtml(item.unit) + '</span>' +
            '<button type="button" class="btn btn-use" data-item-id="' + escapeHtml(item.id) + '">Use</button>' +
            '</div>' +
            '<div class="item-date">Added: ' + dateStr + '</div>' +
            '<div class="item-actions"><button type="button" class="btn btn-danger" data-item-id="' + escapeHtml(item.id) + '">Remove</button></div>' +
            '</div>';
    }

    function renderInventory() {
        if (!inventoryList) return;
        if (filteredItems.length === 0) {
            inventoryList.innerHTML = '<p class="empty-message">No items found. ' + (allItems.length === 0 ? 'Add your first item above!' : 'Try adjusting your filters.') + '</p>';
            return;
        }
        inventoryList.innerHTML = filteredItems.map(createItemCard).join('');
        filteredItems.forEach(function(item) {
            var card = inventoryList.querySelector('.item-card[data-item-id="' + item.id + '"]');
            if (!card) return;
            var delBtn = card.querySelector('.btn-danger[data-item-id]');
            if (delBtn) delBtn.addEventListener('click', function() { deleteItem(item.id); });
            var useBtn = card.querySelector('.btn-use[data-item-id]');
            var useInput = card.querySelector('.use-qty-input');
            if (useBtn && useInput) useBtn.addEventListener('click', function() { useQuantity(item, useInput); });
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
        var catSummary = Object.keys(catCounts).map(function(c) { return c + ': ' + catCounts[c]; }).join(' • ');
        inventoryStats.innerHTML = '<strong>Showing ' + filteredItems.length + ' of ' + allItems.length + ' items</strong>' + (catSummary ? ' • ' + catSummary : '');
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
