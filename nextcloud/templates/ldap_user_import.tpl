{include file="header.tpl"}

<div class="container">
    <h2>Import Users from Excel</h2>
    <form action="ldap_user_import.php" method="POST" enctype="multipart/form-data">
        <div class="mb-3">
            <label for="excel_file" class="form-label">Upload Excel File (.xlsx)</label>
            <input type="file" name="excel_file" id="excel_file" class="form-control" accept=".xlsx" required>
        </div>
        <button type="submit" class="btn btn-primary">Upload & Register Users</button>
    </form>

    {if isset($results)}
        <h3 class="mt-4">Results</h3>
        <table class="table table-bordered">
            <thead>
                <tr>
                    <th>Username</th>
                    <th>Status</th>
                    <th>Message</th>
                </tr>
            </thead>
            <tbody>
                {foreach from=$results item=result}
                    <tr class="{if $result.status == 'Success'}table-success{else}table-danger{/if}">
                        <td>{$result.username}</td>
                        <td>{$result.status}</td>
                        <td>{$result.message}</td>
                    </tr>
                {/foreach}
            </tbody>
        </table>
    {/if}
</div>

{include file="footer.tpl"}
