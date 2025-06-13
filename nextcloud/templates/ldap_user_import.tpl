{extends file="adminlte_base.tpl"}

{block name="content"}

<section class="content-header">
  <div class="container-fluid">
    <div class="row mb-2">
      <div class="col-sm-12 text-center">
        <h1>Bulk User Import</h1>
        <p>Upload an Excel file to register multiple Active Directory users.</p>
      </div>
    </div>
  </div>
</section>

<section class="content">
  <div class="container-fluid">
    <div class="row justify-content-center">

      <div class="col-md-8">
        <div class="card card-primary">
          <div class="card-body">

            <form action="ldap_user_import.php" method="POST" enctype="multipart/form-data" onsubmit="disableSubmitButton();">
              <input type="hidden" name="csrf_token" value="{$csrf_token}">

              <div class="mb-3">
                <label for="userType" class="form-label">User Type</label>
                <select name="userType" id="userType" class="form-control" required>
                  <option value="students" selected>Students</option>
                  <option value="staff">Staff</option>
                </select>
              </div>

              <div class="mb-3">
                <label for="excel_file" class="form-label">Upload Excel File (.xlsx)</label>
                <input type="file" name="excel_file" id="excel_file" class="form-control" accept=".xlsx" required>
              </div>

              <div class="d-grid gap-2">
                <button type="submit" id="submitButton" class="btn btn-primary">Upload & Register Users</button>
              </div>
            </form>

          </div>
        </div>
      </div>

    </div>

    {if isset($results)}
    <div class="row justify-content-center mt-4">
      <div class="col-md-10">
        <div class="card card-info">
          <div class="card-header">
            <h3 class="card-title">Import Results</h3>
          </div>

          <div class="card-body table-responsive p-0">
            <table class="table table-hover">
              <thead>
                <tr>
                  <th>Username</th>
                  <th>Status</th>
                  <th>Message</th>
                </tr>
              </thead>
              <tbody>
                {foreach from=$results item=result}
                  <tr class="{if $result.status == 'Success'}table-success{else}table-danger{/if}">
                    <td>{$result.username}</td>
                    <td>{$result.status}</td>
                    <td>{$result.message}</td>
                  </tr>
                {/foreach}
              </tbody>
            </table>
          </div>

          {if $csv_download_link}
          <div class="card-footer">
            <a href="{$csv_download_link}" class="btn btn-success">Download Created Accounts CSV</a>
          </div>
          {/if}

        </div>
      </div>
    </div>
    {/if}

  </div>
</section>

<script>
function disableSubmitButton() {
    const btn = document.getElementById('submitButton');
    btn.disabled = true;
    btn.innerText = "Uploading...";
}
</script>

{/block}
