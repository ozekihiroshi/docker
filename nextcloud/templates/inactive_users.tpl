{extends file='adminlte_base.tpl'}

{block name="title"}Inactive Users{/block}

{block name="content"}
<section class="content-header">
  <h1>Inactive Users</h1>
<form method="post" class="mb-4">
  <input type="hidden" name="csrf_token" value="{$csrf_token}">
  <div class="row">
    <div class="col-md-3">
      <label for="days">Inactive for (days):</label>
      <input type="number" name="days" id="days" value="{$days}" class="form-control">
    </div>
    <div class="col-md-3">
      <label for="user_type">User Type:</label>
      <select name="user_type" id="user_type" class="form-control">
        <option value="staff" {if $user_type == "staff"}selected{/if}>Staff</option>
        <option value="students" {if $user_type == "students"}selected{/if}>Students</option>
      </select>
    </div>
    <div class="col-md-3 align-self-end">
      <button type="submit" class="btn btn-primary">Search</button>
    </div>
  </div>
</form>
</section>

<section class="content mt-4">
  {if $error}
    <div class="alert alert-danger">{$error}</div>
  {/if}

  {if $inactive_users|@count > 0}
    <div class="card">
      <div class="card-header">
        <h3 class="card-title">Found {$inactive_users|@count} users</h3>
      </div>
      <div class="card-body p-0">
      <form method="post" action="disable_users.php">
        <input type="hidden" name="csrf_token" value="{$csrf_token}" />
        <table class="table table-striped table-bordered">
          <thead>
            <tr>
              <th><input type="checkbox" id="check_all" /></th>
              <th>Username</th>
              <th>Distinguished Name (DN)</th>
              <th>Last Logon (Local Time)</th>
              <th>Created (Local Time)</th>
            </tr>
          </thead>
          <tbody>
            {foreach $inactive_users as $user}
              <tr>
                <td><input type="checkbox" name="disable_dns[]" value="{$user.dn}" /></td>
                <td>{$user.username}</td>
                <td>{$user.dn}</td>
                <td>{$user.last_logon_local}</td>
                <td>{$user.created_local}</td>
              </tr>
            {/foreach}
          </tbody>
        </table>
        <button type="submit" class="btn btn-danger">Disable Selected Users</button>
      </div>
    </div>
<script>
document.getElementById('check_all').addEventListener('click', function() {
  const checkboxes = document.querySelectorAll('input[name="disable_dns[]"]');
  checkboxes.forEach(cb => cb.checked = this.checked);
});
</script>
  {else}
    <p>No inactive users found for the past {$days} days.</p>
  {/if}
</section>
{/block}
