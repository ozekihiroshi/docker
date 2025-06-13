{extends file='adminlte_base.tpl'}

{block name="title"}Confirm Deletion{/block}

{block name="content"}
<section class="content-header">
  <h1>Confirm User Deletion</h1>
  <p class="text-danger">The following users will be permanently deleted. Are you sure?</p>
</section>

<section class="content">
  <form method="post" action="delete_users.php">
    <input type="hidden" name="csrf_token" value="{$csrf_token}" />
    <input type="hidden" name="confirm" value="yes" />

    <table class="table table-bordered table-striped">
      <thead>
        <tr>
          <th>Username</th>
          <th>Distinguished Name (DN)</th>
          <th>User Type</th>
        </tr>
      </thead>
      <tbody>
        {foreach $delete_candidates as $user}
          <tr>
            <td>{$user.username}</td>
            <td>{$user.dn}</td>
            <td>{$user.user_type}</td>
          </tr>
          <input type="hidden" name="user_type" value="{$user_type}" />
          <input type="hidden" name="dns[]" value="{$user.dn}" />
          <input type="hidden" name="usernames[{$user.dn}]" value="{$user.username}" />
        {/foreach}
      </tbody>
    </table>

    <div class="mt-3">
      <button type="submit" class="btn btn-danger">Yes, Delete Selected Users</button>
      <a href="reactivate_users.php" class="btn btn-secondary">Cancel</a>
    </div>
  </form>
</section>
{/block}

