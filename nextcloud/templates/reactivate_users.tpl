{extends file='adminlte_base.tpl'}

{block name="title"}Manage Disabled Users{/block}

{block name="content"}
<section class="content-header">
  <h1>Manage Disabled Users</h1>
  <form method="post" class="mb-4">
    <input type="hidden" name="csrf_token" value="{$csrf_token}">
    <div class="row">
      <div class="col-md-3">
        <label for="days">Disabled for (days):</label>
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
  {if $success}
    <div class="alert alert-success">{$success}</div>
  {/if}

  {if $disabled_users|@count > 0}
    <div class="card">
      <div class="card-header">
        <h3 class="card-title">Found {$disabled_users|@count} disabled users</h3>
      </div>
      <div class="card-body p-0">
        <form method="post" id="bulkActionsForm">
          <input type="hidden" name="csrf_token" value="{$csrf_token}" />
          <table class="table table-striped table-bordered">
            <thead>
              <tr>
                <th><input type="checkbox" id="check_all" /></th>
                <th>Username</th>
                <th>Distinguished Name</th>
                <th>Disabled At</th>
                <th>Created At</th>
              </tr>
            </thead>
            <tbody>
              {foreach $disabled_users as $user}
                <tr>
                  <td><input type="checkbox" name="dns[]" value="{$user.dn}" /></td>
                  <input type="hidden" name="user_type" value="{$user.user_type}" />
                  <input type="hidden" name="usernames[{$user.dn}]" value="{$user.username}" />
                  <td>{$user.username}</td>
                  <td>{$user.dn}</td>
                  <td>{$user.disabled_at|default:'-'}</td>
                  <td>{$user.created_local}</td>
                </tr>
              {/foreach}
            </tbody>
          </table>

          <div class="mt-3">
            <button type="submit" formaction="enable_users.php" class="btn btn-success">Enable Selected Users</button>
            <button type="submit" formaction="delete_users.php" class="btn btn-danger">Delete Selected Users</button>
          </div>
        </form>
      </div>
    </div>
<script>
  document.getElementById('check_all').addEventListener('click', function() {
    const checkboxes = document.querySelectorAll('input[name="dns[]"]');
    checkboxes.forEach(cb => cb.checked = this.checked);
  });
</script>
  {else}
    <p>No disabled users found for the past {$days} days.</p>
  {/if}
</section>
{/block}
