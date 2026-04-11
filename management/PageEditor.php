<?php
  include "helpers/template.php";

  function populate_fonts() {
    echo(populate_generic("fonts", "ttf"));
  }

  function populate_templates() {
    echo(populate_generic("templates", "json"));
  }

  function populate_notices() {
    echo(populate_generic("notices", "json"));
  }

  function populate_backgrounds() {
    echo(populate_generic("backgrounds", "png"));
  }

?>
<!DOCTYPE html>
<html>
  <head>
    <title>Page Editor - Reside TRMNL server</title>
    <script src="css/bootstrap.bundle.min.js"></script>
    <link href="css/bootstrap.min.css" rel="stylesheet">
    <link href="css/bootstrap-icons.min.css" rel="stylesheet">
    <link rel="stylesheet" href="css/styles.css">
    <script id="font-data" type="application/json">
      <?php populate_fonts(); ?>
    </script>
    <script id="backgrounds-data" type="application/json">
      <?php populate_backgrounds(); ?>
    </script>
    <script id="templates-data" type="application/json">
      <?php populate_templates(); ?>
    </script>
    <script id="notices-data" type="application/json">
      <?php populate_notices(); ?>
    </script>
    <script src="PageEditor.js"></script>
  </head>
  <body class="p-4">
    <input type="file" id="rowFilePicker" accept="image/png" style="display:none">
    <div class="container">
      <div class="row mb-3">
        <div class="mb-6 col-md-6 text-center">
          <img id="previewImage" width="400" height="240" alt="Preview" style="display:block" class="border d-block mx-auto" src="backgrounds/ic-display.png">
          <div class="mt-2">
            <button id="previewBtn" class="btn btn-primary btn-sm">Preview</button>
            <label class="ms-3">
              <input type="checkbox" id="autoPreview"> Auto Preview
            </label>
          </div>
        </div>
        <div class="col-md-6">
          <ul class="nav nav-tabs mb-3" id="modeTabs">
            <li class="nav-item">
              <button class="nav-link active" data-bs-toggle="tab" data-bs-target="#templatesPane">
                Templates
              </button>
            </li>
            <li class="nav-item">
              <button class="nav-link" data-bs-toggle="tab" data-bs-target="#noticesPane">
                Notices
              </button>
            </li>
          </ul>
          <div class="tab-content">
            <div class="tab-pane fade show active" id="templatesPane">
              <div class="card p-3">
              <div class="d-flex align-items-center mb-3">
                <label for="templateName" class="me-2 mb-0">This Template:</label>
                <input type="text" id="templateName" class="form-control me-2" placeholder="Enter template name" style="flex:1">
                <button id="saveTemplateBtn" class="btn btn-success">Save</button>
              </div>
              <div class="d-flex align-items-center">
                <label for="existingTemplates" class="me-2 mb-0">Existing Templates:</label>
                <select id="existingTemplates" class="form-select me-2" style="flex:1">
                </select>
                <button id="loadTemplateBtn" class="btn btn-primary me-1">Load</button>
                <button id="deleteTemplateBtn" class="btn btn-danger">Delete</button>
              </div>
              </div>
            </div>
            <div class="tab-pane fade" id="noticesPane">
              <div class="card p-3">
              <div class="d-flex align-items-center mb-3">
                <label for="noticeName" class="me-2 mb-0">This Notice:</label>
                <input type="text" id="noticeName" class="form-control me-2" placeholder="Enter notice name" style="flex:1">
                <button id="saveNoticeBtn" class="btn btn-success">Save</button>
              </div>
              <div class="d-flex align-items-center">
                <label for="existingNotices" class="me-2 mb-0">Existing Notices:</label>
                <select id="existingNotices" class="form-select me-2" style="flex:1">
                </select>
                <button id="loadNoticeBtn" class="btn btn-primary me-1">Load</button>
                <button id="deleteNoticeBtn" class="btn btn-danger">Delete</button>
              </div>
              </div>
            </div>
          </div>
          <hr/>
          <div class="card p-3">
            <div class="d-flex align-items-center">
              <label for="backgroundImg" class="me-2 mb-0">Background Image:</label>
              <select id="backgroundImg" class="form-select me-2" style="flex:1">
              </select>
            </div>
          </div>
        </div>
      </div>
      &nbsp;<br/><hr/>
      <div class="table-responsive mx-auto" style="width: 94%; max-height: 50vh; overflow-y: auto;">
        <table class="table-responsive" id="editableTable">
          <thead>
            <tr>
              <th style="width:11%">Type</th>
              <th style="width:7%">X</th>
              <th style="width:7%">Y</th>
              <th style="width:10%">Justify</th>
              <th style="width:22%">Font</th>
              <th style="width:8%">Size/Scale</th>
              <th style="width:10%">Colour</th>
              <th style="width:16%">Text/Content</th>
              <th style="width:11%">Actions</th>
            </tr>
          </thead>
          <tbody></tbody>
        </table>
      </div>
      <div class="d-flex justify-content-center mt-2">
        <button id="addRowBtn" class="btn btn-primary">Add Row</button>
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