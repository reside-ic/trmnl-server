function showToast(message, type = 'success') {
  const toastEl = document.getElementById('saveToast');
  const body = toastEl.querySelector('.toast-body');
  body.textContent = message;
  toastEl.className = `toast align-items-center text-bg-${type} border-0`;
  const toast = new bootstrap.Toast(toastEl);
  toast.show();
}

function populateList(containerId, data, className, def = false) {
  const container = document.getElementById(containerId);
  container.innerHTML = '';
  data.forEach((item, index) => {
    const div = document.createElement('div');
    div.className = 'form-check';
    div.innerHTML = `
      <input class="form-check-input ${className}" type="checkbox" value="${item}" id="${className}-${index}" ${def ? 'checked' : ''}>
      <label class="form-check-label" for="${className}-${index}">${item}</label>
    `;
    container.appendChild(div);
  });
}

function updatePreviewForNotice() {
  const checkbox = event.target;
  const noticeName = encodeURIComponent(checkbox.value);
  const previewImage = document.getElementById('previewImage');

  fetch(`helpers/preview.php?file=${noticeName}`)
      .then(res => {
    return res.blob();
  })
  .then(blob => {
    const reader = new FileReader();
    reader.onloadend = function() {
      const imgEl = document.getElementById("previewImage");
      imgEl.src = reader.result;
    };
    reader.readAsDataURL(blob);
  })
}

function loadRowIntoEditor(tr) {
  const cells = tr.children;
  const from = cells[0].textContent;
  const to = cells[1].textContent;
  const notices = cells[2].textContent.split(',').map(s => s.trim());
  const devices = cells[3].textContent.split(',').map(s => s.trim());
  document.getElementById('fromDate').value = from;
  document.getElementById('toDate').value = to;
  document.querySelectorAll('.notice, .device').forEach(cb => cb.checked = false);
  document.querySelectorAll('.notice').forEach(cb => {
    if (notices.includes(cb.value)) cb.checked = true;
  });
  document.querySelectorAll('.device').forEach(cb => {
    if (devices.includes(cb.value)) cb.checked = true;
  });
}

function overwriteRow(tr) {
  const from = document.getElementById('fromDate').value;
  const to = document.getElementById('toDate').value;
  const notices = Array.from(document.querySelectorAll('.notice:checked')).map(n => n.value);
  const devices = Array.from(document.querySelectorAll('.device:checked')).map(d => d.value);

  if (!from || !to || notices.length === 0 || devices.length === 0) {
    showToast("Please complete date/times, notice(s) and device(s)", "warning");
    return;
  }

  tr.children[0].textContent = from;
  tr.children[1].textContent = to;
  tr.children[2].textContent = notices.join(', ');
  tr.children[3].textContent = devices.join(', ');
}

function getTableData() {
  const rows = [];
  document.querySelectorAll('#scheduleTable tbody tr').forEach(tr => {
    const cells = tr.children;

    rows.push({
      from: cells[0].textContent,
      to: cells[1].textContent,
      notices: cells[2].textContent.split(',').map(s => s.trim()),
      devices: cells[3].textContent.split(',').map(s => s.trim())
    });
  });
  return rows;
}

function saveConfig() {
  const data = getTableData();
  fetch('helpers/saveSchedule.php', {
    method: 'POST',
    headers: {
      'Content-Type': 'application/json'
    },
    body: JSON.stringify(data)
  })
  .then(res => res.text())
  .then(msg => {
    showToast("Saved!", "success");
    console.log(msg);
  })
  .catch(err => console.error(err));
}

function loadConfig() {
  fetch('helpers/loadSchedule.php')
    .then(res => res.json())
    .then(data => {
      const tbody = document.querySelector('#scheduleTable tbody');
      tbody.innerHTML = '';
      data.forEach(row => {
        addRowToTable(row);
      });
    })
    .catch(err => console.error(err));
}

function addRowToTable(row) {
  const tbody = document.querySelector('#scheduleTable tbody');
  const tr = document.createElement('tr');

  tr.innerHTML = `
    <td>${row.from}</td>
    <td>${row.to}</td>
    <td>${row.notices.join(', ')}</td>
    <td>${row.devices.join(', ')}</td>
    <td>
      <button class="btn btn-sm btn-danger removeBtn"><i class="bi bi-trash"></i></button>
      <button class="btn btn-sm btn-secondary editBtn"><i class="bi bi-pencil"></i></button>
      <button class="btn btn-sm btn-success overwriteBtn"><i class="bi bi-save"></i></button>
    </td>
  `;

  tbody.appendChild(tr);

  // Reattach handlers
  tr.querySelector('.removeBtn').onclick = () => tr.remove();
  tr.querySelector('.editBtn').onclick = () => loadRowIntoEditor(tr);
  tr.querySelector('.overwriteBtn').onclick = () => overwriteRow(tr);
}
function revertToSaved() {
  if (confirm("Revert to last saved schedule?\n\nAll unsaved changes will be lost.")) {
    loadConfig();
  }
}

function setDefaultDateTimes() {
  const now = new Date();
  const today = now.toISOString().split('T')[0]; // YYYY-MM-DD

  document.getElementById('fromDate').value = `${today}T00:00`;
  document.getElementById('toDate').value = `${today}T23:59`;
}


document.addEventListener("DOMContentLoaded", function() {
  populateList("noticesList", JSON.parse(document.getElementById('notices-data').textContent), 'notice');
  populateList("devicesList", JSON.parse(document.getElementById('devices-data').textContent), 'device', true);
  loadConfig();
  setDefaultDateTimes();

  document.getElementById('saveAllBtn').addEventListener('click', saveConfig);
  document.getElementById('revertBtn').addEventListener('click', revertToSaved);

  document.querySelectorAll('.notice').forEach(cb => {
    cb.addEventListener('change', updatePreviewForNotice);
  });

  document.getElementById('addBtn').addEventListener('click', function() {
    const from = document.getElementById('fromDate').value;
    const to = document.getElementById('toDate').value;
    const fromDate = new Date(from);
    const toDate   = new Date(to);
    if (fromDate >= toDate) {
      alert("'From' must be before 'To'");
      return;
    }

    const notices = Array.from(document.querySelectorAll('.notice:checked')).map(n => n.value);
    const devices = Array.from(document.querySelectorAll('.device:checked')).map(d => d.value);

    if (notices.length === 0 || devices.length === 0) {
      showToast("Please select at least one notice and one device", "warning");
      return;
    }
    addRowToTable({ from, to, notices, devices });
    setDefaultDateTimes();
    document.querySelectorAll('.notice').forEach(cb => cb.checked = false);
    document.querySelectorAll('.device').forEach(cb => cb.checked = true);
  });
});