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

    var addForm = document.getElementById('addLocationForm');
    var locFreezer = document.getElementById('locFreezer');
    var tbody = document.getElementById('locationsBody');
    var allLocations = [];
    var editingCell = null;

    function setup() {
        if (addForm) addForm.addEventListener('submit', handleAdd);
        populateFreezerSelect();
        loadLocations();
    }

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
                render();
            })
            .catch(function(err) { showMsg('error', err.message); });
    }

    function handleAdd(e) {
        e.preventDefault();
        var fd = new FormData(addForm);
        var data = { freezer: fd.get('freezer'), shelf: fd.get('shelf'), bin: fd.get('bin') };
        fetch(API_BASE + '/locations', { method: 'POST', headers: headers(), body: JSON.stringify(data) })
            .then(function(r) { return r.json().then(function(j) { return { ok: r.ok, json: j }; }); })
            .then(function(res) {
                if (!res.ok) throw new Error(res.json.error || 'Failed');
                addForm.reset();
                loadLocations();
                showMsg('success', 'Location added.');
            })
            .catch(function(err) { showMsg('error', err.message); });
    }

    function saveField(locId, field, value) {
        var data = {};
        data[field] = value;
        return fetch(API_BASE + '/locations/' + locId, { method: 'PUT', headers: headers(), body: JSON.stringify(data) })
            .then(function(r) { return r.json().then(function(j) { return { ok: r.ok, json: j }; }); })
            .then(function(res) {
                if (!res.ok) throw new Error(res.json.error || 'Failed');
                loadLocations();
            })
            .catch(function(err) { showMsg('error', err.message); loadLocations(); });
    }

    function deleteLoc(id) {
        if (!confirm('Delete this location?')) return;
        fetch(API_BASE + '/locations/' + id, { method: 'DELETE', headers: headers() })
            .then(function(r) { return r.json().then(function(j) { return { ok: r.ok, json: j }; }); })
            .then(function(res) {
                if (!res.ok) throw new Error(res.json.error || 'Failed');
                loadLocations();
                showMsg('success', 'Location deleted.');
            })
            .catch(function(err) { showMsg('error', err.message); });
    }

    // Inline editing
    function makeEditable(td, loc, field, type) {
        td.classList.add('editable-cell');
        td.addEventListener('click', function() { startEdit(td, loc, field, type); });
    }

    function startEdit(td, loc, field, type) {
        if (td.querySelector('input, select')) return;
        if (editingCell && editingCell !== td) commitEdit(editingCell);
        editingCell = td;
        var val = loc[field] || '';
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

        input.dataset.locId = loc.id;
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
        if (val !== orig) saveField(locId, field, val);
    }

    function cancelEdit(td, val) {
        td.classList.remove('editing');
        editingCell = null;
        td.textContent = val;
    }

    function render() {
        if (!tbody) return;
        editingCell = null;
        tbody.innerHTML = '';
        if (allLocations.length === 0) {
            var tr = document.createElement('tr');
            var td = document.createElement('td');
            td.colSpan = 5;
            td.className = 'empty-message';
            td.textContent = 'No locations defined.';
            tr.appendChild(td);
            tbody.appendChild(tr);
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
            btn.addEventListener('click', function() { deleteLoc(loc.id); });
            tdAct.appendChild(btn);
            tr.appendChild(tdAct);

            tbody.appendChild(tr);
        });
    }

    function showMsg(type, message) {
        var section = document.querySelector('.form-section');
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
