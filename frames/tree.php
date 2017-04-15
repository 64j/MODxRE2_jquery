<?php
if(IN_MANAGER_MODE != "true") {
	die("<b>INCLUDE_ORDERING_ERROR</b><br /><br />Please use the MODX Content Manager instead of accessing this file directly.");
}
$modx->config['mgr_jquery_path'] = 'media/script/jquery/jquery.min.js';
$modx_textdir = isset($modx_textdir) ? $modx_textdir : null;
$mxla = $modx_lang_attribute ? $modx_lang_attribute : 'en';
?><!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.1//EN" "http://www.w3.org/TR/xhtml11/DTD/xhtml11.dtd">
<html <?php echo ($modx_textdir ? 'dir="rtl" lang="' : 'lang="') . $mxla . '" xml:lang="' . $mxla . '"'; ?>>
<head>
	<title>Document Tree</title>
	<meta http-equiv="Content-Type" content="text/html; charset=<?php echo $modx_manager_charset; ?>" />
	<link rel="stylesheet" type="text/css" href="media/style/<?php echo $modx->config['manager_theme']; ?>/style.css" />
	<link rel="stylesheet" href="media/style/common/font-awesome/css/font-awesome.min.css" />
	<?php echo sprintf('<script src="%s" type="text/javascript"></script>' . "\n", $modx->config['mgr_jquery_path']); ?>
	<script type="text/javascript">

		// jQuery.noConflict();

		// preload images
		var i = new Image(18, 18);
		i.src = "<?php echo $_style["tree_page"]?>";
		i = new Image(18, 18);
		i.src = "<?php echo isset($_style["tree_globe"]) ? $_style["tree_globe"] : $_style["tree_page"]; ?>";
		i = new Image(18, 18);
		i.src = "<?php echo $_style["tree_minusnode"]?>";
		i = new Image(18, 18);
		i.src = "<?php echo $_style["tree_plusnode"]?>";
		i = new Image(18, 18);
		i.src = "<?php echo isset($_style["tree_folderopen"]) ? $_style["tree_folderopen"] : $_style["tree_page"]; ?>";
		i = new Image(18, 18);
		i.src = "<?php echo isset($_style["tree_folder"]) ? $_style["tree_folder"] : $_style["tree_page"]; ?>";


		var rpcNode = null;
		var ca = "open";
		var selectedObject = 0;
		var selectedObjectDeleted = 0;
		var selectedObjectName = "";
		var _rc = 0; // added to fix onclick body event from closing ctx menu

		<?php
		echo "var openedArray = new Array();\n";
		if(isset($_SESSION['openedArray'])) {
			$opened = array_filter(array_map('intval', explode('|', $_SESSION['openedArray'])));

			foreach($opened as $item) {
				printf("openedArray[%d] = 1;\n", $item);
			}
		}
		?>

		// return window dimensions in array
		function getWindowDimension() {
			var width = 0;
			var height = 0;

			if(typeof( window.innerWidth ) == 'number') {
				width = window.innerWidth;
				height = window.innerHeight;
			} else if(document.documentElement &&
				( document.documentElement.clientWidth ||
				document.documentElement.clientHeight )) {
				width = document.documentElement.clientWidth;
				height = document.documentElement.clientHeight;
			}
			else if(document.body &&
				( document.body.clientWidth || document.body.clientHeight )) {
				width = document.body.clientWidth;
				height = document.body.clientHeight;
			}

			return {'width': width, 'height': height};
		}

		function resizeTree() {
			// get window width/height
			var win = getWindowDimension();

			// set tree height
			jQuery('#treeHolder').css({
				width: (win['width'] - 20) + 'px',
				height: (win['height'] - jQuery('#treeHolder').offset().top - 6) + 'px',
				overflow: 'auto'
			})
		}

		function getScrollY() {
			var scrOfY = 0;
			if(typeof( window.pageYOffset ) == 'number') {
				//Netscape compliant
				scrOfY = window.pageYOffset;
			} else if(document.body && ( document.body.scrollLeft || document.body.scrollTop )) {
				//DOM compliant
				scrOfY = document.body.scrollTop;
			} else if(document.documentElement &&
				(document.documentElement.scrollTop )) {
				//IE6 standards compliant mode
				scrOfY = document.documentElement.scrollTop;
			}
			return scrOfY;
		}

		function showPopup(id, title, pub, del, folder, e) {
			var x, y;

			var mnu = document.getElementById('mx_contextmenu');
			var permpub = <?php echo $modx->hasPermission('publish_document') ? 1 : 0; ?>;
			var permdel = <?php echo $modx->hasPermission('delete_document') ? 1 : 0; ?>;

			if(permpub == 1) {
				jQuery('#item9').show();
				jQuery('#item10').show();
				if(pub == 1) jQuery('#item9').hide();
				else       jQuery('#item10').hide();
			} else {
				if(jQuery('#item5') != null) jQuery('#item5').hide();
			}

			if(permdel == 1) {
				jQuery('#item4').show();
				jQuery('#item8').show();
				if(del == 1) {
					jQuery('#item4').hide();
					jQuery('#item9').hide();
					jQuery('#item10').hide();
				}
				else jQuery('#item8').hide();
			}
			if(folder == 1) jQuery('#item11').show();
			else          jQuery('#item11').hide();

			var bodyHeight = parseInt(document.body.offsetHeight);
			var bodyWidth = parseInt(document.body.offsetWidth);
			x = e.clientX > 0 ? e.clientX : e.pageX;
			if(x + mnu.offsetWidth > bodyWidth) {
				// make sure context menu is within frame
				x = Math.max(x - ((x + mnu.offsetWidth) - bodyWidth + 5), 0);
			}
			y = e.clientY > 0 ? e.clientY : e.pageY;
			y = getScrollY() + (y / 2);
			if(y + mnu.offsetHeight > bodyHeight) {
				// make sure context menu is within frame
				y = y - ((y + mnu.offsetHeight) - bodyHeight + 5);
			}
			itemToChange = id;
			selectedObjectName = title;
			dopopup(x + 5, y);
			e.cancelBubble = true;
			return false;
		}

		function dopopup(x, y) {
			if(selectedObjectName.length > 20) {
				selectedObjectName = selectedObjectName.substr(0, 20) + "...";
			}
			jQuery('#mx_contextmenu').css({
				left: x<?php echo $modx_textdir ? '-190' : '';?>,
				top: y,
				visibility: 'visible'
			});
			jQuery("#nameHolder").html(selectedObjectName);
			_rc = 1;
			setTimeout("_rc = 0;", 100);
		}

		function hideMenu() {
			if(_rc) return false;
			jQuery('#mx_contextmenu').css('visibility', 'hidden');
		}

		function toggleNode(node, indent, parent, expandAll, privatenode) {
			privatenode = (!privatenode || privatenode == '0') ? '0' : '1';
			rpcNode = jQuery(node.parentNode.lastChild).get(0);

			var rpcNodeText;
			var loadText = "<?php echo $_lang['loading_doc_tree'];?>";

			var signImg = document.getElementById("s" + parent);
			var folderImg = document.getElementById("f" + parent);

			if(rpcNode.style.display != 'block') {
				// expand
				if(signImg && signImg.src.indexOf('<?php echo $_style['tree_plusnode']?>') > -1) {
					signImg.src = '<?php echo $_style["tree_minusnode"]; ?>';
					folderImg.src = (privatenode == '0') ? '<?php echo $_style["tree_folderopen"]; ?>' : '<?php echo $_style["tree_folderopen_secure"]; ?>';
				}

				rpcNodeText = rpcNode.innerHTML;

				if(rpcNodeText == "" || rpcNodeText.indexOf(loadText) > 0) {
					var i, spacer = '';
					for(i = 0; i <= indent + 1; i++) spacer += '&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;';
					rpcNode.style.display = 'block';
					//Jeroen set opened
					openedArray[parent] = 1;
					//Raymond:added getFolderState()
					var folderState = getFolderState();
					rpcNode.innerHTML = "<span class='emptyNode' style='white-space:nowrap;'>" + spacer + "&nbsp;&nbsp;&nbsp;" + loadText + "...<\/span>";
					jQuery.get('index.php?a=1&f=nodes&indent=' + indent + '&parent=' + parent + '&expandAll=' + expandAll + folderState, function(data) {
						rpcLoadData(data)
					})
				} else {
					rpcNode.style.display = 'block';
					//Jeroen set opened
					openedArray[parent] = 1;
				}
			}
			else {
				// collapse
				if(signImg && signImg.src.indexOf('<?php echo $_style["tree_minusnode"]; ?>') > -1) {
					signImg.src = '<?php echo $_style["tree_plusnode"]; ?>';
					folderImg.src = (privatenode == '0') ? '<?php echo $_style["tree_folder"]; ?>' : '<?php echo $_style["tree_folder_secure"]; ?>';
				}
				//rpcNode.innerHTML = '';
				rpcNode.style.display = 'none';
				openedArray[parent] = 0;
			}
		}

		function rpcLoadData(response) {
			if(rpcNode != null) {
				rpcNode.innerHTML = typeof response == 'object' ? response.responseText : response;
				rpcNode.style.display = 'block';
				rpcNode.loaded = true;
				jQuery(parent.document).find("#buildText").html('').hide();
				// check if bin is full
				if(rpcNode.id == 'treeRoot') {
					if(jQuery('#binFull').length) showBinFull();
					else showBinEmpty();
				}

				// check if our payload contains the login form :)
				if(jQuery('#mx_loginbox').length) {
					// yep! the session has timed out
					rpcNode.innerHTML = '';
					top.location = 'index.php';
				}
			}
		}

		function expandTree() {
			rpcNode = jQuery('#treeRoot').get(0);
			jQuery.get('index.php?a=1&f=nodes&indent=1&parent=0&expandAll=1', function(data) {
				rpcLoadData(data)
			})
		}

		function collapseTree() {
			rpcNode = jQuery('#treeRoot').get(0);
			jQuery.get('index.php?a=1&f=nodes&indent=1&parent=0&expandAll=0', function(data) {
				rpcLoadData(data)
			})
		}

		// new function used in body onload
		function restoreTree() {
			rpcNode = jQuery('#treeRoot').get(0);
			jQuery.get('index.php?a=1&f=nodes&indent=1&parent=0&expandAll=2', function(data) {
				rpcLoadData(data)
			})
		}

		function setSelected(elSel) {
			var all = document.getElementsByTagName("SPAN");
			var l = all.length;

			for(var i = 0; i < l; i++) {
				el = all[i];
				cn = el.className;
				if(cn == "treeNodeSelected") {
					el.className = "treeNode";
				}
			}
			elSel.className = "treeNodeSelected";
		}

		function setHoverClass(el, dir) {
			if(el.className != "treeNodeSelected") {
				if(dir == 1) {
					el.className = "treeNodeHover";
				} else {
					el.className = "treeNode";
				}
			}
		}

		// set Context Node State
		function setCNS(n, b) {
			if(b == 1) {
				n.style.backgroundColor = "beige";
			} else {
				n.style.backgroundColor = "";
			}
		}

		function updateTree() {
			rpcNode = jQuery('#treeRoot').get(0);
			treeParams = 'a=1&f=nodes&indent=1&parent=0&expandAll=2&dt=' + document.sortFrm.dt.value + '&tree_sortby=' + document.sortFrm.sortby.value + '&tree_sortdir=' + document.sortFrm.sortdir.value + '&tree_nodename=' + document.sortFrm.nodename.value;
			jQuery.get('index.php?' + treeParams, function(data) {
				rpcLoadData(data)
			})
		}

		<?php
		// Prepare lang-strings
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

		var lockedElementsTranslation = <?php echo json_encode($unlockTranslations); ?>;

		function unlockElement(type, id, domEl) {
			var msg = lockedElementsTranslation.msg.replace('[+id+]', id).replace('[+element_type+]', lockedElementsTranslation['type' + type]);
			if(confirm(msg) == true) {
				jQuery.get('index.php?a=67&type=' + type + '&id=' + id, function(data) {
					if(data == 1) {
						jQuery(domEl).fadeOut();
					}
					else alert(data);
				});
			}
		}

		function emptyTrash() {
			if(confirm("<?php echo $_lang['confirm_empty_trash']; ?>") == true) {
				top.main.document.location.href = "index.php?a=64";
			}
		}

		currSorterState = "none";
		function showSorter() {
			if(currSorterState == "none") {
				currSorterState = "block";
				document.getElementById('floater').style.display = currSorterState;
			} else {
				currSorterState = "none";
				document.getElementById('floater').style.display = currSorterState;
			}
		}

		function treeAction(e, id, name, treedisp_children) {
			if(ca == "move") {
				try {
					parent.main.setMoveValue(id, name);
				} catch(oException) {
					alert('<?php echo $_lang['unable_set_parent']; ?>');
				}
			}
			if(ca == "open" || ca == "") {
				if(id == 0) {
					// do nothing?
					parent.main.location.href = "index.php?a=2";
				} else {
					// parent.main.location.href="index.php?a=3&id=" + id + getFolderState(); //just added the getvar &opened=
					var href = '';
					setLastClickedElement(7, id);
					if(treedisp_children == 0) {
						href = "index.php?a=3&r=1&id=" + id + getFolderState();
					} else {
						href = "index.php?a=<?php echo(!empty($modx->config['tree_page_click']) ? $modx->config['tree_page_click'] : '27'); ?>&r=1&id=" + id; // edit as default action
					}
					if(e.shiftKey) {
						window.getSelection().removeAllRanges(); // Remove unnessecary text-selection
						randomNum = Math.floor((Math.random() * 999999) + 1);
						window.open(href, 'res' + randomNum, 'width=960,height=720,top=' + ((screen.height - 720) / 2) + ',left=' + ((screen.width - 960) / 2) + ',toolbar=no,location=no,directories=no,status=no,menubar=no,scrollbars=yes,resizable=no');
						top.modx.reloadtree(); // Show updated locks as &r=1 will not work in popup
					} else {
						parent.main.location.href = href;
					}
				}
			}
			if(ca == "parent") {
				try {
					parent.main.setParent(id, name);
				} catch(oException) {
					alert('<?php echo $_lang['unable_set_parent']; ?>');
				}
			}
			if(ca == "link") {
				try {
					parent.main.setLink(id);
				} catch(oException) {
					alert('<?php echo $_lang['unable_set_link']; ?>');
				}
			}
		}

		//Raymond: added getFolderState,saveFolderState
		function getFolderState() {
			if(openedArray != [0]) {
				oarray = "&opened=";
				for(key in openedArray) {
					if(openedArray[key] == 1) {
						oarray += key + "|";
					}
				}
			} else {
				oarray = "&opened=";
			}
			return oarray;
		}

		function saveFolderState() {
			jQuery.get('index.php?a=1&f=nodes&savestateonly=1' + getFolderState())
		}

		// show state of recycle bin
		function showBinFull() {
			if(jQuery('#Button10').length) {
				jQuery('#Button10').attr('title', '<?php echo $_lang['empty_recycle_bin']; ?>').attr('onclick', emptyTrash).addClass('treeButton').html('<?php echo $_style['empty_recycle_bin']; ?>')
			}
		}

		function showBinEmpty() {
			if(jQuery('#Button10').length) {
				jQuery('#Button10').attr('title', '<?php echo addslashes($_lang['empty_recycle_bin_empty']); ?>').removeAttr('onclick').addClass('treeButton').html('<?php echo $_style['empty_recycle_bin_empty']; ?>')
			}
		}

		function setLastClickedElement(type, id) {
			localStorage.setItem('MODX_lastClickedElement', '[' + type + ',' + id + ']');
		}
	</script>

