<?php

if(IN_MANAGER_MODE != "true") {
	die("<b>INCLUDE_ORDERING_ERROR</b><br /><br />Please use the MODX Content Manager instead of accessing this file directly.");
}
header("X-XSS-Protection: 0");

$_SESSION['browser'] = (strpos($_SERVER['HTTP_USER_AGENT'], 'MSIE 1') !== false) ? 'legacy_IE' : 'modern';

$modx->invokeEvent('OnManagerPreFrameLoader', array('action' => $action));

if(isset($_SESSION['onLoginForwardToAction']) && is_int($_SESSION['onLoginForwardToAction'])) {
	$initMainframeAction = $_SESSION['onLoginForwardToAction'];
	unset($_SESSION['onLoginForwardToAction']);
} else {
	$initMainframeAction = 2; // welcome.static
}

$modx_textdir = isset($modx_textdir) ? $modx_textdir : null;
$mxla = $modx_lang_attribute ? $modx_lang_attribute : 'en';

$unlockTranslations = array(
	'msg' => $_lang["unlock_element_id_warning"],
	'type1' => $_lang["lock_element_type_1"],
	'type2' => $_lang["lock_element_type_2"],
	'type3' => $_lang["lock_element_type_3"],
	'type4' => $_lang["lock_element_type_4"],
	'type5' => $_lang["lock_element_type_5"],
	'type6' => $_lang["lock_element_type_6"],
	'type7' => $_lang["lock_element_type_7"],
	'type8' => $_lang["lock_element_type_8"]
);

foreach($unlockTranslations as $key => $value) $unlockTranslations[$key] = iconv($modx->config["modx_charset"], "utf-8", $value);

