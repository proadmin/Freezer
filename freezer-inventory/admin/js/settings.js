(function() {
    'use strict';

    var API_BASE = typeof freezerInventory !== 'undefined' ? freezerInventory.restUrl : '';
    var NONCE = typeof freezerInventory !== 'undefined' ? freezerInventory.nonce : '';
    var FREEZERS = typeof freezerInventory !== 'undefined' ? freezerInventory.freezers : [];

    function headers() {
        var h = { 'Content-Type': 'application/json' };
        if (NONCE) h['X-WP-Nonce'] = NONCE;
        return h;
    }

    // --- DOM refs ---

    var addFreezerForm = document.getElementById('addFreezerForm');
    var freezersBody = document.getElementById('freezersBody');

    var addLocationForm = document.getElementById('addLocationForm');
    var locFreezer = document.getElementById('locFreezer');
    var locationsBody = document.getElementById('locationsBody');

    var addCategoryForm = document.getElementById('addCategoryForm');
    var categoriesBody = document.getElementById('categoriesBody');

    var allFreezers = [];
    var allLocations = [];
    var allCategories = [];
    var editingCell = null;

    function setup() {
        if (addFreezerForm) addFreezerForm.addEventListener('submit', handleAddFreezer);
        if (addLocationForm) addLocationForm.addEventListener('submit', handleAddLocation);
        if (addCategoryForm) addCategoryForm.addEventListener('submit', handleAddCategory);

        loadFreezers();
        loadLocations();
        loadCategories();
    }

    // ============================
    // Freezers
    // ============================

    function loadFreezers() {
        fetch(API_BASE + '/freezers', { headers: headers() })
            .then(function(r) { return r.ok ? r.json() : Promise.reject(new Error('Failed to load')); })
            .then(function(data) {
                allFreezers = data;
                FREEZERS = data;
                renderFreezers();
                populateFreezerSelect();
            })
            .catch(function(err) { showMsg('freezers-section', 'error', err.message); });
    }

    function handleAddFreezer(e) {
        e.preventDefault();
        var fd = new FormData(addFreezerForm);
        var data = { name: fd.get('name') };
        fetch(API_BASE + '/freezers', { method: 'POST', headers: headers(), body: JSON.stringify(data) })
            .then(function(r) { return r.json().then(function(j) { return { ok: r.ok, json: j }; }); })
            .then(function(res) {
                if (!res.ok) throw new Error(res.json.error || 'Failed');
                addFreezerForm.reset();
                loadFreezers();
                showMsg('freezers-section', 'success', 'Freezer added.');
            })
            .catch(function(err) { showMsg('freezers-section', 'error', err.message); });
    }

    function deleteFreezer(id) {
        if (!confirm('Delete this freezer?')) return;
        fetch(API_BASE + '/freezers/' + id, { method: 'DELETE', headers: headers() })
            .then(function(r) { return r.json().then(function(j) { return { ok: r.ok, json: j }; }); })
            .then(function(res) {
                if (!res.ok) throw new Error(res.json.error || 'Failed');
                loadFreezers();
                showMsg('freezers-section', 'success', 'Freezer deleted.');
            })
            .catch(function(err) { showMsg('freezers-section', 'error', err.message); });
    }

    function renderFreezers() {
        if (!freezersBody) return;
        freezersBody.innerHTML = '';
        if (allFreezers.length === 0) {
            var tr = document.createElement('tr');
            var td = document.createElement('td');
            td.colSpan = 3;
            td.className = 'empty-message';
            td.textContent = 'No freezers defined.';
            tr.appendChild(td);
            freezersBody.appendChild(tr);
            return;
        }
        allFreezers.forEach(function(f) {
            var tr = document.createElement('tr');

            var tdName = document.createElement('td');
            tdName.textContent = f.name;
            tr.appendChild(tdName);

            var tdCount = document.createElement('td');
            tdCount.textContent = f.location_count || 0;
            tdCount.className = 'cell-date';
            tr.appendChild(tdCount);

            var tdAct = document.createElement('td');
            tdAct.className = 'cell-actions';
            var btn = document.createElement('button');
            btn.type = 'button';
            btn.className = 'btn btn-danger btn-sm';
            btn.textContent = 'Delete';
            btn.addEventListener('click', function() { deleteFreezer(f.id); });
            tdAct.appendChild(btn);
            tr.appendChild(tdAct);

            freezersBody.appendChild(tr);
        });
    }

    // ============================
    // Locations
    // ============================

    function populateFreezerSelect() {
        if (!locFreezer) return;
        locFreezer.innerHTML = '<option value="">Select freezer</option>';
        FREEZERS.forEach(function(f) {
            var opt = document.createElement('option');
            opt.value = f.name;
            opt.textContent = f.name;
            locFreezer.appendChild(opt);
        });
    }

    function loadLocations() {
        fetch(API_BASE + '/locations', { headers: headers() })
            .then(function(r) { return r.ok ? r.json() : Promise.reject(new Error('Failed to load')); })
            .then(function(data) {
                allLocations = data;
                renderLocations();
            })
            .catch(function(err) { showMsg('locations-section', 'error', err.message); });
    }

    function handleAddLocation(e) {
        e.preventDefault();
        var fd = new FormData(addLocationForm);
        var data = { freezer: fd.get('freezer'), shelf: fd.get('shelf'), bin: fd.get('bin') };
        fetch(API_BASE + '/locations', { method: 'POST', headers: headers(), body: JSON.stringify(data) })
            .then(function(r) { return r.json().then(function(j) { return { ok: r.ok, json: j }; }); })
            .then(function(res) {
                if (!res.ok) throw new Error(res.json.error || 'Failed');
                addLocationForm.reset();
                loadLocations();
                showMsg('locations-section', 'success', 'Location added.');
            })
            .catch(function(err) { showMsg('locations-section', 'error', err.message); });
    }

    function saveLocField(locId, field, value) {
        var data = {};
        data[field] = value;
        return fetch(API_BASE + '/locations/' + locId, { method: 'PUT', headers: headers(), body: JSON.stringify(data) })
            .then(function(r) { return r.json().then(function(j) { return { ok: r.ok, json: j }; }); })
            .then(function(res) {
                if (!res.ok) throw new Error(res.json.error || 'Failed');
                loadLocations();
            })
            .catch(function(err) { showMsg('locations-section', 'error', err.message); loadLocations(); });
    }

    function deleteLocation(id) {
        if (!confirm('Delete this location?')) return;
        fetch(API_BASE + '/locations/' + id, { method: 'DELETE', headers: headers() })
            .then(function(r) { return r.json().then(function(j) { return { ok: r.ok, json: j }; }); })
            .then(function(res) {
                if (!res.ok) throw new Error(res.json.error || 'Failed');
                loadLocations();
                showMsg('locations-section', 'success', 'Location deleted.');
            })
            .catch(function(err) { showMsg('locations-section', 'error', err.message); });
    }

    // Inline editing for locations
    function makeEditable(td, obj, field, type) {
        td.classList.add('editable-cell');
        td.addEventListener('click', function() { startEdit(td, obj, field, type); });
    }

    function startEdit(td, obj, field, type) {
        if (td.querySelector('input, select')) return;
        if (editingCell && editingCell !== td) commitEdit(editingCell);
        editingCell = td;
        var val = obj[field] || '';
        td.classList.add('editing');
        td.innerHTML = '';

        var input;
        if (type === 'select-freezer') {
            input = document.createElement('select');
            input.className = 'cell-editor';
            FREEZERS.forEach(function(f) {
                var opt = document.createElement('option');
                opt.value = f.name;
                opt.textContent = f.name;
                if (f.name === val) opt.selected = true;
                input.appendChild(opt);
            });
            input.addEventListener('change', function() { commitEdit(td); });
        } else {
            input = document.createElement('input');
            input.type = 'text';
            input.className = 'cell-editor';
            input.value = val;
        }

        input.dataset.locId = obj.id;
        input.dataset.field = field;
        input.dataset.originalValue = val;
        td.appendChild(input);
        input.focus();
        if (input.select) input.select();

        input.addEventListener('keydown', function(e) {
            if (e.key === 'Enter') { e.preventDefault(); commitEdit(td); }
            else if (e.key === 'Escape') { cancelEdit(td, val); }
        });
        input.addEventListener('blur', function() {
            setTimeout(function() { if (editingCell === td) commitEdit(td); }, 100);
        });
    }

    function commitEdit(td) {
        var input = td.querySelector('input, select');
        if (!input) return;
        var locId = input.dataset.locId;
        var field = input.dataset.field;
        var orig = input.dataset.originalValue;
        var val = input.value;
        td.classList.remove('editing');
        editingCell = null;
        td.textContent = val;
        if (val !== orig) saveLocField(locId, field, val);
    }

    function cancelEdit(td, val) {
        td.classList.remove('editing');
        editingCell = null;
        td.textContent = val;
    }

    function renderLocations() {
        if (!locationsBody) return;
        editingCell = null;
        locationsBody.innerHTML = '';
        if (allLocations.length === 0) {
            var tr = document.createElement('tr');
            var td = document.createElement('td');
            td.colSpan = 5;
            td.className = 'empty-message';
            td.textContent = 'No locations defined.';
            tr.appendChild(td);
            locationsBody.appendChild(tr);
            return;
        }
        allLocations.forEach(function(loc) {
            var tr = document.createElement('tr');

            var tdF = document.createElement('td');
            tdF.textContent = loc.freezer;
            makeEditable(tdF, loc, 'freezer', 'select-freezer');
            tr.appendChild(tdF);

            var tdS = document.createElement('td');
            tdS.textContent = loc.shelf;
            makeEditable(tdS, loc, 'shelf', 'text');
            tr.appendChild(tdS);

            var tdB = document.createElement('td');
            tdB.textContent = loc.bin;
            makeEditable(tdB, loc, 'bin', 'text');
            tr.appendChild(tdB);

            var tdCount = document.createElement('td');
            tdCount.textContent = loc.item_count || 0;
            tdCount.className = 'cell-date';
            tr.appendChild(tdCount);

            var tdAct = document.createElement('td');
            tdAct.className = 'cell-actions';
            var btn = document.createElement('button');
            btn.type = 'button';
            btn.className = 'btn btn-danger btn-sm';
            btn.textContent = 'Delete';
            btn.addEventListener('click', function() { deleteLocation(loc.id); });
            tdAct.appendChild(btn);
            tr.appendChild(tdAct);

            locationsBody.appendChild(tr);
        });
    }

    // ============================
    // Categories
    // ============================

    function loadCategories() {
        fetch(API_BASE + '/categories', { headers: headers() })
            .then(function(r) { return r.ok ? r.json() : Promise.reject(new Error('Failed to load')); })
            .then(function(data) {
                allCategories = data;
                renderCategories();
            })
            .catch(function(err) { showMsg('categories-section', 'error', err.message); });
    }

    function handleAddCategory(e) {
        e.preventDefault();
        var fd = new FormData(addCategoryForm);
        var data = { name: fd.get('name') };
        fetch(API_BASE + '/categories', { method: 'POST', headers: headers(), body: JSON.stringify(data) })
            .then(function(r) { return r.json().then(function(j) { return { ok: r.ok, json: j }; }); })
            .then(function(res) {
                if (!res.ok) throw new Error(res.json.error || 'Failed');
                addCategoryForm.reset();
                loadCategories();
                showMsg('categories-section', 'success', 'Category added.');
            })
            .catch(function(err) { showMsg('categories-section', 'error', err.message); });
    }

    function deleteCategory(id) {
        if (!confirm('Delete this category?')) return;
        fetch(API_BASE + '/categories/' + id, { method: 'DELETE', headers: headers() })
            .then(function(r) { return r.json().then(function(j) { return { ok: r.ok, json: j }; }); })
            .then(function(res) {
                if (!res.ok) throw new Error(res.json.error || 'Failed');
                loadCategories();
                showMsg('categories-section', 'success', 'Category deleted.');
            })
            .catch(function(err) { showMsg('categories-section', 'error', err.message); });
    }

    function renderCategories() {
        if (!categoriesBody) return;
        categoriesBody.innerHTML = '';
        if (allCategories.length === 0) {
            var tr = document.createElement('tr');
            var td = document.createElement('td');
            td.colSpan = 3;
            td.className = 'empty-message';
            td.textContent = 'No categories defined.';
            tr.appendChild(td);
            categoriesBody.appendChild(tr);
            return;
        }
        allCategories.forEach(function(c) {
            var tr = document.createElement('tr');

            var tdName = document.createElement('td');
            tdName.textContent = c.name;
            tr.appendChild(tdName);

            var tdCount = document.createElement('td');
            tdCount.textContent = c.item_count || 0;
            tdCount.className = 'cell-date';
            tr.appendChild(tdCount);

            var tdAct = document.createElement('td');
            tdAct.className = 'cell-actions';
            var btn = document.createElement('button');
            btn.type = 'button';
            btn.className = 'btn btn-danger btn-sm';
            btn.textContent = 'Delete';
            btn.addEventListener('click', function() { deleteCategory(c.id); });
            tdAct.appendChild(btn);
            tr.appendChild(tdAct);

            categoriesBody.appendChild(tr);
        });
    }

    // ============================
    // Shared helpers
    // ============================

    function showMsg(sectionId, type, message) {
        var section = document.getElementById(sectionId);
        if (!section) return;
        var div = document.createElement('div');
        div.className = type === 'error' ? 'error-message' : 'success-message';
        div.textContent = message;
        section.insertBefore(div, section.firstChild);
        setTimeout(function() { div.remove(); }, 4000);
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', setup);
    } else {
        setup();
    }
})();