</head>
<body onClick="hideMenu(1);" class="<?php echo $modx_textdir ? ' rtl' : '' ?>">

<?php
// invoke OnTreePrerender event
$evtOut = $modx->invokeEvent('OnManagerTreeInit', $_REQUEST);
if(is_array($evtOut)) {
	echo implode("\n", $evtOut);
}
?>

<div class="treeframebody">
	<div id="treeSplitter"></div>

	<table id="treeMenu" width="100%" border="0" cellpadding="0" cellspacing="0">
		<tr>
			<td>
				<table cellpadding="0" cellspacing="0" border="0">
					<tr>
						<td>
							<a href="#" class="treeButton" id="Button1" onClick="expandTree();" title="<?php echo $_lang['expand_tree']; ?>"><?php echo $_style['expand_tree']; ?></a>
						</td>
						<td>
							<a href="#" class="treeButton" id="Button2" onClick="collapseTree();" title="<?php echo $_lang['collapse_tree']; ?>"><?php echo $_style['collapse_tree']; ?></a>
						</td>
						<?php if($modx->hasPermission('new_document')) { ?>
							<td>
								<a href="#" class="treeButton" id="Button3a" onClick="top.main.document.location.href='index.php?a=4';" title="<?php echo $_lang['add_resource']; ?>"><?php echo $_style['add_doc_tree']; ?></a>
							</td>
							<td>
								<a href="#" class="treeButton" id="Button3c" onClick="top.main.document.location.href='index.php?a=72';" title="<?php echo $_lang['add_weblink']; ?>"><?php echo $_style['add_weblink_tree']; ?></a>
							</td>
						<?php } ?>
						<td>
							<a href="#" class="treeButton" id="Button4" onClick="top.modx.reloadtree();" title="<?php echo $_lang['refresh_tree']; ?>"><?php echo $_style['refresh_tree']; ?></a>
						</td>
						<td>
							<a href="#" class="treeButton" id="Button5" onClick="showSorter();" title="<?php echo $_lang['sort_tree']; ?>"><?php echo $_style['sort_tree']; ?></a>
						</td>
						<?php if($modx->hasPermission('edit_document')) { ?>
							<td>
								<a href="#" id="Button11" class="treeButton" onClick="top.main.document.location.href='index.php?a=56&id=0';" title="<?php echo $_lang['sort_menuindex']; ?>"><?php echo $_style['sort_menuindex']; ?></a>
							</td>
						<?php } ?>
						<?php if($use_browser && $modx->hasPermission('assets_images')) { ?>
							<td>
								<a href="#" id="Button13" class="treeButton" title="<?php echo $_lang["images_management"] . "\n" . $_lang['em_button_shift'] ?>"><?php echo $_style['images_management']; ?></a>
							</td>
						<?php } ?>
						<?php if($use_browser && $modx->hasPermission('assets_files')) { ?>
							<td>
								<a href="#" id="Button14" class="treeButton" title="<?php echo $_lang["files_management"] . "\n" . $_lang['em_button_shift'] ?>"><?php echo $_style['files_management']; ?></a>
							</td>
						<?php } ?>
						<?php if($modx->hasPermission('edit_template') || $modx->hasPermission('edit_snippet') || $modx->hasPermission('edit_chunk') || $modx->hasPermission('edit_plugin')) { ?>
							<td>
								<a href="#" id="Button12" class="treeButton" title="<?php echo $_lang["element_management"] . "\n" . $_lang['em_button_shift'] ?>"><?php echo $_style['element_management']; ?></a>
							</td>
						<?php } ?>
						<?php if($modx->hasPermission('empty_trash')) { ?>
							<td>
								<a href="#" id="Button10" class="treeButtonDisabled" title="<?php echo $_lang['empty_recycle_bin_empty']; ?>"><?php echo $_style['empty_recycle_bin_empty']; ?></a>
							</td>
						<?php } ?>
					</tr>
				</table>
			</td>
		</tr>
	</table>

	<?php if($modx->hasPermission('edit_template') || $modx->hasPermission('edit_snippet') || $modx->hasPermission('edit_chunk') || $modx->hasPermission('edit_plugin')) { ?>
		<script>
			jQuery('#Button12').click(function(e) {
				e.preventDefault();
				var randomNum = 'gener1';
				if(e.shiftKey) {
					randomNum = Math.floor((Math.random() * 999999) + 1);
				}
				window.open('index.php?a=76', randomNum, 'width=960,height=720,top=' + ((screen.height - 720) / 2) + ',left=' + ((screen.width - 960) / 2) + ',toolbar=no,location=no,directories=no,status=no,menubar=no,scrollbars=yes,resizable=no')
			});
		</script>
	<?php } ?>

	<?php if($use_browser && $modx->hasPermission('assets_images')) { ?>
		<script>
			jQuery('#Button13').click(function(e) {
				e.preventDefault();
				var randomNum = 'gener2';
				if(e.shiftKey) {
					randomNum = Math.floor((Math.random() * 999999) + 1);
				}
				window.open('media/browser/<?php echo $which_browser; ?>/browse.php?&type=images', randomNum, 'width=960,height=720,top=' + ((screen.height - 720) / 2) + ',left=' + ((screen.width - 960) / 2) + ',toolbar=no,location=no,directories=no,status=no,menubar=no,scrollbars=yes,resizable=no')
			});
		</script>
	<?php } ?>

	<?php if($use_browser && $modx->hasPermission('assets_files')) { ?>
		<script>
			jQuery('#Button14').click(function(e) {
				e.preventDefault();
				var randomNum = 'gener3';
				if(e.shiftKey) {
					randomNum = Math.floor((Math.random() * 999999) + 1);
				}
				window.open('media/browser/<?php echo $which_browser; ?>/browse.php?&type=files', randomNum, 'width=960,height=720,top=' + ((screen.height - 720) / 2) + ',left=' + ((screen.width - 960) / 2) + ',toolbar=no,location=no,directories=no,status=no,menubar=no,scrollbars=yes,resizable=no')
			});
		</script>
	<?php } ?>

	<div id="floater">
		<?php
		$sortParams = array(
			'tree_sortby',
			'tree_sortdir',
			'tree_nodename'
		);
		foreach($sortParams as $param) {
			if(isset($_REQUEST[$param])) {
				$modx->manager->saveLastUserSetting($param, $_REQUEST[$param]);
				$_SESSION[$param] = $_REQUEST[$param];
			} else if(!isset($_SESSION[$param])) {
				$_SESSION[$param] = $modx->manager->getLastUserSetting($param);
			}
		}
		?>
		<form name="sortFrm" id="sortFrm" action="menu.php">
			<input type="hidden" name="dt" value="<?php echo htmlspecialchars($_REQUEST['dt']); ?>" />
			<table width="100%" border="0" cellpadding="0" cellspacing="0">
				<tr>
					<td style="padding-left: 10px;padding-top: 1px;">
						<?php echo $_lang["sort_tree"] ?>
						<select name="sortby" style="margin-top:5px; width: 100%;">
							<option value="isfolder" <?php echo $_SESSION['tree_sortby'] == 'isfolder' ? "selected='selected'" : "" ?>><?php echo $_lang['folder']; ?></option>
							<option value="pagetitle" <?php echo $_SESSION['tree_sortby'] == 'pagetitle' ? "selected='selected'" : "" ?>><?php echo $_lang['pagetitle']; ?></option>
							<option value="longtitle" <?php echo $_SESSION['tree_sortby'] == 'longtitle' ? "selected='selected'" : "" ?>><?php echo $_lang['long_title']; ?></option>
							<option value="id" <?php echo $_SESSION['tree_sortby'] == 'id' ? "selected='selected'" : "" ?>><?php echo $_lang['id']; ?></option>
							<option value="menuindex" <?php echo $_SESSION['tree_sortby'] == 'menuindex' ? "selected='selected'" : "" ?>><?php echo $_lang['resource_opt_menu_index'] ?></option>
							<option value="createdon" <?php echo $_SESSION['tree_sortby'] == 'createdon' ? "selected='selected'" : "" ?>><?php echo $_lang['createdon']; ?></option>
							<option value="editedon" <?php echo $_SESSION['tree_sortby'] == 'editedon' ? "selected='selected'" : "" ?>><?php echo $_lang['editedon']; ?></option>
							<option value="publishedon" <?php echo $_SESSION['tree_sortby'] == 'publishedon' ? "selected='selected'" : "" ?>><?php echo $_lang['page_data_publishdate']; ?></option>
						</select>
					</td>
				</tr>
				<tr>
					<td style="padding-left: 10px;padding-top: 1px;">
						<select name="sortdir" style="width: 100%;">
							<option value="DESC" <?php echo $_SESSION['tree_sortdir'] == 'DESC' ? "selected='selected'" : "" ?>><?php echo $_lang['sort_desc']; ?></option>
							<option value="ASC" <?php echo $_SESSION['tree_sortdir'] == 'ASC' ? "selected='selected'" : "" ?>><?php echo $_lang['sort_asc']; ?></option>
						</select>
					</td>
				</tr>
				<tr>
					<td width="99%" style="padding-left: 10px;padding-top: 1px;" colspan="2">
						<p style="margin-top:10px;"><?php echo $_lang["setting_resource_tree_node_name"] ?></p>
						<select name="nodename" style="width: 100%;">
							<option value="default" <?php echo $_SESSION['tree_nodename'] == 'default' ? "selected='selected'" : "" ?>><?php echo trim($_lang['default'], ':'); ?></option>
							<option value="pagetitle" <?php echo $_SESSION['tree_nodename'] == 'pagetitle' ? "selected='selected'" : "" ?>><?php echo $_lang['pagetitle']; ?></option>
							<option value="longtitle" <?php echo $_SESSION['tree_nodename'] == 'longtitle' ? "selected='selected'" : "" ?>><?php echo $_lang['long_title']; ?></option>
							<option value="menutitle" <?php echo $_SESSION['tree_nodename'] == 'menutitle' ? "selected='selected'" : "" ?>><?php echo $_lang['resource_opt_menu_title']; ?></option>
							<option value="alias" <?php echo $_SESSION['tree_nodename'] == 'alias' ? "selected='selected'" : "" ?>><?php echo $_lang['alias']; ?></option>
							<option value="createdon" <?php echo $_SESSION['tree_nodename'] == 'createdon' ? "selected='selected'" : "" ?>><?php echo $_lang['createdon']; ?></option>
							<option value="editedon" <?php echo $_SESSION['tree_nodename'] == 'editedon' ? "selected='selected'" : "" ?>><?php echo $_lang['editedon']; ?></option>
							<option value="publishedon" <?php echo $_SESSION['tree_nodename'] == 'publishedon' ? "selected='selected'" : "" ?>><?php echo $_lang['page_data_publishdate']; ?></option>
						</select>
					</td>
				</tr>
				<tr>
					<td style="padding-left: 10px;padding-top: 1px;">
						<ul class="actionButtons" style="margin:10px 0;">
							<li>
								<a href="#" class="treeButton" id="button7" style="text-align:right" onClick="updateTree();showSorter();" title="<?php echo $_lang['sort_tree']; ?>"><?php echo $_lang['sort_tree']; ?></a>
							</li>
						</ul>
					</td>
				</tr>
			</table>
		</form>
	</div>

	<div id="treeHolder">
		<?php
		// invoke OnTreeRender event
		$evtOut = $modx->invokeEvent('OnManagerTreePrerender', $modx->db->escape($_REQUEST));
		if(is_array($evtOut)) {
			echo implode("\n", $evtOut);
		}
		?>
		<div><?php echo $_style['tree_showtree']; ?>&nbsp;<span class="rootNode" onClick="treeAction(event, 0, '<?php $site_name = htmlspecialchars($site_name, ENT_QUOTES, $modx->config['modx_charset']);
			echo $site_name; ?>');"><b><?php echo $site_name; ?></b></span>
			<div id="treeRoot"></div>
		</div>
		<?php
		// invoke OnTreeRender event
		$evtOut = $modx->invokeEvent('OnManagerTreeRender', $modx->db->escape($_REQUEST));
		if(is_array($evtOut)) {
			echo implode("\n", $evtOut);
		}
		?>
	</div>

	<script type="text/javascript">

		resizeTree();
		restoreTree();
		jQuery(window).resize(function() {
			resizeTree()
		});

		// Set 'treeNodeSelected' class on document node when editing via Context Menu
		function setActiveFromContextMenu(doc_id) {
			jQuery('.treeNodeSelected').removeClass('treeNodeSelected');
			jQuery('#node' + doc_id + '>span').addClass('treeNodeSelected');
		}

		// Context menu stuff
		function menuHandler(action) {
			switch(action) {
				case 1 : // view
					setActiveFromContextMenu(itemToChange);
					top.main.document.location.href = "index.php?a=3&id=" + itemToChange;
					break;
				case 2 : // edit
					setLastClickedElement(7, itemToChange);
					setActiveFromContextMenu(itemToChange);
					top.main.document.location.href = "index.php?a=27&id=" + itemToChange;
					break;
				case 3 : // new Resource
					top.main.document.location.href = "index.php?a=4&pid=" + itemToChange;
					break;
				case 4 : // delete
					if(selectedObjectDeleted == 0) {
						if(confirm("'" + selectedObjectName + "'\n\n<?php echo $_lang['confirm_delete_resource']; ?>") == true) {
							top.main.document.location.href = "index.php?a=6&id=" + itemToChange;
						}
					} else {
						alert("'" + selectedObjectName + "' <?php echo $_lang['already_deleted']; ?>");
					}
					break;
				case 5 : // move
					top.main.document.location.href = "index.php?a=51&id=" + itemToChange;
					break;
				case 6 : // new Weblink
					top.main.document.location.href = "index.php?a=72&pid=" + itemToChange;
					break;
				case 7 : // duplicate
					if(confirm("<?php echo $_lang['confirm_resource_duplicate'] ?>") == true) {
						top.main.document.location.href = "index.php?a=94&id=" + itemToChange;
					}
					break;
				case 8 : // undelete
					if(selectedObjectDeleted == 0) {
						alert("'" + selectedObjectName + "' <?php echo $_lang['not_deleted']; ?>");
					} else {
						if(confirm("'" + selectedObjectName + "' <?php echo $_lang['confirm_undelete']; ?>") == true) {
							top.main.document.location.href = "index.php?a=63&id=" + itemToChange;
						}
					}
					break;
				case 9 : // publish
					if(confirm("'" + selectedObjectName + "' <?php echo $_lang['confirm_publish']; ?>") == true) {
						top.main.document.location.href = "index.php?a=61&id=" + itemToChange;
					}
					break;
				case 10 : // unpublish
					if(itemToChange != <?php echo $modx->config['site_start']?>) {
						if(confirm("'" + selectedObjectName + "' <?php echo $_lang['confirm_unpublish']; ?>") == true) {
							top.main.document.location.href = "index.php?a=62&id=" + itemToChange;
						}
					} else {
						alert('Document is linked to site_start variable and cannot be unpublished!');
					}
					break;
				case 11 : // sort menu index
					top.main.document.location.href = "index.php?a=56&id=" + itemToChange;
					break;
				case 12 : // preview
					window.open(selectedObjectUrl, 'previeWin'); //re-use 'new' window
					break;

				default :
					alert('Unknown operation command.');
			}
		}

	</script>

	<!-- Contextual Menu Popup Code -->
	<div id="mx_contextmenu" onselectstart="return false;">
		<div id="nameHolder">&nbsp;</div>
		<?php
		constructLink(3, $_style["ctx_new_document"], $_lang["create_resource_here"], $modx->hasPermission('new_document')); // new Resource
		constructLink(2, $_style["ctx_edit_document"], $_lang["edit_resource"], $modx->hasPermission('edit_document')); // edit
		constructLink(5, $_style["ctx_move_document"], $_lang["move_resource"], $modx->hasPermission('save_document')); // move
		constructLink(7, $_style["ctx_resource_duplicate"], $_lang["resource_duplicate"], $modx->hasPermission('new_document')); // duplicate
		constructLink(11, $_style["ctx_sort_menuindex"], $_lang["sort_menuindex"], $modx->hasPermission('edit_document')); // sort menu index
		?>
		<div class="seperator"></div>
		<?php
		constructLink(9, $_style["ctx_publish_document"], $_lang["publish_resource"], $modx->hasPermission('publish_document')); // publish
		constructLink(10, $_style["ctx_unpublish_resource"], $_lang["unpublish_resource"], $modx->hasPermission('publish_document')); // unpublish
		constructLink(4, $_style["ctx_delete"], $_lang["delete_resource"], $modx->hasPermission('delete_document')); // delete
		constructLink(8, $_style["ctx_undelete_resource"], $_lang["undelete_resource"], $modx->hasPermission('delete_document')); // undelete
		?>
		<div class="seperator"></div>
		<?php
		constructLink(6, $_style["ctx_weblink"], $_lang["create_weblink_here"], $modx->hasPermission('new_document')); // new Weblink
		?>
		<div class="seperator"></div>
		<?php
		constructLink(1, $_style["ctx_resource_overview"], $_lang["resource_overview"], $modx->hasPermission('view_document')); // view
		constructLink(12, $_style["ctx_preview_resource"], $_lang["preview_resource"], 1); // preview
		?>
	</div>
</div>
</body>
</html>
<?php
function constructLink($action, $img, $text, $allowed) {
	if($allowed == 1) {
		echo sprintf('<div class="menuLink" id="item%s" onclick="menuHandler(%s); hideMenu();">', $action, $action);
	} else {
		echo '<div class="menuLinkDisabled">';
	}
	echo sprintf('<i class="%s"></i> %s</div>', $img, $text);
}