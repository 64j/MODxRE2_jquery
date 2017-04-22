<?php
if(IN_MANAGER_MODE != "true") {
	die("<b>INCLUDE_ORDERING_ERROR</b><br /><br />Please use the MODX Content Manager instead of accessing this file directly.");
}
if(!array_key_exists('mail_check_timeperiod', $modx->config) || !is_numeric($modx->config['mail_check_timeperiod'])) {
	$modx->config['mail_check_timeperiod'] = 5;
}
$modx_textdir = isset($modx_textdir) ? $modx_textdir : null;
$mxla = $modx_lang_attribute ? $modx_lang_attribute : 'en';

if(!isset($modx->config['manager_tree_width'])) {
	$modx->config['manager_tree_width'] = '320';
}

$useEVOModal = '';
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.1//EN" "http://www.w3.org/TR/xhtml11/DTD/xhtml11.dtd">
<html <?php echo ($modx_textdir ? 'dir="rtl" lang="' : 'lang="') . $mxla . '" xml:lang="' . $mxla . '"'; ?>>
<head>
	<meta http-equiv="Content-Type" content="text/html; charset=<?php echo $modx_manager_charset ?>" />
	<title>nav</title>
	<link rel="stylesheet" type="text/css" href="media/style/common/font-awesome/css/font-awesome.min.css" />
	<link rel="stylesheet" type="text/css" href="media/style/<?php echo $modx->config['manager_theme']; ?>/style.css" />
	<script src="media/script/jquery/jquery.min.js" type="text/javascript"></script>
	<script type="text/javascript">
		/*
		 * Small script to keep session alive in MODX
		 */
		function keepMeAlive() {
			var sessionJSON = jQuery.get('includes/session_keepalive.php?tok=' + document.getElementById('sessTokenInput').value + '&o=' + Math.random(), function(data) {
				resp = Json.evaluate(sessionResponse);
				if(resp.status != 'ok') {
					window.location.href = 'index.php?a=8';
				}
			});
		}
		window.setInterval(keepMeAlive, 1000 * 600); // Update session every 10min

		// TREE FUNCTIONS - FRAME
		// These functions affect the tree frame and any items that may be pointing to the tree.
		var currentFrameState = 'open';
		var defaultFrameWidth = '<?php echo !$modx_textdir ? '260,*' : '*,260'?>';
		var userDefinedFrameWidth = '<?php echo !$modx_textdir ? '260,*' : '*,260'?>';

		var workText;
		var buildText;

		// Create the AJAX mail update object before requesting it
		var updateMailerAjx = jQuery.post('index.php', {updateMsgCount: true}, function(data) {
			showResponse(data)
		});

		function updateMail(now) {
			try {
				// if 'now' is set, runs immediate ajax request (avoids problem on initial loading where periodical waits for time period before making first request)
				if(now)
					updateMailerAjx.request();
				return false;
			} catch(oException) {
				// Delay first run until we're ready...
				xx = setTimeout(function() {
					updateMail(true)
				}, 1000 * 60);
			}
		}

		function showResponse(request) {
			var counts = request.split(',');
			var elm = document.getElementById('msgCounter');
			if(elm) elm.innerHTML = '(' + counts[0] + ' / ' + counts[1] + ')';
			var elm = document.getElementById('newMail');
			if(elm) elm.style.display = counts[0] > 0 ? 'inline' : 'none';
		}

		jQuery(window).on('load', function() {
			updateMail(true); // First run update
			// :TODO updateMail.periodical(<?php echo $modx->config['mail_check_timeperiod'] * 1000 ?>, '', true); // Periodical Updater
			if(top.__hideTree) {
				// display toc icon
				var elm = document.getElementById('tocText');
				if(elm) elm.innerHTML = "<a href='#' onclick='document.mainMenu.defaultTreeFrame();'><?php echo $_lang['show_tree']?></a>";
			}
		});

		// TREE FUNCTIONS - Expand/ Collapse
		// These functions affect the expanded/collapsed state of the tree and any items that may be pointing to it
		function expandTree() {
			try {
				parent.tree.d.openAll();  // dtree
			} catch(oException) {
				zz = window.setTimeout('expandTree()', 1000);
			}
		}

		function collapseTree() {
			try {
				parent.tree.d.closeAll();  // dtree
			} catch(oException) {
				yy = window.setTimeout('collapseTree()', 1000);
			}
		}

		// GENERAL FUNCTIONS - Refresh
		// These functions are used for refreshing the tree or menu
		function reloadtree() {
			var elm = document.getElementById("buildText");
			if(elm) {
				elm.innerHTML = '<img src="<?php echo $_style['icons_loading_doc_tree']?>" width="16" height="16" /><?php echo $_lang['loading_doc_tree']?>';
				elm.style.display = 'block';
			}
//			top.tree.saveFolderState(); // save folder state
//			setTimeout('top.tree.restoreTree()', 200);
		}

		function reloadmenu() {
			<?php if($manager_layout == 0) { ?>
			var elm = document.getElementById("buildText");
			if(elm) {
				elm.innerHTML = '<img src="<?php echo $_style['icons_working']?>" width="16" height="16" /><?php echo $_lang['loading_menu']?>';
				elm.style.display = 'block';
			}
			parent.mainMenu.location.reload();
			<?php } ?>
		}

		function startrefresh(rFrame) {
			if(rFrame == 1) {
				top.tree.restoreTree();
				//x = window.setTimeout('reloadtree()', 500);
			}
			if(rFrame == 2) {
				parent.mainMenu.location.reload();
				//x = window.setTimeout('reloadmenu()', 500);
			}
			if(rFrame == 9) {
				top.tree.restoreTree();
				parent.mainMenu.location.reload();
//				x = window.setTimeout('reloadmenu()', 500);
//				y = window.setTimeout('reloadtree()', 500);
			}
			if(rFrame == 10) {
				window.top.location.href = "../<?php echo MGR_DIR;?>";
			}
		}
	</script>
