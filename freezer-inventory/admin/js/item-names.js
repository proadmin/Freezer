(function() {
    'use strict';

    var API_BASE = typeof freezerInventory !== 'undefined' ? freezerInventory.restUrl : '';
    var NONCE = typeof freezerInventory !== 'undefined' ? freezerInventory.nonce : '';

    function headers() {
        var h = { 'Content-Type': 'application/json' };
        if (NONCE) h['X-WP-Nonce'] = NONCE;
        return h;
    }

    var addForm = document.getElementById('addItemNameForm');
    var tbody = document.getElementById('itemNamesBody');
    var allNames = [];

    function setup() {
        if (addForm) addForm.addEventListener('submit', handleAdd);
        loadNames();
    }

    function loadNames() {
        fetch(API_BASE + '/item-names', { headers: headers() })
            .then(function(r) { return r.ok ? r.json() : Promise.reject(new Error('Failed to load')); })
            .then(function(data) {
                allNames = data;
                render();
            })
            .catch(function(err) { showMsg('error', err.message); });
    }

    function handleAdd(e) {
        e.preventDefault();
        var fd = new FormData(addForm);
        var data = { name: fd.get('name') };
        fetch(API_BASE + '/item-names', { method: 'POST', headers: headers(), body: JSON.stringify(data) })
            .then(function(r) { return r.json().then(function(j) { return { ok: r.ok, json: j }; }); })
            .then(function(res) {
                if (!res.ok) throw new Error(res.json.error || 'Failed');
                addForm.reset();
                loadNames();
                showMsg('success', 'Item name added.');
            })
            .catch(function(err) { showMsg('error', err.message); });
    }

    function deleteName(id) {
        if (!confirm('Delete this item name?')) return;
        fetch(API_BASE + '/item-names/' + id, { method: 'DELETE', headers: headers() })
            .then(function(r) { return r.json().then(function(j) { return { ok: r.ok, json: j }; }); })
            .then(function(res) {
                if (!res.ok) throw new Error(res.json.error || 'Failed');
                loadNames();
                showMsg('success', 'Item name deleted.');
            })
            .catch(function(err) { showMsg('error', err.message); });
    }

    function render() {
        if (!tbody) return;
        tbody.innerHTML = '';
        if (allNames.length === 0) {
            var tr = document.createElement('tr');
            var td = document.createElement('td');
            td.colSpan = 3;
            td.className = 'empty-message';
            td.textContent = 'No item names defined.';
            tr.appendChild(td);
            tbody.appendChild(tr);
            return;
        }
        allNames.forEach(function(n) {
            var tr = document.createElement('tr');

            var tdName = document.createElement('td');
            tdName.textContent = n.name;
            tr.appendChild(tdName);

            var tdCount = document.createElement('td');
            tdCount.textContent = n.item_count || 0;
            tdCount.className = 'cell-date';
            tr.appendChild(tdCount);

            var tdAct = document.createElement('td');
            tdAct.className = 'cell-actions';
            var btn = document.createElement('button');
            btn.type = 'button';
            btn.className = 'btn btn-danger btn-sm';
            btn.textContent = 'Delete';
            btn.addEventListener('click', function() { deleteName(n.id); });
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
