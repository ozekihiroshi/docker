{include file="header.tpl"}
<!-- ナビゲーションリンク -->
<div class="nav-links">
    <a href="change_password.php">Change Password</a>
    <a href="reset_request.php">Password Reset Request</a>
    <a href="admin_dashboard.php">Admin Dashboard</a>
</div>
{block name="scripts"}
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <!-- Bootstrap 5 の JavaScript を正しく読み込む -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        $(document).ready(function () {
            $(".request-row").click(function() {
                var requestId = $(this).data("id");
                var username = $(this).data("username");
                var user_type = $(this).data("user_type");

                $("#request_id").val(requestId);
                $("#username").val(username);
                $("#user_type").val(user_type);
                $("#modal-username").text(username);

                // Bootstrap 5のモーダルの開き方
                var modalElement = document.getElementById("passwordResetModal");
                var modal = new bootstrap.Modal(modalElement);
                modal.show();
            });

            // パスワードリセット処理
            $("#passwordResetForm").submit(function (e) {
                e.preventDefault();

                $.ajax({
                    url: "reset_password.php",
                    type: "POST",
                    data: $(this).serialize(),
                    success: function (response) {
                        alert(response);  // 成功メッセージを表示
                        var modalElement = document.getElementById("passwordResetModal");
                        var modal = bootstrap.Modal.getInstance(modalElement);
                        modal.hide(); // モーダルを閉じる
                        location.reload();  // ページをリロードしてステータスを更新
                    },
                    error: function () {
                        alert("Password reset failed.");
                    }
                });
            });
        });
    </script>
{/block}

{block name="content"}
    <!-- リクエスト一覧 -->
    <table class="table table-hover">
        <thead>
            <tr>
                <th>ID</th>
                <th>Username</th>
                <th>User Type</th>
                <th>Request Details</th>
                <th>Status</th>
                <th>Created At</th>
            </tr>
        </thead>
        <tbody>
            {foreach from=$requests item=request}
                <tr class="request-row" data-id="{$request.id}" data-username="{$request.userAccount}" data-user_type="{$request.user_type}">
                    <td>{$request.id}</td>
                    <td>{if isset($request.userAccount)}{$request.userAccount}{else}N/A{/if}</td>
                    <td>{$request.user_type}</td>
                    <td>{if isset($request.request_details)}{$request.request_details}{else}N/A{/if}</td>
                    <td>{$request.status}</td>
                    <td>{$request.created_at}</td>
                </tr>
            {/foreach}
        </tbody>
    </table>
{/block}

<!-- パスワードリセットのモーダル -->
<div id="passwordResetModal" class="modal fade" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Password Reset for <span id="modal-username"></span></h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="passwordResetForm">
                    <input type="hidden" id="request_id" name="request_id">
                    <input type="hidden" id="username" name="username">
                    <input type="hidden" id="user_type" name="user_type">
                    <div class="form-group">
                        <label for="new_password">New Password</label>
                        <input type="password" id="new_password" name="new_password" class="form-control" required>
                    </div>

                    <button type="submit" class="btn btn-primary">Reset Password</button>
                </form>
            </div>
        </div>
    </div>
</div>
{include file="footer.tpl"}