</head>
<body id="topMenu" class="<?php echo $modx_textdir ? 'rtl' : 'ltr' ?>">
<?php
// invoke OnManagerTopPrerender event
$evtOut = $modx->invokeEvent('OnManagerTopPrerender', $_REQUEST);
if(is_array($evtOut)) {
	echo implode("\n", $evtOut);
}
?>

<div class="mainmenu">
	<form name="menuForm" action="l4mnu.php">
		<input type="hidden" name="sessToken" id="sessTokenInput" value="<?php echo md5(session_id()); ?>" />
		<div id="Navcontainer">
			<div id="divNav">
				<?php include('mainmenu.php'); ?>
			</div>
		</div>
	</form>
	<div id="topbar">
		<div id="topbar-container">
			<div id="statusbar">
				<span id="buildText"></span>
				<span id="workText"></span>
			</div>
		</div>
	</div>
</div>
<div id="supplementalNav">
	<ul>
		<li class="account">
			<a class="account-dropdown-toggle dropdown-toggle">
				<?php
				$user = $modx->getUserInfo($modx->getLoginUserID());
				?>
				<div class="username"><?php echo $user['username'] ?></div>
				<?php if($user['photo']) { ?>
					<div class="icon" style="background-image: url(<?php echo MODX_SITE_URL . $user['photo'] ?>);"></div>
				<?php } else { ?>
					<div class="icon">
						<i class="fa fa-user-circle" aria-hidden="true"></i>
					</div>
				<?php } ?>
			</a>
			<ul class="account-dropdown-menu dropdown-menu">
				<li id="tocText"></li>

				<?php if($modx->hasPermission('change_password')) { ?>
					<li>
						<a onclick="this.blur();" href="index.php?a=28" target="main"><i class="fa fa-lock"></i><?php echo $_lang['change_password'] ?>
						</a>
					</li>
				<?php } ?>

				<li>
					<a href="index.php?a=8" target="_top"><i class="fa fa-sign-out" aria-hidden="true"></i><?php echo $_lang['logout'] ?>
					</a>
				</li>

				<?php $style = $modx->config['settings_version'] != $modx->getVersionData('version') ? 'style="color:#ffff8a;"' : '';
				$version = stristr($modx->config['settings_version'], 'd') === FALSE ? 'MODX Evolution' : 'MODX EVO Custom';
				?>
				<?php
				echo sprintf('<li><span title="%s &ndash; %s" %s>' . $version . ' %s</span></li>', $site_name, $modx->getVersionData('full_appname'), $style, $modx->config['settings_version']);
				?>
			</ul>
		</li>
		<li>
			<a class="dropdown-toggle">
				<i class="fa fa-sliders" aria-hidden="true"></i>
			</a>
			<ul class="dropdown-menu">
				<?php if($modx->hasPermission('settings')) { ?>
					<li>
						<a href="index.php?a=17" target="main" onclick="top.modx.tree.setLastClickedElement(0,0);this.blur();"><i class="fa fa-cog fw"></i><?php echo $_lang['edit_settings'] ?>
						</a>
					</li>
				<?php } ?>

				<?php if($modx->hasPermission('view_eventlog')) { ?>
					<li>
						<a href="index.php?a=70" target="main" onclick="top.modx.tree.setLastClickedElement(0,0);this.blur();"><i class="fa fa-calendar"></i><?php echo $_lang['site_schedule'] ?>
						</a>
					</li>
				<?php } ?>

				<?php if($modx->hasPermission('view_eventlog')) { ?>
					<li>
						<a href="index.php?a=114" target="main" onclick="top.modx.tree.setLastClickedElement(0,0);this.blur();"><i class="fa fa-exclamation-triangle"></i><?php echo $_lang['eventlog_viewer'] ?>
						</a>
					</li>
				<?php } ?>

				<?php if($modx->hasPermission('logs')) { ?>
					<li>
						<a href="index.php?a=13" target="main" onclick="top.modx.tree.setLastClickedElement(0,0);this.blur();"><i class="fa fa-user-secret"></i><?php echo $_lang['view_logging'] ?>
						</a>
					</li>
					<li>
						<a href="index.php?a=53" target="main" onclick="top.modx.tree.setLastClickedElement(0,0);this.blur();"><i class="fa fa-info-circle"></i><?php echo $_lang['view_sysinfo'] ?>
						</a>
					</li>
				<?php } ?>

				<?php if($modx->hasPermission('help')) { ?>
					<li>
						<a href="index.php?a=9#version_notices" target="main" onclick="top.modx.tree.setLastClickedElement(0,0);this.blur();"><i class="fa fa-question-circle"></i><?php echo $_lang['help'] ?>
						</a>
					</li>
				<?php } ?>

			</ul>
		</li>
		<li>
			<a href="../" target="_blank" title="<?php echo $_lang['preview'] ?>" onclick="top.modx.tree.setLastClickedElement(0,0);this.blur();"><i class="fa fa-home"></i></a>
		</li>
	</ul>
</div>
<div id="searchform">
	<form action="index.php?a=71#results" method="post" target="main">
		<input type="hidden" value="Search" name="submitok" />
		<input type="text" name="searchid" size="25" class="form-control input-sm" autocomplete="off" placeholder="<?php echo $_lang['search'] ?>">
	</form>
</div>
<div id="menuSplitter"></div>
</body>
</html>