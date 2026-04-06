<?php
  include "helpers/template.php";

  function populate_notices() {
    echo(populate_generic("notices", "json"));
  }

  function populate_devices() {
    echo(populate_generic("devices", "json"));
  }

?>
<!DOCTYPE html>
<html>
  <head>
    <title>Scheduler - Reside TRMNL server</title>
    <script src="css/bootstrap.bundle.min.js"></script>
    <link href="css/bootstrap.min.css" rel="stylesheet">
    <link href="css/bootstrap-icons.min.css" rel="stylesheet">
    <link rel="stylesheet" href="css/scheduler.css">

    <script id="devices-data" type="application/json">
      <?php populate_devices(); ?>
    </script>
    <script id="notices-data" type="application/json">
      <?php populate_notices(); ?>
    </script>
    <script src="Scheduler.js"></script>
  </head>

  <body class="p-3">
    <div class="container mb-4">
      <div class="row justify-content-center">
        <div class="col-auto me-3" style="width:400px; text-align:center;">
          <img id="previewImage" width="400" height="240" alt="Preview" 
               class="border d-block mx-auto" src="backgrounds/ic-display.png">
        </div>
        <div class="col-auto me-3" style="width:250px;">
          <div class="card h-100" style="max-height:240px;">
            <div class="card-header">Notices</div>
            <div class="card-body scroll-card" id="noticesList">
            </div>
          </div>
        </div>
        <div class="col-auto" style="width:250px;">
          <div class="card h-100" style="max-height:240px;">
            <div class="card-header">Devices</div>
            <div class="card-body scroll-card" id="devicesList">
            </div>
          </div>
        </div>
      </div><hr/>
      <div class="row mb-4 justify-content-center">
        <div class="col-md-3">
          <label for="fromDate" class="form-label">From</label>
          <input type="datetime-local" class="form-control" id="fromDate">
        </div>
        <div class="col-md-3">
            <label for="toDate" class="form-label">To</label>
            <input type="datetime-local" class="form-control" id="toDate">
        </div>
        <div class="col-md-2 align-self-end">
            <button id="addBtn" class="btn btn-primary w-100">Add</button>
        </div>
      </div>
      <div class="row">
        <div class="col-12">
          <div style="max-height: 300px; overflow-y: auto; border: 1px solid #dee2e6;">
            <table class="table table-bordered" id="scheduleTable">
              <thead class="table-light">
                <tr>
                  <th style="width:15%">From</th>
                  <th style="width:15%">To</th>
                  <th style="width:25%">Notices</th>
                  <th style="width:17%">Devices</th>
                  <th style="width:8%">Actions</th>
                </tr>
              </thead>
              <tbody>
              </tbody>
            </table>
          </div>
        </div>
      </div>
      <div class="row mt-3">
        <div class="col text-center">
          <button id="revertBtn" class="btn btn-secondary ms-2">
            <i class="bi bi-folder2-open"></i> Revert to Last Saved
          </button>
          <button id="saveAllBtn" class="btn btn-success px-4">
            <i class="bi bi-save"></i> Save All
          </button>
        </div>
      </div>
    </div>
    <div class="position-fixed bottom-0 end-0 p-3" style="z-index: 11">
      <div id="saveToast" class="toast align-items-center text-bg-success border-0" role="alert">
        <div class="d-flex">
          <div class="toast-body">
            Saved!
          </div>
          <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast"></button>
        </div>
      </div>
    </div>
  </body>
</html>