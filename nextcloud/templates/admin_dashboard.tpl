{extends file='adminlte_base.tpl'}
{block name="title"}Admin Dashboard{/block}
{block name="content"}

<section class="content-header">
  <h1>Admin Dashboard</h1>
</section>

<section class="content">
  <div class="row">

    <!-- Password Operations -->
    <div class="col-md-6">
      <div class="card card-success">
        <div class="card-header">
          <h3 class="card-title">Password Reset Management</h3>
        </div>
        <div class="card-body">
          <p>View reset requests and process password changes.</p>
          <a href="update_password.php" class="btn btn-success">Manage Requests</a>
        </div>
      </div>
    </div>

    <!-- Bulk User Import -->
    <div class="col-md-6">
      <div class="card card-info">
        <div class="card-header">
          <h3 class="card-title">Bulk User Import</h3>
        </div>
        <div class="card-body">
          <p>Upload Excel files to register multiple AD users.</p>
          <a href="ldap_user_import.php" class="btn btn-info">Import Users</a>
        </div>
      </div>
    </div>

    <!-- Inactive User Search -->
    <div class="col-md-6">
      <div class="card card-warning">
        <div class="card-header">
          <h3 class="card-title">Inactive User Search</h3>
        </div>
        <div class="card-body">
          <p>Find users who haven't logged in for a defined period and disable them in bulk.</p>
          <a href="inactive_users.php" class="btn btn-warning">Search Inactive Users</a>
        </div>
      </div>
    </div>

    <!-- Reactivate Disabled Users -->
    <div class="col-md-6">
      <div class="card card-secondary">
        <div class="card-header">
          <h3 class="card-title">Reactivate Disabled Users</h3>
        </div>
        <div class="card-body">
          <p>Search disabled users and enable them again if needed.</p>
          <a href="reactivate_users.php" class="btn btn-secondary">Search Disabled Users</a>
        </div>
      </div>
    </div>

    <!-- Deleted User Archive (DB) -->
    <div class="col-md-6">
      <div class="card card-danger">
        <div class="card-header">
          <h3 class="card-title">Deleted User Archive</h3>
        </div>
        <div class="card-body">
          <p>View and restore archived deleted users.</p>
          <a href="view_deleted_users.php" class="btn btn-danger">View Deleted Users</a>
        </div>
      </div>
    </div>

  </div>
</section>

{/block}
