(function() {
    'use strict';

    var API_BASE = typeof freezerInventory !== 'undefined' ? freezerInventory.restUrl : '';
    var NONCE = typeof freezerInventory !== 'undefined' ? freezerInventory.nonce : '';

    function headers() {
        var h = { 'Content-Type': 'application/json' };
        if (NONCE) h['X-WP-Nonce'] = NONCE;
        return h;
    }

    var addForm = document.getElementById('addFreezerForm');
    var tbody = document.getElementById('freezersBody');
    var allFreezers = [];

    function setup() {
        if (addForm) addForm.addEventListener('submit', handleAdd);
        loadFreezers();
    }

    function loadFreezers() {
        fetch(API_BASE + '/freezers', { headers: headers() })
            .then(function(r) { return r.ok ? r.json() : Promise.reject(new Error('Failed to load')); })
            .then(function(data) {
                allFreezers = data;
                render();
            })
            .catch(function(err) { showMsg('error', err.message); });
    }

    function handleAdd(e) {
        e.preventDefault();
        var fd = new FormData(addForm);
        var data = { name: fd.get('name') };
        fetch(API_BASE + '/freezers', { method: 'POST', headers: headers(), body: JSON.stringify(data) })
            .then(function(r) { return r.json().then(function(j) { return { ok: r.ok, json: j }; }); })
            .then(function(res) {
                if (!res.ok) throw new Error(res.json.error || 'Failed');
                addForm.reset();
                loadFreezers();
                showMsg('success', 'Freezer added.');
            })
            .catch(function(err) { showMsg('error', err.message); });
    }

    function deleteFreezer(id) {
        if (!confirm('Delete this freezer?')) return;
        fetch(API_BASE + '/freezers/' + id, { method: 'DELETE', headers: headers() })
            .then(function(r) { return r.json().then(function(j) { return { ok: r.ok, json: j }; }); })
            .then(function(res) {
                if (!res.ok) throw new Error(res.json.error || 'Failed');
                loadFreezers();
                showMsg('success', 'Freezer deleted.');
            })
            .catch(function(err) { showMsg('error', err.message); });
    }

    function render() {
        if (!tbody) return;
        tbody.innerHTML = '';
        if (allFreezers.length === 0) {
            var tr = document.createElement('tr');
            var td = document.createElement('td');
            td.colSpan = 3;
            td.className = 'empty-message';
            td.textContent = 'No freezers defined.';
            tr.appendChild(td);
            tbody.appendChild(tr);
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
