var maxRow = 0;

const FONT_LIST = JSON.parse(
  document.getElementById("font-data").textContent
);

const IMAGE_LIST = JSON.parse(
  document.getElementById("backgrounds-data").textContent
);

function getFontOptions(selected = "") {
  return FONT_LIST.map(font =>
    `<option value="${font}" ${font === selected ? "selected" : ""}>${font}</option>`
  ).join("");
}

function updateRowButtons() {
  const rows = document.querySelectorAll("#editableTable tbody tr");

  rows.forEach((row, index) => {
    const upBtn = row.querySelector(".bi-chevron-up").closest("button");
    const downBtn = row.querySelector(".bi-chevron-down").closest("button");
    upBtn.disabled = (index === 0);
    downBtn.disabled = (index === rows.length - 1);
  });
}

function addRow(data = {}) {
  const tbody = document.querySelector("#editableTable tbody");
  const row = document.createElement("tr");
  const typ = data.typ ?? "t";
  const x = data.x ?? 100;
  const y = data.y ?? 100;
  const j = data.j ?? "l";
  const s = data.s ?? 12;
  const t = data.t ?? "";
  const c = data.c ?? "";
  const img = data.i ?? "";
  maxRow++;
  const rowNo = maxRow;

  // Justification group. Left, center, right.
  const jg = "j-"+rowNo;
  const idl = "lj-" + rowNo;
  const idc = "cj-" + rowNo;
  const idr = "rj-" + rowNo;
  
  // Type group. Text, QR, Image
  const tg = "t-"+rowNo;
  const idt = "tj-" + rowNo;
  const idq = "qj-" + rowNo;
  const idi = "ij-" + rowNo;

  const dis1 = (rowNo == 1) ? "disabled" : "";
  const dis2 = "";

  row.innerHTML = `
    <td>
      <div class="btn-group" role="group">
        <input type="radio" class="btn-check" name="${tg}" id="${idt}" value="t" ${typ === "t" ? "checked" : ""}>
        <label class="btn btn-outline-secondary" for="${idt}" ><i class="bi bi-type"></i></label>
        <input type="radio" class="btn-check" name="${tg}" id="${idq}" value="q" ${typ === "q" ? "checked" : ""}>
        <label class="btn btn-outline-secondary" for="${idq}" ><i class="bi bi-qr-code"></i></label>
        <input type="radio" class="btn-check" name="${tg}" id="${idi}" value="i" ${typ === "i" ? "checked" : ""}>
        <label class="btn btn-outline-secondary" for="${idi}" ><i class="bi bi-image"></i></label>
      </div>
    </td>
    <td><input type="number" data-field="x" class="form-control" value="${x}"></td>
    <td><input type="number" data-field="y" class="form-control" value="${y}"></td>
    <td>
      <div class="btn-group" role="group">
        <input type="radio" class="btn-check" name="${jg}" id="${idl}" value="l" ${j === "l" ? "checked" : ""}>
        <label class="btn btn-outline-secondary" for="${idl}" >L</label>
        <input type="radio" class="btn-check" name="${jg}" id="${idc}" value="c" ${j === "c" ? "checked" : ""}>
        <label class="btn btn-outline-secondary" for="${idc}" >C</label>
        <input type="radio" class="btn-check" name="${jg}" id="${idr}"value="r" ${j === "r" ? "checked" : ""}>
        <label class="btn btn-outline-secondary" for="${idr}" >R</label>
      </div>
    </td>
    <td>
      <select class="form-select">
        ${getFontOptions(data.f)}
      </select>
    </td>
    <td><input type="number" data-field="size" class="form-control" value="${s}"></td>
    <td class="d-flex align-items-center gap-2">
      <select class="form-select form-select-sm color-select">
        <option value="black" ${c==="black" ? "selected" : ""}>Black</option>
        <option value="dgrey" ${c==="dgrey" ? "selected" : ""}>Dark Grey</option>
        <option value="lgrey" ${c==="lgrey" ? "selected" : ""}>Light Grey</option>
        <option value="white" ${c==="white" ? "selected" : ""}>White</option>
      </select>
    </td>
    <td><input type="text" data-field="text" class="form-control" value="${t}"></td>
    <td>
      <button class="btn btn-outline-danger btn-sm action-delete"><i class="bi bi-trash-fill"></i></button>
      <button class="btn btn-outline-primary btn-sm action-up" ${dis1}><i class="bi bi-chevron-up"></i></button>
      <button class="btn btn-outline-primary btn-sm action-down" ${dis2}><i class="bi bi-chevron-down"></i></button>
      <input type="hidden" data-field="imgData" value="${img}">
    </td>
  `;
  tbody.appendChild(row);
  updateRowButtons();
}

