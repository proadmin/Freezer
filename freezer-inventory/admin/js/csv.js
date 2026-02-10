(function() {
    'use strict';

    var API_BASE = typeof freezerInventory !== 'undefined' ? freezerInventory.restUrl : '';
    var NONCE = typeof freezerInventory !== 'undefined' ? freezerInventory.nonce : '';

    function headers() {
        var h = { 'Content-Type': 'application/json' };
        if (NONCE) h['X-WP-Nonce'] = NONCE;
        return h;
    }

    var downloadCsvBtn = document.getElementById('downloadCsvBtn');
    var csvFileInput = document.getElementById('csvFileInput');
    var csvFileName = document.getElementById('csvFileName');

    function setup() {
        if (downloadCsvBtn) downloadCsvBtn.addEventListener('click', handleDownloadCsv);
        if (csvFileInput) csvFileInput.addEventListener('change', handleCsvImport);
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
            .catch(function() { showMsg('error', 'Failed to export CSV.'); });
    }

    function handleCsvImport() {
        var file = csvFileInput.files[0];
        if (!file) return;
        if (csvFileName) csvFileName.textContent = file.name;
        if (!confirm('WARNING: Importing a CSV will replace ALL current inventory items. This cannot be undone.\n\nAre you sure you want to continue?')) {
            csvFileInput.value = '';
            if (csvFileName) csvFileName.textContent = '';
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
                if (csvFileName) csvFileName.textContent = '';
                if (!res.ok) throw new Error(res.json.error || 'Import failed');
                showMsg('success', 'Imported ' + res.json.imported + ' items.');
            })
            .catch(function(err) {
                csvFileInput.value = '';
                if (csvFileName) csvFileName.textContent = '';
                showMsg('error', 'CSV import failed: ' + err.message);
            });
    }

    function showMsg(type, message) {
        var section = document.querySelector('.form-section');
        if (!section) return;
        var div = document.createElement('div');
        div.className = type === 'error' ? 'error-message' : 'success-message';
        div.textContent = message;
        section.insertBefore(div, section.firstChild);
        setTimeout(function() { div.remove(); }, 5000);
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', setup);
    } else {
        setup();
    }
})();
