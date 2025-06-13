{extends file="adminlte_base.tpl"}

{block name="content"}

<section class="content-header">
  <div class="container-fluid">
    <div class="row mb-2">
      <div class="col-sm-12 text-center">
        <h1>Password Reset Requests</h1>
        <p>View and process submitted password reset requests</p>
      </div>
    </div>
  </div>
</section>

<section class="content">
  <div class="container-fluid">

    <div class="card card-info">
      <div class="card-header">
        <h3 class="card-title">Pending Requests</h3>
      </div>
      <div class="card-body table-responsive p-0">
        <table class="table table-hover text-nowrap">
          <thead>
            <tr>
              <th>Request ID</th>
              <th>Username</th>
              <th>User Type</th>
              <th>Department</th>
              <th>Status</th>
              <th>Created At</th>
            </tr>
          </thead>
          <tbody>
            {foreach from=$requests item=request}
              <tr class="request-row"
                  data-id="{$request.id}"
                  data-username="{$request.userAccount}"
                  data-user_type="{$request.user_type}">
                <td>{$request.id}</td>
                <td>{$request.userAccount|default:"N/A"}</td>
                <td>{$request.user_type}</td>
                <td>{$request.department|default:"-"}</td>
                <td>{$request.status}</td>
                <td>{$request.created_at}</td>
              </tr>
            {/foreach}
          </tbody>
        </table>
      </div>
    </div>

  </div>
</section>

<!-- モーダル -->
<div class="modal fade" id="passwordResetModal" tabindex="-1" aria-labelledby="resetLabel" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">

      <form id="passwordResetForm">
        <div class="modal-header">
          <h5 class="modal-title">Reset Password for <span id="modal-username" class="text-primary"></span></h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>

        <div class="modal-body">
          <input type="hidden" name="request_id" id="request_id">
          <input type="hidden" name="username" id="username">
          <input type="hidden" name="user_type" id="user_type">
          <input type="hidden" name="csrf_token" value="{$csrf_token}">

          <div class="form-group">
            <label for="new_password">New Password</label>
            <input type="password" name="new_password" id="new_password" class="form-control" required>
          </div>
        </div>

        <div class="modal-footer">
          <button type="submit" class="btn btn-success">Reset Password</button>
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
        </div>
      </form>

    </div>
  </div>
</div>

{/block}

{block name="scripts"}
<script>
document.addEventListener("DOMContentLoaded", function () {
  // テーブル行クリックでモーダルを表示
  document.querySelectorAll(".request-row").forEach(row => {
    row.addEventListener("click", function () {
      document.getElementById("request_id").value = this.dataset.id;
      document.getElementById("username").value = this.dataset.username;
      document.getElementById("user_type").value = this.dataset.user_type;
      document.getElementById("modal-username").textContent = this.dataset.username;
      new bootstrap.Modal(document.getElementById("passwordResetModal")).show();
    });
  });

  // フォーム送信（AJAX）
  document.getElementById("passwordResetForm").addEventListener("submit", function (e) {
    e.preventDefault();

    fetch("reset_password.php", {
      method: "POST",
      body: new FormData(this)
    })
    .then(response => response.text())
    .then(msg => {
      alert(msg);
      bootstrap.Modal.getInstance(document.getElementById("passwordResetModal")).hide();
      location.reload(); // 状態更新のためリロード
    })
    .catch(err => {
      alert("Password reset failed.");
      console.error(err);
    });
  });
});
</script>
{/block}
</body>
</html>