function getTableData() {
  const rows = document.querySelectorAll("#editableTable tbody tr");

  return Array.from(rows).map(row => {
    const inputs = row.querySelectorAll("input");
    const select = row.querySelector("select");
    const color = row.querySelector(".color-select");

    const typeRadio = row.querySelector('input[type="radio"][name^="t-"]:checked');
    const xVal = parseInt(row.querySelector('[data-field="x"]').value);
    const yVal = parseInt(row.querySelector('[data-field="y"]').value);
    const sVal = parseInt(row.querySelector('[data-field="size"]').value);
    const tVal = row.querySelector('[data-field="text"]').value;
    const jRadio = row.querySelector('input[type="radio"][name^="j-"]:checked');
    const imgD = row.querySelector('input[data-field="imgData"]').value;

    return {
      typ : typeRadio ? typeRadio.value : "t",
      x: xVal,
      y: yVal,
      j: jRadio ? jRadio.value : "left",
      f: select ? select.value : "Arial",
      s: sVal,
      t: tVal,
      c: color ? color.value : "black",
      i: imgD
    };
  });
}

function updatePreview() {
  const data = getTableData();
  const bg = document.getElementById("backgroundImg").value;
  fetch("helpers/preview.php", {
    method: "POST",
    headers: { "Content-Type": "application/json" },
    body: JSON.stringify({ elements: data,
                           back: bg})
  })
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

function showToast(message, type = 'success') {
  const toastEl = document.getElementById('saveToast');
  const body = toastEl.querySelector('.toast-body');
  body.textContent = message;
  toastEl.className = `toast align-items-center text-bg-${type} border-0`;
  const toast = new bootstrap.Toast(toastEl);
  toast.show();
}

function maybeAutoPreview() {
  if (document.getElementById("autoPreview").checked) {
    debouncePreview();
  }
}

let previewTimeout;
let activeImageRow = null;

function debouncePreview() {
  clearTimeout(previewTimeout);
  previewTimeout = setTimeout(updatePreview, 300);
}


function populateDropdown(data, ddEl) {
  const dropdown = document.getElementById(ddEl);
  dropdown.innerHTML = '';
  data.forEach(name => {
    const opt = document.createElement('option');
    opt.value = name;
    opt.textContent = name;
    dropdown.appendChild(opt);
  });
}

function loadPage(data) {
  const tbody = document.querySelector('#editableTable tbody');
  tbody.innerHTML = '';
  data.forEach(row => addRow(row));
}

document.addEventListener("DOMContentLoaded", function() {
  const addRowBtn = document.getElementById("addRowBtn");
  if (addRowBtn) {
    addRowBtn.addEventListener("click", function(event) {
      event.preventDefault();
      addRow();
      updateRowButtons();
    });
  }

  document.querySelector("#editableTable tbody").addEventListener("click", function(e) {
    const row = e.target.closest("tr");
    if (!row) return;

    const btn = e.target.closest("button");
    if (btn == null) return;
    if (btn.classList.contains("action-delete")) {
      row.remove();
      updateRowButtons();
    } else if (btn.classList.contains("action-up") && row.previousElementSibling) {
      row.parentNode.insertBefore(row, row.previousElementSibling);
      updateRowButtons();
    } else if (btn.classList.contains("action-down") && row.nextElementSibling) {
      row.parentNode.insertBefore(row.nextElementSibling, row);
      updateRowButtons();
    }
  });
  
  const previewBtn = document.getElementById("previewBtn");
  previewBtn.addEventListener("click", (e) => {
    e.preventDefault();
    updatePreview();
  });



  const tbody = document.querySelector("#editableTable tbody");
  tbody.addEventListener("input", maybeAutoPreview);
  tbody.addEventListener("change", maybeAutoPreview);

  const autoPreview = document.getElementById("autoPreview");
  autoPreview.addEventListener("change", () => {
    if (autoPreview.checked) {
      previewBtn.disabled = true;
      updatePreview();
    } else {
      previewBtn.disabled = false;
    }
  });

  /************************************/
  /* Save template or notice to stash */
  /************************************/
  
  function saveThing(dropdown, nameInputId, thing, phpfile) {
    const name = document.getElementById(nameInputId).value.trim();
    if (!name) {
        showToast(`Please enter a ${thing} name`, "warning");
        return;
    }
    const rows = getTableData();
    fetch(`helpers/${phpfile}`, {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json'
      },
      body: JSON.stringify({
        name: name,
        back: document.getElementById('backgroundImg').value,
        elements: rows
      })
    })
    .then(res => {
      if (!res.ok) throw new Error(`HTTP error ${res.status}`);
      return res.json();
    })
    .then(data => {
      populateDropdown(data, dropdown);
      document.getElementById(dropdown).value = name;
      showToast(`Successfully saved ${thing} "${name}"`, "success");
    
    })
    .catch(err => {
      console.error("Error:", err);
      showToast(`Failed to save ${thing}. See console.`, "danger");
    });
  }


  document.getElementById('saveTemplateBtn').addEventListener('click', () => {
   saveThing("existingTemplates" ,"templateName", "template", "saveTemplate.php");
  });

  document.getElementById('saveNoticeBtn').addEventListener('click', () => {
   saveThing("existingNotices" ,"noticeName", "notice", "saveNotice.php");
  });

  /**************************************/
  /* Load template or notice from stash */
  /**************************************/

  function loadThing(dropdown, thing, phpfile) {
    const name = document.getElementById(dropdown).value;
    if (!name) {
        showToast(`Please select a ${thing} to load`, "danger");
        return;
    }
    if (!confirm(`Load ${thing} "${name}"?\nUnsaved changes will be lost.`)) return;
    fetch(`helpers/${phpfile}?name=${encodeURIComponent(name)}`)
      .then(res => res.json())
      .then(data => {
        loadPage(data.elements);
        document.getElementById(`${thing}Name`).value = name;
        debouncePreview();
        showToast(`Sucessfully loaded ${thing} "${name}"`);
    });
  }

  document.getElementById('loadTemplateBtn').addEventListener('click', () => {
    loadThing("existingTemplates", "template", "loadTemplate.php");
  });

  document.getElementById('loadNoticeBtn').addEventListener('click', () => {
    loadThing("existingNotices", "notice", "loadNotice.php");
  });

  /*****************************/
  /* Delete template or notice */
  /*****************************/

  function deleteThing(dropdown, thing, phpfile) {
    const name = document.getElementById(dropdown).value;
    if (!name) {
        showToast(`Please select a ${thing} to delete`, "danger");
        return;
    }
    if (!confirm(`Delete ${thing} "${name}"?\nThis cannot be undone.`)) return;

    fetch(`helpers/${phpfile}?name=${encodeURIComponent(name)}`, { method: 'POST' })
      .then(res => res.json())
      .then(data => {
        populateDropdown(data, dropdown)
        showToast(`${thing} ${name} deleted`, "success");
      })
      .catch(err => {
        console.error("Error:",    err);
        showToast(`Failed to delete ${thing} ${name}`, "danger");
    });
  }

  document.getElementById('deleteTemplateBtn').addEventListener('click', () => {
    deleteThing("existingTemplates", "template", "deleteTemplate.php")
  });

  document.getElementById('deleteNoticeBtn').addEventListener('click', () => {
    deleteThing("existingNotices", "notice", "deleteNotice.php")
  });

  /************************/
  /* Handle uploading PNG */
  /************************/

  document.addEventListener("click", (e) => {
    const input = e.target;
    if (!input.matches('input[data-field="text"]')) return;
    const row = input.closest("tr");
    const type = row.querySelector('input[type="radio"][name^="t-"]:checked')?.value;
    if ((type === "t") || (type === "q")) return;

    if (type === "i") {
      activeImageRow = row;
      document.getElementById("rowFilePicker").click();
    }
  });

  document.getElementById("rowFilePicker").addEventListener("change", function (e) {
    const file = e.target.files[0];
    if (!file || !activeImageRow) return;
    const row = activeImageRow;
    const textInput = row.querySelector('input[data-field="text"]');
    textInput.value = file.name;
    const reader = new FileReader();
    reader.onload = function (ev) {
      let hidden = row.querySelector('input[data-field="imgData"]');
      if (!hidden) {
        hidden = document.createElement("input");
        hidden.type = "hidden";
        hidden.setAttribute("data-field", "img");
        row.appendChild(hidden);
      }
      alert(ev.target.result);
      hidden.value = ev.target.result;
    };
    reader.readAsDataURL(file);

    // reset
    e.target.value = "";
    activeImageRow = null;
  });

 
  /********************************/
  /* Final first-time setup stuff */
  /********************************/
  
  populateDropdown(JSON.parse(document.getElementById('templates-data').textContent), 'existingTemplates');
  populateDropdown(JSON.parse(document.getElementById('backgrounds-data').textContent), 'backgroundImg');
  populateDropdown(JSON.parse(document.getElementById('notices-data').textContent), 'existingNotices');
  document.getElementById('backgroundImg').addEventListener('change', maybeAutoPreview);
  debouncePreview();
});
