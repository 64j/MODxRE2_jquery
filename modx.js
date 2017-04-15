;
!function($, w, d, undefined) {
	$.extend(modx, {
		work: function() {
			if($('#workText').length) {
				$('#workText').html('<i class="fa fa-warning"></i>&nbsp;' + modx.lang.working);
			} else {
				setTimeout(function() {
					modx.work()
				}, 50);
			}
		},
		stopWork: function() {
			if($('#workText').length) {
				$('#workText').html('');
			} else {
				setTimeout(function() {
					modx.stopWork()
				}, 50);
			}
		},
		getQueryVariable: function(variable, query) {
			var vars = query.split('&');
			for(var i = 0; i < vars.length; i++) {
				var pair = vars[i].split('=');
				if(decodeURIComponent(pair[0]) == variable) {
					return decodeURIComponent(pair[1]);
				}
			}
		},
		scrollWork: function() {
			var frm = document.getElementById("mainframe").contentWindow;
			currentPageY = localStorage.getItem('page_y');
			pageUrl = localStorage.getItem('page_url');
			if(currentPageY === undefined) {
				localStorage.setItem('page_y', 0);
			}
			if(pageUrl === null) {
				pageUrl = frm.location.search.substring(1);
			}
			if(modx.getQueryVariable('a', pageUrl) == modx.getQueryVariable('a', frm.location.search.substring(1))) {
				if(modx.getQueryVariable('id', pageUrl) == modx.getQueryVariable('id', frm.location.search.substring(1))) {
					frm.scrollTo(0, currentPageY);
				}
			}

			frm.onscroll = function() {
				if(frm.pageYOffset > 0) {
					localStorage.setItem('page_y', frm.pageYOffset);
					localStorage.setItem('page_url', frm.location.search.substring(1));
				}
			}
		},
		startrefresh: function(rFrame) {
			if(rFrame == 1) {
				x = window.setTimeout('reloadtree()', 500);
			}
			if(rFrame == 2) {
				x = window.setTimeout('reloadmenu()', 500);
			}
			if(rFrame == 9) {
				x = window.setTimeout('reloadmenu()', 500);
				y = window.setTimeout('reloadtree()', 500);
			}
			if(rFrame == 10) {
				window.top.location.href = "../" + modx.MGR_DIR;
			}
		},
		reloadtree: function() {
			if($('#buildText').length) {
				$('#buildText').html('<i class="fa fa-info-circle"></i>&nbsp;' + modx.lang.loading_doc_tree);
				$('#buildText').show();
			}
			tree.saveFolderState(); // save folder state
			setTimeout('tree.restoreTree()', 200);
		},
		reloadmenu: function() {
			if(modx.manager_layout == 0) {
				if($('#buildText').length) {
					$('#buildText').html('<i class="fa fa-warning"></i>&nbsp;' + modx.lang.loading_menu);
					$('#buildText').show();
				}
				location.reload();
			}
		},
		setLastClickedElement: function(type, id) {
			localStorage.setItem('MODX_lastClickedElement', '[' + type + ',' + id + ']');
		},
		removeLocks: function() {
			if(confirm(modx.lang.confirm_remove_locks) == true) {
				main.document.location.href = "index.php?a=67";
			}
		},
		ExtractNumber: function(value) {
			var n = parseInt(value);
			return n == null || isNaN(n) ? 0 : n
		},
		init_mainMenu: function() {
			var menu = $('#mainMenu', d);
			$('.nav>li>a', menu).click(function() {
				$('.nav>li:not(:hover)', menu).removeClass('active').addClass('close');
				$(this).closest('li').toggleClass('active');
			});

			$('.nav>li li a', menu).click(function() {
				$('.nav>li', menu).removeClass('active').addClass('close');
				$(this).closest('li.dropdown').addClass('active')
			});

			$('.nav>li', menu).hover(function() {
				$(this).removeClass('close')
			});
		},
		init_sideBar: function() {
			$('#hideMenu', d).click(function() {
				var pos = 0;
				if($('#tree', d).width()) {
					$(d.body).removeClass('tree-show').addClass('tree-hide');
				} else {
					$(d.body).addClass('tree-show').removeClass('tree-hide');
					pos = 320
				}
				$(d.body).removeClass('resizer-move');
				$('#tree', d).css({
					width: pos
				});
				$('#resizer, #main', d).css({
					left: pos
				});
			});

			$(d).on('mousedown touchstart', '#resizer', function(e) {
				var pos = {};
				pos.x = typeof e.originalEvent.touches != 'undefined' && e.originalEvent.touches.length ? e.originalEvent.touches[0].clientX || e.originalEvent.changedTouches[0].clientX : e.clientX;

				$(d.body).addClass('resizer-move');

				$(d).on('mousemove touchmove', function(e) {
					pos.x = typeof e.originalEvent.touches != 'undefined' && e.originalEvent.touches.length  ? e.originalEvent.touches[0].clientX || e.originalEvent.changedTouches[0].clientX : e.clientX;

					if(parseInt(pos.x) > 0) {
						$(d.body).addClass('tree-show').removeClass('tree-hide')
					} else {
						pos.x = 0;
						$(d.body).removeClass('tree-show').addClass('resizer-move')
					}

					$('#tree', d).css({
						width: pos.x
					});
					$('#resizer, #main', d).css({
						left: pos.x
					});
				});

				$(parent.document).one('mouseup touchend', function(e) {
					if(typeof e.originalEvent.touches != 'undefined' && e.originalEvent.touches.length) {
						pos.x = e.originalEvent.touches[0].clientX
					} else if(typeof e.originalEvent.changedTouches != 'undefined' && e.originalEvent.changedTouches.length) {
						pos.x = e.originalEvent.changedTouches[0].clientX
					} else {
						pos.x = e.clientX
					}

					$(parent.document).off('mousemove touchmove');
					if(parseInt(pos.x) > 0) {
						$(parent.document.body).removeClass('resizer-move').addClass('tree-show').removeClass('tree-hide')
					} else {
						$(parent.document.body).removeClass('resizer-move').removeClass('tree-show').addClass('tree-hide')
					}
				});

			})
		}
	});

	$(document).ready(function() {
		modx.stopWork();
		modx.scrollWork();
		modx.init_mainMenu();
		modx.init_sideBar()
	});

}(jQuery, window, document, undefined);

var mainMenu = {
	startrefresh: function(a) {
		modx.startrefresh(a)
	},
	work: function() {
		modx.work()
	},
	reloadtree: function() {
		modx.reloadtree()
	}
};

var tree = {
	restoreTree: function() {
		restoreTree()
	},
	saveFolderState: function() {
		saveFolderState()
	}
};

var reloadtree = function() {
	modx.reloadtree()
};

var setLastClickedElement = function(type, id) {
	modx.setLastClickedElement(type, id)
};