?>
<!DOCTYPE html>
<html <?php echo (isset($modx_textdir) && $modx_textdir ? 'dir="rtl" lang="' : 'lang="') . $mxla . '" xml:lang="' . $mxla . '"'; ?>>
<head>
	<title><?php echo $site_name ?>- (MODX CMS Manager)</title>
	<meta name="viewport" content="width=device-width, minimum-scale=0.25, maximum-scale=1.0, initial-scale=0.8">
	<meta http-equiv="Content-Type" content="text/html; charset=<?php echo $modx_manager_charset ?>" />
	<link rel="stylesheet" type="text/css" href="media/style/common/font-awesome/css/font-awesome.min.css" />
	<link rel="stylesheet" type="text/css" href="media/style/<?php echo $modx->config['manager_theme']; ?>/style.css" />
	<script src="media/script/jquery/jquery.min.js"></script>
	<script type="text/javascript">

		// GLOBAL variable modx
		var modx = {
			site_start: "<?php echo $modx->config['site_start']?>",
			MGR_DIR: "<?php echo MGR_DIR ?>",
			openedArray: [{
				<?php
				$opened = array_filter(array_map('intval', explode('|', $_SESSION['openedArray'])));
				foreach($opened as $key => $item) {
					printf('"%d": "1"', $item);
					if(count($opened) - 1 > $key) {
						print(',');
					}
				}
				?>

			}],
			manager: {
				layout: "<?php echo $manager_layout ?>",
				textdir: "<?php echo $modx_textdir ?>",
				theme: "<?php echo $modx->config['manager_theme'] ?>",
				which_browser: "<?php echo $which_browser; ?>"
			},
			lang: {
				already_deleted: "<?php echo $_lang['already_deleted']; ?>",
				collapse_tree: "<?php echo $_lang['collapse_tree']; ?>",
				confirm_delete_resource: "<?php echo $_lang['confirm_delete_resource']; ?>",
				confirm_empty_trash: "<?php echo $_lang['confirm_empty_trash']; ?>",
				confirm_publish: "<?php echo $_lang['confirm_publish']; ?>",
				confirm_remove_locks: "<?php echo $_lang['confirm_remove_locks'] ?>",
				confirm_resource_duplicate: "<?php echo $_lang['confirm_resource_duplicate'] ?>",
				confirm_undelete: "<?php echo $_lang['confirm_undelete']; ?>",
				confirm_unpublish: "<?php echo $_lang['confirm_unpublish']; ?>",
				empty_recycle_bin: "<?php echo $_lang['empty_recycle_bin']; ?>",
				empty_recycle_bin_empty: "<?php echo addslashes($_lang['empty_recycle_bin_empty']); ?>",
				expand_tree: "<?php echo $_lang['expand_tree']; ?>",
				loading_doc_tree: "<?php echo $_lang['loading_doc_tree'] ?>",
				loading_menu: "<?php echo $_lang['loading_menu'] ?>",
				not_deleted: "<?php echo $_lang['not_deleted']; ?>",
				unable_set_link: "<?php echo $_lang['unable_set_link']; ?>",
				unable_set_parent: "<?php echo $_lang['unable_set_parent']; ?>",
				working: "<?php echo $_lang['working'] ?>"
			},
			style: {
				collapse_tree: "<?php echo addslashes($_style['collapse_tree']) ?>",
				empty_recycle_bin: "<?php echo addslashes($_style['empty_recycle_bin']) ?>",
				empty_recycle_bin_empty: "<?php echo addslashes($_style['empty_recycle_bin_empty']) ?>",
				expand_tree: "<?php echo addslashes($_style['expand_tree']) ?>",
				tree_folder_new: "<?php echo addslashes($_style['tree_folder_new']) ?>",
				tree_folder_secure: "<?php echo addslashes($_style['tree_folder_secure']) ?>",
				tree_folderopen: "<?php echo addslashes($_style['tree_folderopen']) ?>",
				tree_folderopen_new: "<?php echo addslashes($_style['tree_folderopen_new']) ?>",
				tree_folderopen_secure: "<?php echo addslashes($_style['tree_folderopen_secure']) ?>",
				tree_minusnode: "<?php echo addslashes($_style["tree_minusnode"]) ?>",
				tree_plusnode: "<?php echo addslashes($_style['tree_plusnode']) ?>"
			},
			permission: {
				assets_images: "<?php echo $modx->hasPermission('assets_images') ? 1 : 0; ?>",
				delete_document: "<?php echo $modx->hasPermission('delete_document') ? 1 : 0; ?>",
				edit_chunk: "<?php echo $modx->hasPermission('edit_chunk') ? 1 : 0; ?>",
				edit_plugin: "<?php echo $modx->hasPermission('edit_plugin') ? 1 : 0; ?>",
				edit_snippet: "<?php echo $modx->hasPermission('edit_snippet') ? 1 : 0; ?>",
				edit_template: "<?php echo $modx->hasPermission('edit_template') ? 1 : 0; ?>",
				new_document: "<?php echo $modx->hasPermission('new_document') ? 1 : 0; ?>",
				publish_document: "<?php echo $modx->hasPermission('publish_document') ? 1 : 0; ?>"
			},
			tree_page_click: "<?php echo(!empty($modx->config['tree_page_click']) ? $modx->config['tree_page_click'] : '27'); ?>",
			lockedElementsTranslation: <?php echo json_encode($unlockTranslations); ?>
		};
	</script>
	<script src="media/style/<?php echo $modx->config['manager_theme']; ?>/modx.js"></script>
</head>
<body id="frameset" class="tree-show">
<div id="mainMenu">
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
	<!-- <iframe name="tree" src="index.php?a=1&amp;f=tree" scrolling="no" frameborder="0"></iframe> -->
	<?php include('tree.php'); ?>
</div>
<div id="main">
	<iframe name="main" id="mainframe" src="index.php?a=<?php echo $initMainframeAction; ?>" scrolling="auto" frameborder="0" onload="modx.stopWork();modx.scrollWork();"></iframe>
</div>
<div id="resizer"><a id="hideMenu"> <i class="fa fa-chevron-right"></i> </a></div>
<?php
$modx->invokeEvent('OnManagerFrameLoader', array('action' => $action));
?>
</body>
</html>
