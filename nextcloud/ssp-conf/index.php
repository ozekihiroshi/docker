<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
#==============================================================================
# Configuration
#==============================================================================
require_once(__DIR__ . "/../conf/config.inc.php");
require_once(__DIR__ . "/../vendor/autoload.php");

#==============================================================================
# Routing
#==============================================================================
$action = isset($_GET['action']) ? $_GET['action'] : 'reset_request';

switch ($action) {
    case 'reset_request':
        require_once(__DIR__ . "/reset_request.php");
        break;
    case 'admin_dashboard':
        require_once(__DIR__ . "/admin_dashboard.php");
        break;
    default:
        echo "Action not found.";
        break;
}

