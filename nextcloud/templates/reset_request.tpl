{extends file="adminlte_base.tpl"}

{block name="content"}

<section class="content-header">
  <div class="container-fluid">
    <div class="row mb-2">
      <div class="col-sm-12 text-center">
        <h1>Password Reset Request</h1>
        <p>Please fill in the form below to request a password reset.</p>
      </div>
    </div>
  </div>
</section>

<section class="content">
  <div class="container-fluid">
    <div class="row justify-content-center">

      <!-- パスワードリセットフォーム -->
      <div class="col-md-6">
        <div class="card card-primary">
          <div class="card-body">

            {if $error}
              <div class="alert alert-danger">
                {$error}
              </div>
            {/if}

            {if $success_message}
              <div class="alert alert-success">
                {$success_message}
              </div>
            {/if}

            <form method="POST" action="reset_request.php">
              <input type="hidden" name="csrf_token" value="{$csrf_token}">

              <div class="mb-3">
                <label for="user_type" class="form-label">User Type</label>
                <select name="user_type" id="user_type" class="form-control" required>
                  <option value="">Select User Type</option>
                  <option value="staff">Staff</option>
                  <option value="student">Student</option>
                </select>
              </div>

              <div class="mb-3">
                <label for="department" class="form-label">Department</label>
                <select name="department" id="department" class="form-control" required>
                  {foreach from=$departments item=dept}
                    <option value="{$dept}">{$dept}</option>
                  {/foreach}
                </select>
              </div>

              <div class="mb-3">
                <label for="userAccount" class="form-label">Username</label>
                <input type="text" name="userAccount" id="userAccount" class="form-control" required>
              </div>

              <div class="d-grid gap-2">
                <button type="submit" class="btn btn-primary">Submit Request</button>
              </div>

            </form>

          </div>
        </div>
      </div>

    </div>

    <!-- ペンディングリクエスト一覧 -->
    <div class="row justify-content-center mt-4">

      <div class="col-md-10">
        <div class="card card-info">
          <div class="card-header">
            <h3 class="card-title">Pending Requests</h3>
          </div>

          <div class="card-body table-responsive p-0">
            <table class="table table-hover text-nowrap">
              <thead>
                <tr>
                  <th>ID</th>
                  <th>User Account</th>
                  <th>User Type</th>
                  <th>Department</th>
                  <th>Status</th>
                  <th>Created At</th>
                </tr>
              </thead>
              <tbody>
                {foreach from=$requests item=req}
                  <tr>
                    <td>{$req.id}</td>
                    <td>*******</td>
                    <td>{$req.user_type}</td>
                    <td>{$req.department}</td>
                    <td>{$req.status}</td>
                    <td>{$req.created_at}</td>
                  </tr>
                {/foreach}
              </tbody>
            </table>
          </div>

        </div>
      </div>

    </div>

  </div>
</section>

{/block}

