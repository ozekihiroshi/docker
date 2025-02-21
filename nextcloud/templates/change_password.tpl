{include file="header.tpl"}
<div class="nav-links">
    <a href="change_password.php">Change Password</a>
    <a href="reset_request.php">ResetPassword Request</a>
    <a href="admin_dashboard.php">Admin Dashboard</a>
</div>
<div class="card card-primary shadow">
    <div class="card-body">
        <h2 class="text-center">Change Password</h2>

        {if $error != ""}
            <div class="alert alert-danger">
                <i class="fa fa-exclamation-circle"></i> {$error}
            </div>
        {/if}

        {if $success != ""}
            <div class="alert alert-success">
                <i class="fa fa-check-circle"></i> {$success}
            </div>
        {/if}

        <form action="change_password.php" method="post" onsubmit="return validatePassword()">
           <div class="mb-3">
                <label for="user_type" class="form-label">User Type</label>
                <select name="user_type" id="user_type" class="form-control" required>
                    <option value="">Select User Type</option>
                    <option value="staff">Staff</option>
                    <option value="students">Students</option>
                </select>
            </div>
            <div class="mb-3">
                <label for="username" class="form-label">Username</label>
                <input type="text" name="username" id="username" class="form-control" required>
            </div>

            <div class="mb-3">
                <label for="current_password" class="form-label">Current Password</label>
                <input type="password" name="current_password" id="current_password" class="form-control" required>
            </div>

            <div class="mb-3">
                <label for="new_password" class="form-label">New Password</label>
                <input type="password" name="new_password" id="new_password" class="form-control" required>
            </div>

            <div class="mb-3">
                <label for="confirm_password" class="form-label">Confirm New Password</label>
                <input type="password" name="confirm_password" id="confirm_password" class="form-control" required>
                <span id="password-error" class="text-danger"></span>
            </div>

            <button type="submit" class="btn btn-success">Change Password</button>
        </form>
    </div>
</div>

<script>
function validatePassword() {
    let newPassword = document.getElementById("new_password").value;
    let confirmPassword = document.getElementById("confirm_password").value;
    let errorMessage = document.getElementById("password-error");

    if (newPassword !== confirmPassword) {
        errorMessage.textContent = "Passwords do not match.";
        return false;
    }

    errorMessage.textContent = "";
    return true;
}
</script>

{include file="footer.tpl"}

