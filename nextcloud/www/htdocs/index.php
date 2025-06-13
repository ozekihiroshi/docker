<?php
require_once(__DIR__ . "/../conf/config.inc.php");
require_once(__DIR__ . "/../vendor/autoload.php");
require_once(__DIR__ . "/../vendor/smarty/smarty/libs/Smarty.class.php");

$smarty = new Smarty\Smarty();
$smarty->setTemplateDir(__DIR__ . '/../templates/');
$smarty->setCompileDir(__DIR__ . '/../templates_c/');

$smarty->display('index.tpl');
?>

