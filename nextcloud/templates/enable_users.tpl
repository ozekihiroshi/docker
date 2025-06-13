{extends file='adminlte_base.tpl'}

{block name="title"}Enable Users - Result{/block}

{block name="content"}
<section class="content-header">
  <h1>Enable Users - Result</h1>
</section>

<section class="content mt-4">
  {if $enabled|@count > 0}
    <div class="alert alert-success">
      <strong>Successfully enabled users: {$enabled|@count}</strong>
      <ul>
        {foreach $enabled as $dn}
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

  <a href="reactivate_users.php" class="btn btn-primary">Back to Reactivate Users</a>
</section>
{/block}

