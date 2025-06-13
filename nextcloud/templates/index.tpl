{extends file="adminlte_base.tpl"}

{block name="content"}

<section class="content-header">
  <div class="container-fluid">
    <div class="row mb-2">
      <div class="col-sm-12 text-center">
        <h1>Welcome to GTC Self-Service Portal</h1>
        <p>Please select an option below:</p>
      </div>
    </div>
  </div>
</section>

<section class="content">
  <div class="container-fluid">

    <div class="row">

      <div class="col-md-4 offset-md-2">
        <div class="card bg-primary">
          <div class="card-body text-center">
            <h5>Change Password</h5>
            <p>If you know your current password.</p>
            <a href="change_password.php" class="btn btn-light">Change Password</a>
          </div>
        </div>
      </div>

      <div class="col-md-4">
        <div class="card bg-warning">
          <div class="card-body text-center">
            <h5>Reset Password</h5>
            <p>If you forgot your password.</p>
            <a href="reset_request.php" class="btn btn-light">Reset Password</a>
          </div>
        </div>
      </div>

    </div>

    <div class="row mt-4">
      <div class="col-md-4 offset-md-4">
        <div class="card bg-success">
          <div class="card-body text-center">
            <h5>Admin Login</h5>
            <p>For administrators only.</p>
            <a href="admin_login.php" class="btn btn-light">Admin Dashboard</a>
          </div>
        </div>
      </div>
    </div>

  </div>
</section>

{/block}
