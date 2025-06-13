{extends file='adminlte_base.tpl'}

{block name="title"}Enable Users - Result{/block}

{block name="content"}
<section class="content-header">
  <h1>Enable Users - Result</h1>
</section>

<section class="content mt-4">
  {if $success_enabled|@count > 0}
    <div class="alert alert-success">
      <strong>Successfully enabled users:</strong>
      <ul>
        {foreach $success_enabled as $dn}
          <li>{$dn}</li>
        {/foreach}
      </ul>
    </div>
  {/if}

  {if $failed_enabled|@count > 0}
    <div class="alert alert-danger">
      <strong>Some errors occurred:</strong>
      <ul>
        {foreach $failed_enabled as $fail}
          <li>{$fail.dn}: {$fail.error}</li>
        {/foreach}
      </ul>
    </div>
  {/if}

  <a href="reactivate_users.php" class="btn btn-primary">Back to Disabled Users</a>
</section>
{/block}

