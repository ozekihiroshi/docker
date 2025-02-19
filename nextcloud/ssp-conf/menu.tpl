<div class="container">
    <div class="navbar navbar-expand-lg bg-body-tertiary shadow" role="navigation">
      <div class="container-fluid">
        <a class="navbar-brand" href="index.php">
          {if $logo}
          <img src="{$logo}" alt="Logo" class="menu-logo me-2 img-fluid" />
          {/if}
          {$msg_title}
        </a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarSupportedContent" aria-controls="navbarSupportedContent" aria-expanded="false" aria-label="Toggle navigation">
          <span class="navbar-toggler-icon"></span>
        </button>

        <div class="collapse navbar-collapse" id="navbarSupportedContent">
          <ul class="nav navbar-nav me-auto mb-2 mb-lg-0">
            <li class="nav-item">
              <a href="index.php" class="nav-link {if $action == 'change'}active{/if}" aria-current="page">
                <i class="fa fa-fw fa-key"></i> Change Password
              </a>
            </li>
            <li class="nav-item">
              <a href="reset_request.php" class="nav-link {if $action == 'reset_request'}active{/if}" aria-current="page">
                <i class="fa fa-fw fa-question-circle"></i> Request Password Reset
              </a>
            </li>
            <li class="nav-item">
              <a href="admin_dashboard.php" class="nav-link {if $action == 'admin_dashboard'}active{/if}" aria-current="page">
                <i class="fa fa-fw fa-user-shield"></i> Admin Dashboard
              </a>
            </li>
          </ul>
        </div>
      </div>
    </div>
</div>

