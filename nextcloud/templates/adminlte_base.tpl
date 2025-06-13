<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>{$title|default:'Admin Panel'}</title>

  <!-- Bootstrap 5 CSS (CDN) -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <!-- FontAwesome (CDN) -->
  <link href="https://cdn.jsdelivr.net/npm/@fortawesome/fontawesome-free@6.5.1/css/all.min.css" rel="stylesheet">
  <!-- AdminLTE (ローカル) -->
  <link rel="stylesheet" href="/adminlte/css/adminlte.min.css">

  {$custom_css|default:''}
</head>

<body class="hold-transition sidebar-mini">

<div class="wrapper">

  <!-- メインヘッダーナビ -->
  <nav class="main-header navbar navbar-expand navbar-white navbar-light">
    <ul class="navbar-nav ml-auto">

      {if isset($admin_user) && $admin_user != ''}
        <!-- ★ 管理者ログイン後用メニュー ★ -->
        <li class="nav-item">
          <a href="admin_dashboard.php" class="nav-link">Dashboard</a>
        </li>
        <li class="nav-item">
          <a href="ldap_user_import.php" class="nav-link">User Import</a>
        </li>
        <li class="nav-item">
          <a href="update_password.php" class="nav-link">Password Requests</a>
        </li>
        <li class="nav-item">
          <a href="logout.php" class="nav-link text-danger">Logout</a>
        </li>

      {else}
        <!-- ★ 一般ユーザーメニュー（未ログイン時） ★ -->
        <li class="nav-item">
          <a href="change_password.php" class="nav-link">Change Password</a>
        </li>
        <li class="nav-item">
          <a href="reset_request.php" class="nav-link">Reset Password Request</a>
        </li>
        <li class="nav-item">
          <a href="admin_login.php" class="nav-link">Admin Login</a>
        </li>
      {/if}

    </ul>
  </nav>

  <!-- メインコンテンツ -->
  <div class="content-wrapper p-3">
    {block name="content"}{/block}
  </div>

</div>

<!-- jQuery (CDN) -->
<script src="https://cdn.jsdelivr.net/npm/jquery@3.7.1/dist/jquery.min.js"></script>
<!-- Bootstrap 5 Bundle JS (CDN) -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<!-- AdminLTE (ローカル) -->
<script src="/adminlte/js/adminlte.min.js"></script>

{$custom_js|default:''}

{block name="scripts"}{/block}

</body>
</html>
