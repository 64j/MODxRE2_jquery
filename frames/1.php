<?php

if(IN_MANAGER_MODE != "true") {
	die("<b>INCLUDE_ORDERING_ERROR</b><br /><br />Please use the MODX Content Manager instead of accessing this file directly.");
}
header("X-XSS-Protection: 0");

$_SESSION['browser'] = (strpos($_SERVER['HTTP_USER_AGENT'], 'MSIE 1') !== false) ? 'legacy_IE' : 'modern';

$mxla = $modx_lang_attribute ? $modx_lang_attribute : 'en';

$modx->invokeEvent('OnManagerPreFrameLoader', array('action' => $action));

if(isset($_SESSION['onLoginForwardToAction']) && is_int($_SESSION['onLoginForwardToAction'])) {
	$initMainframeAction = $_SESSION['onLoginForwardToAction'];
	unset($_SESSION['onLoginForwardToAction']);
} else {
	$initMainframeAction = 2; // welcome.static
}

?>
<!DOCTYPE html>
<html <?php echo (isset($modx_textdir) && $modx_textdir ? 'dir="rtl" lang="' : 'lang="') . $mxla . '" xml:lang="' . $mxla . '"'; ?>>
<head>
	<title><?php echo $site_name ?>- (MODX CMS Manager)</title>
	<meta name="viewport" content="width=device-width, minimum-scale=0.25, maximum-scale=1.0, initial-scale=0.8">
	<meta http-equiv="Content-Type" content="text/html; charset=<?php echo $modx_manager_charset ?>" />
	<link rel="stylesheet" type="text/css" href="media/style/common/font-awesome/css/font-awesome.min.css" />
	<link rel="stylesheet" type="text/css" href="media/style/<?php echo $modx->config['manager_theme']; ?>/style.css" />
	<style>
		#tree { width: 320px }
		#main, #resizer { left: 320px }
	</style>
	<script src="media/script/jquery/jquery.min.js"></script>
	<script type="text/javascript">
		var modx = {
			MGR_DIR: "<?php echo MGR_DIR ?>",
			manager_theme: "<?php echo $modx->config['manager_theme'] ?>",
			manager_layout: "<?php echo $manager_layout ?>",
			lang: {
				confirm_remove_locks: "<?php echo $_lang['confirm_remove_locks'] ?>",
				working: "<?php echo $_lang['working'] ?>",
				loading_doc_tree: "<?php echo $_lang['loading_doc_tree'] ?>",
				loading_menu: "<?php echo $_lang['loading_menu'] ?>"
			}
		};
	</script>
	<script src="media/style/<?php echo $modx->config['manager_theme']; ?>/modx.js"></script>
