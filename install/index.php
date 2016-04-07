<?php
session_start();
ini_set('error_reporting', E_ALL);
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);

define('__archiveDIR', '../archive/');
define('__corePath', '../core/');
define('__workfolder', '../');
define('__imgfolder', '../img/');
define('__spath', $_SERVER['REQUEST_URI']);

include __corePath.'libs/db.php';
include __corePath.'libs/core.php';
include __corePath.'libs/controller.php';
include __corePath.'libs/action.php';
include __corePath.'libs/service.php';
include __corePath.'libs/jsonDB.php';
include __corePath.'app.php';
include __corePath.'lang/en.php';

function getInstallActions()
{
if(!isset($_POST['action'])) return 0;

$action=str_replace('.','##',$_POST['action']);
if(file_exists("actions/".$action.'.php'))
  return $action;
return 0;
}

$action = getInstallActions();

if($action)
  {
  include "actions/$action.php";
  $action = new action(0);
  $action->execute();
  }

$route = getroute();

$curpage = 'controller/'.$route;

include "$curpage.php";

$page = new page($curpage, 0, 0);
$page->prepare();
$page->render();

echo $page->show();
?>