{extends file='adminlte_base.tpl'}

{block name="title"}Disable Users - Result{/block}

{block name="content"}
<section class="content-header">
  <h1>Disable Users - Result</h1>
</section>

<section class="content mt-4">
  {if $disabled|@count > 0}
    <div class="alert alert-success">
      <strong>Successfully disabled users: {$disabled|@count}</strong>
      <ul>
        {foreach $disabled as $dn}
          <li>{$dn}</li>
        {/foreach}
      </ul>
    </div>
  {/if}

  {if $errors|@count > 0}
    <div class="alert alert-danger">
      <strong>Some errors occurred:</strong>
      <ul>
        {foreach $errors as $error}
          <li>{$error}</li>
        {/foreach}
      </ul>
    </div>
  {/if}

  <a href="inactive_users.php" class="btn btn-primary">Back to Inactive Users</a>
</section>
{/block}