</head>
<body id="frameset" class="tree-show">
<div id="resizer"><a id="hideMenu"> <i class="fa fa-chevron-right"></i> </a></div>
<div id="mainMenu" name="mainMenu">
	<div class="col float-left">
		<!--		<form name="menuForm" action="l4mnu.php">
			<input type="hidden" name="sessToken" id="sessTokenInput" value="<?php echo md5(session_id()); ?>" />-->
		<?php include('mainmenu.php'); ?>
		<!--		</form>-->
	</div>
	<div class="col float-left">
		<div id="statusbar">
			<div id="buildText"></div>
			<div id="workText"></div>
		</div>
	</div>
	<div class="col float-right">
		<ul class="nav">
			<li>
				<a href="../" target="_blank" title="<?php echo $_lang['preview'] ?>" onclick="setLastClickedElement(0,0);this.blur();"><i class="fa fa-home fa-2x"></i></a>
			</li>
			<li class="dropdown"><a class="dropdown-toggle"> <i class="fa fa-sliders fa-2x" aria-hidden="true"></i> </a>
				<ul class="dropdown-menu">
					<?php if($modx->hasPermission('settings')) { ?>
						<li>
							<a href="index.php?a=17" target="main" onclick="setLastClickedElement(0,0);this.blur();"><i class="fa fa-cog fw"></i><?php echo $_lang['edit_settings'] ?>
							</a></li>
					<?php } ?>
					<?php if($modx->hasPermission('view_eventlog')) { ?>
						<li>
							<a href="index.php?a=70" target="main" onclick="setLastClickedElement(0,0);this.blur();"><i class="fa fa-calendar"></i><?php echo $_lang['site_schedule'] ?>
							</a></li>
					<?php } ?>
					<?php if($modx->hasPermission('view_eventlog')) { ?>
						<li>
							<a href="index.php?a=114" target="main" onclick="setLastClickedElement(0,0);this.blur();"><i class="fa fa-exclamation-triangle"></i><?php echo $_lang['eventlog_viewer'] ?>
							</a></li>
					<?php } ?>
					<?php if($modx->hasPermission('logs')) { ?>
						<li>
							<a href="index.php?a=13" target="main" onclick="setLastClickedElement(0,0);this.blur();"><i class="fa fa-user-secret"></i><?php echo $_lang['view_logging'] ?>
							</a></li>
						<li>
							<a href="index.php?a=53" target="main" onclick="setLastClickedElement(0,0);this.blur();"><i class="fa fa-info-circle"></i><?php echo $_lang['view_sysinfo'] ?>
							</a></li>
					<?php } ?>
					<?php if($modx->hasPermission('help')) { ?>
						<li>
							<a href="index.php?a=9#version_notices" target="main" onclick="setLastClickedElement(0,0);this.blur();"><i class="fa fa-question-circle"></i><?php echo $_lang['help'] ?>
							</a></li>
					<?php } ?>
				</ul>
			</li>
			<li class="dropdown account"><a class="dropdown-toggle">
					<?php
					$user = $modx->getUserInfo($modx->getLoginUserID());
					?>
					<div class="username"><?php echo $user['username'] ?></div>
					<?php if($user['photo']) { ?>
						<div class="icon" style="background-image: url(<?php echo MODX_SITE_URL . $user['photo'] ?>);"></div>
					<?php } else { ?>
						<div class="icon"><i class="fa fa-user-circle fa-2x" aria-hidden="true"></i></div>
					<?php } ?>
				</a>
				<ul class="dropdown-menu">
					<li id="tocText"></li>
					<?php if($modx->hasPermission('change_password')) { ?>
						<li>
							<a onclick="this.blur();" href="index.php?a=28" target="main"><i class="fa fa-lock"></i><?php echo $_lang['change_password'] ?>
							</a></li>
					<?php } ?>
					<li>
						<a href="index.php?a=8" target="_top"><i class="fa fa-sign-out" aria-hidden="true"></i><?php echo $_lang['logout'] ?>
						</a></li>
					<?php
					$style = $modx->config['settings_version'] != $modx->getVersionData('version') ? 'style="color:#ffff8a;"' : '';
					$version = stristr($modx->config['settings_version'], 'd') === FALSE ? 'MODX Evolution' : 'MODX EVO Custom';
					?>
					<?php
					echo sprintf('<li><span title="%s &ndash; %s" %s>' . $version . ' %s</span></li>', $site_name, $modx->getVersionData('full_appname'), $style, $modx->config['settings_version']);
					?>
				</ul>
			</li>
		</ul>
	</div>
	<div class="col float-right">
		<div id="searchform">
			<form action="index.php?a=71#results" method="post" target="main">
				<input type="hidden" value="Search" name="submitok" />
				<input type="text" name="searchid" size="25" class="form-control input-sm" placeholder="<?php echo $_lang['search'] ?>">
			</form>
		</div>
	</div>
</div>
<div id="tree">
	<iframe name="tree" src="index.php?a=1&amp;f=tree" scrolling="no" frameborder="0"></iframe>
</div>
<div id="main">
	<iframe name="main" id="mainframe" src="index.php?a=<?php echo $initMainframeAction; ?>" scrolling="auto" frameborder="0" onload="modx.stopWork();modx.scrollWork();"></iframe>
</div>
<?php
$modx->invokeEvent('OnManagerFrameLoader', array('action' => $action));
?>
</body>
</html>
