{extends file="adminlte_base.tpl"}

{block name="content"}

<section class="content-header">
  <div class="container-fluid">
    <div class="row mb-2">
      <div class="col-sm-12 text-center">
        <h1>Change Your Password</h1>
        <p>Please use your current AD password to change it securely.</p>
      </div>
    </div>
  </div>
</section>

<section class="content">
  <div class="container-fluid">
    <div class="row justify-content-center">
      <div class="col-md-6">
        <div class="card card-primary">
          <div class="card-body">

            {if $error != ""}
              <div class="alert alert-danger">
                <i class="fas fa-exclamation-triangle"></i> {$error}
              </div>
            {/if}

            {if $success != ""}
              <div class="alert alert-success">
                <i class="fas fa-check-circle"></i> {$success}
              </div>
            {/if}

            <form action="change_password.php" method="post" onsubmit="return validatePassword();">
              <input type="hidden" name="csrf_token" value="{$csrf_token}">

              <div class="form-group">
                <label for="user_type">User Type</label>
                <select name="user_type" id="user_type" class="form-control" required>
                  <option value="">Select User Type</option>
                  <option value="staff">Staff</option>
                  <option value="students">Students</option>
                </select>
              </div>

              <div class="form-group">
                <label for="username">Username</label>
                <input type="text" name="username" id="username" class="form-control" required>
              </div>

              <div class="form-group">
                <label for="current_password">Current Password</label>
                <input type="password" name="current_password" id="current_password" class="form-control" required>
              </div>

              <div class="form-group">
                <label for="new_password">New Password</label>
                <input type="password" name="new_password" id="new_password" class="form-control" required>
              </div>

              <div class="form-group">
                <label for="confirm_password">Confirm New Password</label>
                <input type="password" name="confirm_password" id="confirm_password" class="form-control" required>
                <small id="password-error" class="text-danger"></small>
              </div>

              <button type="submit" class="btn btn-success btn-block">Change Password</button>
            </form>

          </div>
        </div>
      </div>
    </div>
  </div>
</section>

<script>
function validatePassword() {
  const pw1 = document.getElementById("new_password").value;
  const pw2 = document.getElementById("confirm_password").value;
  const err = document.getElementById("password-error");
  if (pw1 !== pw2) {
    err.textContent = "Passwords do not match.";
    return false;
  }
  err.textContent = "";
  return true;
}
</script>

{/block}
