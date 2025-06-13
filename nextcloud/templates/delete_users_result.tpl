{extends file='adminlte_base.tpl'}

{block name="title"}Delete Result{/block}

{block name="content"}
<section class="content-header">
  <h1>Delete Result</h1>
</section>

<section class="content mt-3">

  {if $success_deleted|@count > 0}
    <div class="alert alert-success">
      <strong>Success:</strong> The following users were successfully deleted.
    </div>
    <table class="table table-bordered table-striped">
      <thead>
        <tr>
          <th>#</th>
          <th>Distinguished Name (DN)</th>
        </tr>
      </thead>
      <tbody>
        {foreach $success_deleted as $dn}
          <tr>
            <td>{$smarty.foreach.success.iteration}</td>
            <td>{$dn}</td>
          </tr>
        {/foreach}
      </tbody>
    </table>
  {/if}

  {if $failed_deleted|@count > 0}
    <div class="alert alert-danger mt-4">
      <strong>Errors:</strong> The following users could not be deleted.
    </div>
    <table class="table table-bordered table-striped">
      <thead>
        <tr>
          <th>#</th>
          <th>Distinguished Name (DN)</th>
          <th>Error</th>
        </tr>
      </thead>
      <tbody>
        {foreach $failed_deleted as $fail}
          <tr>
            <td>{$smarty.foreach.fail.iteration}</td>
            <td>{$fail.dn}</td>
            <td>{$fail.error}</td>
          </tr>
        {/foreach}
      </tbody>
    </table>
  {/if}

  {if $success_deleted|@count == 0 && $failed_deleted|@count == 0}
    <div class="alert alert-warning">
      No users were processed.
    </div>
  {/if}

  <div class="mt-4">
    <a href="reactivate_users.php" class="btn btn-secondary">Back to Disabled Users</a>
  </div>

</section>
{/block}

