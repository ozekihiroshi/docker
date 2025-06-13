{extends file="adminlte_base.tpl"}

{block name="content"}

<div class="login-box" style="margin: auto; width: 360px; padding-top: 100px;">

  <!-- ロゴ -->
  <div class="login-logo">
    <b>GTC</b> Admin Login
  </div>

  <!-- ログインカード -->
  <div class="card">
    <div class="card-body login-card-body">
      <p class="login-box-msg">Sign in to start your session</p>

      {if $error}
        <div class="alert alert-danger">
          {$error}
        </div>
      {/if}

      <form action="admin_login.php" method="post">
        <div class="input-group mb-3">
          <input type="text" name="username" class="form-control" placeholder="Username" required>
          <div class="input-group-append">
            <div class="input-group-text">
              <span class="fas fa-user"></span>
            </div>
          </div>
        </div>

        <div class="input-group mb-3">
          <input type="password" name="password" class="form-control" placeholder="Password" required>
          <div class="input-group-append">
            <div class="input-group-text">
              <span class="fas fa-lock"></span>
            </div>
          </div>
        </div>

        <div class="row">
          <div class="col-12">
            <button type="submit" class="btn btn-primary btn-block">Sign In</button>
          </div>
        </div>
      </form>

    </div>
    <!-- /.login-card-body -->
  </div>

</div>

{/block}
