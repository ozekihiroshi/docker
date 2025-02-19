{include file="header.tpl"}

<!-- ナビゲーションリンク -->
<div class="nav-links">
    <a href="change_password.php">Change Password</a>
    <a href="reset_request.php">ResetPassword Request</a>
    <a href="admin_dashboard.php">Admin dashboard</a>
</div>

<div class="card card-success shadow">
    <div class="card-body">
        <h2 class="text-center">Password Reset Request</h2>

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

        {if $success_message != ""}
            <div class="alert alert-success">
                <i class="fa fa-check-circle"></i> {$success_message}
            </div>
        {/if}

        <form action="reset_request.php" method="post" class="mt-4">
            <div class="mb-3">
                <label class="form-label">User Type</label><br>
                <input type="radio" name="user_type" value="student" required> Student
                <input type="radio" name="user_type" value="staff" required> Staff
            </div>

            <div class="mb-3">
                <label for="department" class="form-label">Department</label>
                <select name="department" id="department" class="form-control" required>
                    <option value="">Select Department</option>
                    {foreach from=$departments item=department}
                        <option value="{$department}">{$department}</option>
                    {/foreach}
                </select>
            </div>

            <div class="mb-3">
                <label for="userAccount" class="form-label">User Account</label>
                <input type="text" name="userAccount" id="userAccount" class="form-control" required>
            </div>

            <button type="submit" class="btn btn-primary">Request Password Reset</button>
        </form>

        <hr>

        <h3 class="mt-4">Pending Requests</h3>
        <table class="table table-striped mt-2">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>User Type</th>
                    <th>Department</th>
                    <th>User Account</th>
                    <th>Status</th>
                    <th>Created At</th>
                </tr>
            </thead>
            <tbody>
                {foreach from=$requests item=request}
                <tr>
                    <td>{$request.id}</td>
                    <td>{$request.user_type}</td>
                    <td>{$request.department}</td>
                    <td> ***** </td>
                    <td>{$request.status}</td>
                    <td>{$request.created_at}</td>
                </tr>
                {/foreach}
            </tbody>
        </table>
    </div>
</div>

{include file="footer.tpl"}

