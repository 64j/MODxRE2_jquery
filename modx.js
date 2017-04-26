'use strict';

window.mainMenu = {};
window.mainMenu.work = function() {
	modx.main.work()
};
window.mainMenu.reloadtree = function() {
	console.log('mainMenu.reloadtree() off');
	//setTimeout('modx.tree.restoreTree()', 50)
};
window.mainMenu.startrefresh = function(rFrame) {
	if(rFrame === 1) {
		console.log('mainMenu.startrefresh(' + rFrame + ')');
		setTimeout('modx.tree.restoreTree()', 50)
	}
	if(rFrame === 2) {
		console.log('mainMenu.startrefresh(' + rFrame + ') off');
		//setTimeout('modx.tree.restoreTree()', 50)
	}
	if(rFrame === 9 || rFrame === 10) {
		console.log('mainMenu.startrefresh(' + rFrame + ')');
		top.location.href = "../" + modx.MGR_DIR;
	}
};
window.tree = {};
window.tree.ca = 'open';
window.tree.document = document;
window.tree.saveFolderState = function() {
	// console.log('tree.saveFolderState() off');
};
window.tree.updateTree = function() {
	console.log('tree.updateTree()');
	modx.tree.updateTree()
};
window.tree.restoreTree = function() {
	console.log('tree.restoreTree()');
	modx.tree.restoreTree()
};
window.tree.reloadElementsInTree = function() {
	console.log('tree.reloadElementsInTree()');
	modx.tree.reloadElementsInTree()
};
window.tree.resizeTree = function() {
	console.log('tree.resizeTree() off');
	// modx.tree.resizeTree()
};

function setLastClickedElement(type, id) {
	modx.setLastClickedElement(type, id)
}

(function($, w, d, undefined) {
	let _ = {
		init: function() {
			if(!localStorage.getItem('MODX_lastPositionSideBar')) {
				localStorage.setItem('MODX_lastPositionSideBar', modx.config.tree_width);
			}
			setLastClickedElement(0, 0);
			modx.main.stopWork();
			modx.tree.init();
			modx.mainMenu.init();
			modx.resizer.init();
			modx.setLastClickedElement(0, 0);
			w.setInterval(modx.keepMeAlive, 1000 * 60 * modx.config.session_timeout); // Update session every 10min 1000 * 600
			w.onload = function() {
				modx.updateMail(true); // First run update
			}
		},
		mainMenu: {
			id: 'mainMenu',
			init: function() {
				console.log('modx.mainMenu.init()');
				d.getElementById(modx.mainMenu.id).onmouseover = function() {
					let el = this.querySelector('.close');
					if(el) el.classList.remove('close');
				};
				d.getElementById(modx.mainMenu.id).onclick = function(e) {
					let t = e.target.closest('a');
					if(t !== null && t.href !== undefined && t.href !== this.baseURI) {
						this.querySelector('.active').classList.remove('active');
						if(t.offsetParent.className.indexOf('dropdown-menu') === 0) {
							t.offsetParent.offsetParent.classList.add('active');
							t.offsetParent.offsetParent.classList.add('close')
						} else {
							t.offsetParent.classList.add('active')
						}
					}

				};
				modx.search.init();

				// set maxHeight for childs UL mainMenu
				let elms = d.querySelectorAll('#' + modx.mainMenu.id + ' .nav > li > ul');
				for(let i = 0; i < elms.length; i++) {
					elms[i].style.maxHeight = w.innerHeight - modx.config.menu_height + 'px';
				}
			}
		},
		search: {
			id: 'searchform',
			idResult: 'searchresult',
			idInput: 'searchid',
			classResult: 'ajaxSearchResults',
			classMask: 'mask',
			searchResultWidth: '400',
			timer: 0,
			init: function() {
				modx.search.result = d.getElementById(modx.search.idResult);
				modx.search.result.style.width = modx.search.searchResultWidth + 'px';
				modx.search.result.style.marginRight = -modx.search.searchResultWidth + 'px';

				let el = d.getElementById(modx.search.idInput);
				let r = d.createElement('i');
				r.className = 'fa fa-refresh fa-spin fa-fw';

				el.onkeyup = function(e) {
					e.preventDefault();
					clearTimeout(modx.search.timer);

					if(el.value.length !== '' && el.value.length > 2) {
						modx.search.timer = setTimeout(function() {
							let xhr = modx.XHR();
							xhr.open('GET', 'index.php?a=71&ajax=1&submitok=Search&searchid=' + el.value, true);
							xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded; charset=UTF-8');

							xhr.onload = function() {
								if(this.readyState === 4) {
									if(this.status === 200) {
										modx.animation.fadeOut(r, true);
										let div = d.createElement('div');
										div.innerHTML = this.responseText;
										let result = div.getElementsByClassName(modx.search.classResult)[0];
										if(result) {
											if(result.innerHTML !== '') {
												let links = result.getElementsByTagName('A');
												for(let i = 0; i < links.length; i++) {
													links[i].target = 'main';
													links[i].innerHTML += '<i onclick="modx.openWindow({title:\'' + links[i].innerText + '\',id:\'' + links[i].id + '\',url:\'' + links[i].href + '\'});return false;">' + modx.style.icons_external_link + '</i>';
												}
												modx.search.result.innerHTML = result.outerHTML;
												modx.search.open();
												modx.search.result.onclick = function(e) {
													if(e.target.tagName === 'I') {
														return false;
													}
													let t = e.target.closest('a');
													if(t !== null) {
														let el = modx.search.result.querySelector('.selected');
														if(el) el.className = '';
														t.className = 'selected'
													}
												};
											} else {
												modx.search.empty()
											}
										} else {
											modx.search.empty()
										}
									}
								}
							};
							xhr.onloadstart = function() {
								el.closest('form').appendChild(r)
							};
							xhr.onerror = function() {
								console.warn(this.status)
							};
							xhr.send();
						}, 300)
					} else {
						modx.animation.fadeOut(r, true);
						modx.search.empty()
					}
				};

				el.onfocus = function() {
					modx.search.open()
				};

				el.onclick = function() {
					modx.search.open()
				};

				el.onblur = function() {
					modx.search.close()
				};

				el.onmouseover = function() {
					modx.search.open()
				};

				d.getElementById(modx.search.id).getElementsByClassName(modx.search.classMask)[0].onmouseover = function() {
					modx.search.open();
				};
				d.getElementById(modx.search.id).getElementsByClassName(modx.search.classMask)[0].onmouseout = function() {
					modx.search.close();
				}
			},
			open: function() {
				if(modx.search.result.getElementsByClassName(modx.search.classResult)[0]) {
					modx.search.result.classList.add('open')
				}
			},
			close: function() {
				modx.search.result.classList.remove('open')
			},
			empty: function() {
				modx.search.result.classList.remove('open');
				modx.search.result.innerHTML = '';
			}
		},
		main: { // mainframe
			id: 'main',
			idFrame: 'mainframe',
			init: function() {
				modx.main.stopWork();
				modx.main.scrollWork();
			},
			work: function() {
				let el = d.getElementById('workText');
				if(el) el.innerHTML = modx.style.icons_working + modx.lang.working;
				else setTimeout('modx.main.work()', 50);
			},
			stopWork: function() {
				let el = d.getElementById('workText');
				if(el) el.innerHTML = "";
				else  setTimeout('modx.main.stopWork()', 50);
			},
			scrollWork: function() {
				let mf = d.getElementById(modx.main.idFrame).contentWindow;
				let y = localStorage.getItem('page_y');
				let u = localStorage.getItem('page_url');
				if(y === undefined) {
					localStorage.setItem('page_y', 0);
				}
				if(u === null) {
					u = mf.location.search.substring(1);
				}
				if((modx.main.getQueryVariable('a', u) === modx.main.getQueryVariable('a', mf.location.search.substring(1))) &&
					(modx.main.getQueryVariable('id', u) === modx.main.getQueryVariable('id', mf.location.search.substring(1)))) {
					mf.scrollTo(0, y);
				}
				mf.onscroll = function() {
					if(mf.pageYOffset > 0) {
						localStorage.setItem('page_y', mf.pageYOffset);
						localStorage.setItem('page_url', mf.location.search.substring(1));
					}
				}
			},
			getQueryVariable: function(v, q) {
				let vars = q.split('&');
				for(let i = 0; i < vars.length; i++) {
					let p = vars[i].split('=');
					if(decodeURIComponent(p[0]) == v) {
						return decodeURIComponent(p[1]);
					}
				}
			}
		},
		resizer: { // resizer for tree / sidebar
			dragElement: null,
			oldZIndex: 9990,
			newZIndex: 9991,
			left: modx.config.tree_width,
			id: 'resizer',
			switcher: 'hideMenu',
			background: '#bbb',
			mask: null,
			init: function() {
				modx.resizer.mask = d.createElement('div');
				modx.resizer.mask.id = 'mask_resizer';
				modx.resizer.mask.style.zIndex = modx.resizer.oldZIndex;

				d.getElementById(modx.resizer.switcher).onclick = modx.resizer.toggle;
				d.getElementById(modx.resizer.id).onmousedown = modx.resizer.onMouseDown;
				d.getElementById(modx.resizer.id).onmouseup = modx.resizer.onMouseUp
			},
			onMouseDown: function(e) {
				if(e === null) e = w.event;
				modx.resizer.dragElement = e.target !== null ? e.target : e.srcElement;
				if((e.buttons === 1 && w.event != null || e.button === 0) && modx.resizer.dragElement.id === modx.resizer.id) {
					modx.resizer.oldZIndex = modx.resizer.dragElement.style.zIndex;
					modx.resizer.dragElement.style.zIndex = modx.resizer.newZIndex;
					modx.resizer.dragElement.style.background = modx.resizer.background;
					localStorage.setItem('MODX_lastPositionSideBar', (modx.resizer.dragElement.offsetLeft > 0 ? modx.resizer.dragElement.offsetLeft : 0));
					d.body.appendChild(modx.resizer.mask);
					d.onmousemove = modx.resizer.onMouseMove;
					d.body.focus();
					d.onselectstart = function() {
						return false
					};
					modx.resizer.dragElement.ondragstart = function() {
						return false
					};
					return false
				}
			},
			onMouseMove: function(e) {
				if(e === null) var e = w.event;
				if(e.clientX > 0) {
					modx.resizer.left = e.clientX
				} else {
					modx.resizer.left = 0;
				}
				modx.resizer.dragElement.style.left = modx.resizer.left + 'px';
				d.getElementById('tree').style.width = modx.resizer.left + 'px';
				d.getElementById('main').style.left = modx.resizer.left + 'px'
				if(e.clientX < -2 || e.clientY < -2) {
					modx.resizer.onMouseUp(e);
				}
			},
			onMouseUp: function(e) {
				if(modx.resizer.dragElement !== null && e.button === 0 && modx.resizer.dragElement.id === modx.resizer.id) {
					if(e.clientX > 0) {
						d.body.classList.add('sidebar-opened');
						d.body.classList.remove('sidebar-closed');
						modx.resizer.left = e.clientX
					} else {
						d.body.classList.remove('sidebar-opened');
						d.body.classList.add('sidebar-closed');
						modx.resizer.left = 0;
					}
					d.cookie = 'MODX_positionSideBar=' + modx.resizer.left;
					modx.resizer.dragElement.style.zIndex = modx.resizer.oldZIndex;
					modx.resizer.dragElement.style.background = '';
					modx.resizer.dragElement.ondragstart = null;
					modx.resizer.dragElement = null;
					d.body.removeChild(modx.resizer.mask);
					d.onmousemove = null;
					d.onselectstart = null;
				}
			},
			toggle: function() {
				let p = parseInt(d.getElementById('tree').offsetWidth) !== 0 ? 0 : (localStorage.getItem('MODX_lastPositionSideBar') ? parseInt(localStorage.getItem('MODX_lastPositionSideBar')) : modx.config.tree_width);
				modx.resizer.setWidth(p)
			},
			setWidth: function(pos) {
				if(pos > 0) {
					d.body.classList.add('sidebar-opened');
					d.body.classList.remove('sidebar-closed');
					localStorage.setItem('MODX_lastPositionSideBar', 0);
				} else {
					d.body.classList.remove('sidebar-opened');
					d.body.classList.add('sidebar-closed');
					localStorage.setItem('MODX_lastPositionSideBar', parseInt(d.getElementById('tree').offsetWidth));
				}
				d.cookie = 'MODX_positionSideBar=' + pos;
				d.getElementById('tree').style.width = pos + 'px';
				d.getElementById('resizer').style.left = pos + 'px';
				d.getElementById('main').style.left = pos + 'px';
			},
			setDefaultWidth: function() {
				modx.resizer.setWidth(modx.config.tree_width);
			}
		},
		tree: {
			_rc: 0,
			rpcNode: null,
			itemToChange: null,
			selectedObjectName: null,
			selectedObject: 0,
			selectedObjectDeleted: 0,
			selectedObjectUrl: '',
			init: function() {
				modx.tree.restoreTree()

			},
			toggleNode: function(node, indent, parent, expandAll, privatenode) {
				privatenode = (!privatenode || privatenode == '0') ? '0' : '1';
				modx.tree.rpcNode = node.parentNode.lastChild;

				let rpcNodeText, loadText = modx.lang.loading_doc_tree, signImg = d.getElementById("s" + parent),
					folderImg = d.getElementById("f" + parent);

				if(modx.tree.rpcNode.style.display !== 'block') {
					// expand
					signImg.innerHTML = modx.style.tree_minusnode;
					folderImg.innerHTML = (privatenode === '0') ? modx.style.tree_folderopen : modx.style.tree_folderopen_secure;
					rpcNodeText = modx.tree.rpcNode.innerHTML;
					modx.openedArray[parent] = 1;

					if(rpcNodeText === "" || rpcNodeText.indexOf(loadText) > 0) {
						let folderState = modx.tree.getFolderState();
						let el = d.getElementById('buildText');
						if(el) {
							el.innerHTML = modx.style.tree_info + loadText;
							el.style.display = 'block'
						}
						//modx.tree.rpcNode.innerHTML = "<span class='emptyNode' style='white-space:nowrap;'>" + loadText + "...<\/span>";
						modx.get('index.php?a=1&f=nodes&indent=' + indent + '&parent=' + parent + '&expandAll=' + expandAll + folderState, function(data) {
							modx.tree.rpcLoadData(data)
						})
					}
					modx.tree.rpcNode.style.display = 'block';
					modx.tree.saveFolderState();
				}
				else {
					// collapse
					signImg.innerHTML = modx.style.tree_plusnode;
					folderImg.innerHTML = (privatenode === '0') ? modx.style.tree_folder : modx.style.tree_folder_secure;
					delete modx.openedArray[parent];
					modx.tree.rpcNode.style.display = 'none';
					modx.tree.rpcNode.innerHTML = '';
					modx.tree.saveFolderState();
				}
			},
			rpcLoadData: function(response) {
				if(modx.tree.rpcNode !== null) {
					modx.tree.rpcNode.innerHTML = typeof response === 'object' ? response.responseText : response;
					modx.tree.rpcNode.style.display = 'block';
					modx.tree.rpcNode.loaded = true;
					let el = d.getElementById('buildText');
					if(el) {
						modx.animation.fadeOut(el)
					}
					if(localStorage.getItem('MODX_lastClickedElement')) {
						modx.tree.setActiveFromContextMenu(JSON.parse(localStorage.getItem('MODX_lastClickedElement'))[1]);
					}
					if(modx.tree.rpcNode.id === 'treeRoot') {
						el = d.getElementById('binFull');
						if(el) modx.tree.showBin(true);
						else modx.tree.showBin(false)
					}
					el = d.getElementById('mx_loginbox');
					if(el) {
						modx.tree.rpcNode.innerHTML = '';
						top.location = 'index.php';
					}
				}
			},
			treeAction: function(e, id, name, treedisp_children) {
				if(tree.ca === "move") {
					try {
						top.main.setMoveValue(id, name);
					} catch(oException) {
						alert(modx.lang.unable_set_parent);
					}
				}
				if(tree.ca === "open" || tree.ca === "") {
					if(id === 0) {
						top.main.location.href = "index.php?a=2";
					} else {
						let href = '';
						modx.setLastClickedElement(7, id);
						if(treedisp_children === 0) {
							href = "index.php?a=3&r=1&id=" + id + modx.tree.getFolderState();
						} else {
							href = "index.php?a=" + modx.config.tree_page_click + "&r=1&id=" + id;
						}
						if(e.shiftKey) {
							w.getSelection().removeAllRanges();
							modx.openWindow(href);
							modx.tree.reloadtree();
						} else {
							top.main.location.href = href;
						}
					}
				}
				if(tree.ca === "parent") {
					try {
						top.main.setParent(id, name);
					} catch(oException) {
						alert(modx.lang.unable_set_parent);
					}
				}
				if(tree.ca === "link") {
					try {
						top.main.setLink(id);
					} catch(oException) {
						alert(modx.lang.unable_set_link);
					}
				}
			},
			showPopup: function(id, title, pub, del, folder, e) {
				let mnu = d.getElementById('mx_contextmenu');
				let item4 = d.getElementById('item4');
				let item5 = d.getElementById('item5');
				let item8 = d.getElementById('item8');
				let item9 = d.getElementById('item9');
				let item10 = d.getElementById('item10');
				let item11 = d.getElementById('item11');

				let el = d.querySelector('#tree .treeNodeSelectedByContext');
				if(el) el.classList.remove('treeNodeSelectedByContext');
				d.querySelector('#node' + id + '>.treeNode').classList.add('treeNodeSelectedByContext');

				if(modx.permission.publish_document === 1) {
					item9.style.display = 'block';
					item10.style.display = 'block';
					if(pub === 1) item9.style.display = 'none';
					else item10.style.display = 'none';
				} else {
					item5.style.display = 'none';
				}

				if(modx.permission.delete_document === 1) {
					item4.style.display = 'block';
					item8.style.display = 'block';
					if(del === 1) {
						item4.style.display = 'none';
						item9.style.display = 'none';
						item10.style.display = 'none';
					} else {
						item8.style.display = 'none';
					}
				}

				if(folder === 1) item11.style.display = 'block';
				else item11.style.display = 'none';

				el = d.getElementById('tree');
				let bodyHeight = el.offsetHeight + el.offsetTop;
				let x = e.clientX > 0 ? e.clientX : e.pageX;
				let y = e.clientY > 0 ? e.clientY : e.pageY;
				if(y + mnu.offsetHeight / 2 > bodyHeight) {
					y = bodyHeight - mnu.offsetHeight - 5;
				} else if(y - mnu.offsetHeight / 2 < el.offsetTop) {
					y = el.offsetTop + 5
				} else {
					y = y - mnu.offsetHeight / 2
				}
				el = e.target.closest('.treeNode');
				if(el === null) x += 50;
				modx.tree.itemToChange = id;
				modx.tree.selectedObjectName = title;
				modx.tree.dopopup(x + 10, y);
				e.cancelBubble = true;
				return false;
			},
			dopopup: function(x, y) {
				if(modx.tree.selectedObjectName.length > 20) {
					modx.tree.selectedObjectName = modx.tree.selectedObjectName.substr(0, 20) + "...";
				}
				let c = d.getElementById('mx_contextmenu'), el = d.getElementById("nameHolder");
				c.style.left = x + (modx.config.textdir ? '-190' : '') + "px"; //offset menu to the left if rtl is selected
				c.style.top = y + "px";
				c.style.visibility = 'visible';
				el.innerHTML = modx.tree.selectedObjectName;
				modx.tree._rc = 1;
				setTimeout(function() {
					modx.tree._rc = 0;
					top.main.onclick = function() {
						modx.tree.hideMenu(1)
					};
					d.onclick = function() {
						modx.tree.hideMenu(1)
					}
				}, 200);
			},
			menuHandler: function(a) {
				switch(a) {
					case 1 : // view
						modx.tree.setActiveFromContextMenu(modx.tree.itemToChange);
						top.main.document.location.href = "index.php?a=3&id=" + modx.tree.itemToChange;
						break;
					case 2 : // edit
						modx.setLastClickedElement(7, modx.tree.itemToChange);
						modx.tree.setActiveFromContextMenu(modx.tree.itemToChange);
						top.main.document.location.href = "index.php?a=27&id=" + modx.tree.itemToChange;
						break;
					case 3 : // new Resource
						top.main.document.location.href = "index.php?a=4&pid=" + modx.tree.itemToChange;
						break;
					case 4 : // delete
						if(modx.tree.selectedObjectDeleted) {
							alert("'" + modx.tree.selectedObjectName + "' " + modx.lang.already_deleted);
						} else if(confirm("'" + modx.tree.selectedObjectName + "'\n\n" + modx.lang.confirm_delete_resource) === true) {
							top.main.document.location.href = "index.php?a=6&id=" + modx.tree.itemToChange;
						}
						break;
					case 5 : // move
						top.main.document.location.href = "index.php?a=51&id=" + modx.tree.itemToChange;
						break;
					case 6 : // new Weblink
						top.main.document.location.href = "index.php?a=72&pid=" + modx.tree.itemToChange;
						break;
					case 7 : // duplicate
						if(confirm(modx.lang.confirm_resource_duplicate) == true) {
							top.main.document.location.href = "index.php?a=94&id=" + modx.tree.itemToChange;
						}
						break;
					case 8 : // undelete
						if(modx.tree.selectedObjectDeleted) {
							if(confirm("'" + modx.tree.selectedObjectName + "' " + modx.lang.confirm_undelete) === true) {
								top.main.document.location.href = "index.php?a=63&id=" + modx.tree.itemToChange;
							}
						} else {
							alert("'" + modx.tree.selectedObjectName + "'" + modx.lang.not_deleted);
						}
						break;
					case 9 : // publish
						if(confirm("'" + modx.tree.selectedObjectName + "' " + modx.lang.confirm_publish) === true) {
							top.main.document.location.href = "index.php?a=61&id=" + modx.tree.itemToChange;
						}
						break;
					case 10 : // unpublish
						if(modx.tree.itemToChange !== modx.config.site_start) {
							if(confirm("'" + modx.tree.selectedObjectName + "' " + modx.lang.confirm_unpublish) === true) {
								top.main.document.location.href = "index.php?a=62&id=" + modx.tree.itemToChange;
							}
						} else {
							alert('Document is linked to site_start variable and cannot be unpublished!');
						}
						break;
					case 11 : // sort menu index
						top.main.document.location.href = "index.php?a=56&id=" + modx.tree.itemToChange;
						break;
					case 12 : // preview
						w.open(modx.tree.selectedObjectUrl, 'previeWin');
						break;
					default :
						alert('Unknown operation command.');
				}
			},
			hideMenu: function() {
				if(modx.tree._rc) return false;
				d.getElementById('mx_contextmenu').style.visibility = 'hidden';
				let el = d.querySelector('#tree .treeNodeSelectedByContext');
				if(el) el.classList.remove('treeNodeSelectedByContext')
			},
			setSelected: function(elSel) {
				let el = d.querySelector('.treeNodeSelected');
				if(el) el.classList.remove('treeNodeSelected');
				elSel.classList.add('treeNodeSelected')

			},
			setActiveFromContextMenu: function(doc_id) {
				let el = d.querySelector('.treeNodeSelected');
				if(el) el.classList.remove('treeNodeSelected');
				el = d.getElementById('node' + doc_id);
				if(el) el.getElementsByClassName('treeNode')[0].classList.add('treeNodeSelected')
			},
			restoreTree: function() {
				console.log('modx.tree.restoreTree()');
				let el = d.getElementById('buildText');
				if(el) {
					el.innerHTML = modx.style.tree_info + modx.lang.loading_doc_tree;
					el.style.display = 'block'
				}
				modx.tree.rpcNode = d.getElementById('treeRoot');
				modx.get('index.php?a=1&f=nodes&indent=1&parent=0&expandAll=2', function(data) {
					modx.tree.rpcLoadData(data)
				})
			},
			expandTree: function() {
				modx.tree.rpcNode = d.getElementById('treeRoot');
				modx.get('index.php?a=1&f=nodes&indent=1&parent=0&expandAll=1', function(data) {
					modx.tree.rpcLoadData(data)
				})
			},
			collapseTree: function() {
				modx.tree.rpcNode = d.getElementById('treeRoot');
				modx.get('index.php?a=1&f=nodes&indent=1&parent=0&expandAll=0', function(data) {
					modx.openedArray = [];
					modx.tree.saveFolderState();
					modx.tree.rpcLoadData(data);
				})
			},
			updateTree: function() {
				modx.tree.rpcNode = d.getElementById('treeRoot');
				let treeParams = 'a=1&f=nodes&indent=1&parent=0&expandAll=2&dt=' + d.sortFrm.dt.value + '&tree_sortby=' + d.sortFrm.sortby.value + '&tree_sortdir=' + d.sortFrm.sortdir.value + '&tree_nodename=' + d.sortFrm.nodename.value;
				modx.get('index.php?' + treeParams, function(data) {
					modx.tree.rpcLoadData(data)
				})
			},
			getFolderState: function() {
				let a;
				if(modx.openedArray !== [0]) {
					a = "&opened=";
					for(let key in modx.openedArray) {
						if(modx.openedArray[key]) {
							a += key + "|";
						}
					}
				} else {
					a = "&opened=";
				}
				return a;
			},
			saveFolderState: function() {
				modx.get('index.php?a=1&f=nodes&savestateonly=1' + modx.tree.getFolderState())
			},
			showSorter: function() {
				let el = d.getElementById('floater');
				el.classList.toggle('open')
			},
			emptyTrash: function() {
				if(confirm(modx.lang.confirm_empty_trash) === true) {
					top.main.document.location.href = "index.php?a=64";
				}
			},
			showBin: function(a) {
				let el = d.getElementById('treeMenu_emptytrash');
				if(a) {
					el.title = modx.lang.empty_recycle_bin;
					el.classList.remove('disabled');
					el.innerHTML = modx.style.empty_recycle_bin;
					el.onclick = function() {
						modx.tree.emptyTrash()
					}
				} else {
					el.title = modx.lang.empty_recycle_bin_empty;
					el.classList.add('disabled');
					el.innerHTML = modx.style.empty_recycle_bin_empty;
					el.onclick = null
				}
			},
			unlockElement: function(t, i, el) {
				let m = modx.lockedElementsTranslation.msg.replace('[+id+]', i).replace('[+element_type+]', modx.lockedElementsTranslation['type' + t]);
				if(confirm(m) === true) {
					modx.get('index.php?a=67&type=' + t + '&id=' + i, function(data) {
						if(parseInt(data) === 1) modx.animation.fadeOut(el);
						else alert(data)
					})
				}
			},
			resizeTree: function() {
			},
			reloadElementsInTree: function() {
				modx.get('index.php?a=1&f=tree', function(data) {
					// init ElementsInTree
					savePositions();

					let div = d.createElement('div');
					div.innerHTML = data;
					let tabs = div.getElementsByClassName('tab-page');
					let id, _class, el, p, r;
					for(let i = 0; i < tabs.length; i++) {
						if(tabs[i].id !== 'tabDoc') {
							el = tabs[i].getElementsByClassName('panel-group')[0];
							el.style.display = 'none';
							el.classList.add('clone');
							p = d.getElementById(tabs[i].id);
							r = p.getElementsByClassName('panel-group')[0];
							p.insertBefore(el, r);
						}
					}
					setRememberCollapsedCategories();

					for(let i = 0; i < tabs.length; i++) {
						if(tabs[i].id !== 'tabDoc') {
							el = d.getElementById(tabs[i].id).getElementsByClassName('panel-group')[1];
							el.remove();
							el = d.getElementById(tabs[i].id).getElementsByClassName('panel-group')[0];
							el.classList.remove('clone');
							el.style.display = 'block'
						}
					}

					loadPositions();

					initQuicksearch('tree_site_templates_search', 'tree_site_templates');

					// Shift-Mouseclick opens/collapsed all categories
					let at = d.querySelectorAll('#tree .accordion-toggle');
					for(let i = 0; i < at.length; i++) {
						at[i].onclick = function(e) {
							e.preventDefault();
							let thisItemCollapsed = $(this).hasClass("collapsed");
							if(e.shiftKey) {
								// Shift-key pressed
								let toggleItems = $(this).closest(".panel-group").find("> .panel .accordion-toggle");
								let collapseItems = $(this).closest(".panel-group").find("> .panel > .panel-collapse");
								if(thisItemCollapsed) {
									toggleItems.removeClass("collapsed");
									collapseItems.collapse("show");
								} else {
									toggleItems.addClass("collapsed");
									collapseItems.collapse("hide");
								}
								// Save states to localStorage
								toggleItems.each(function() {
									let state = $(this).hasClass("collapsed") ? 1 : 0;
									setLastCollapsedCategory($(this).data("cattype"), $(this).data("catid"), state);
								});
								writeElementsInTreeParamsToStorage();
							} else {
								$(this).toggleClass("collapsed");
								$($(this).attr("href")).collapse("toggle");
								// Save state to localStorage
								let state = thisItemCollapsed ? 0 : 1;
								setLastCollapsedCategory($(this).data("cattype"), $(this).data("catid"), state);
								writeElementsInTreeParamsToStorage();
							}
						}
					}
				})
			}
		},
		setLastClickedElement: function(type, id) {
			localStorage.setItem('MODX_lastClickedElement', '[' + type + ',' + id + ']');
		},
		removeLocks: function() {
			if(confirm(modx.lang.confirm_remove_locks) === true) {
				top.main.document.location.href = "index.php?a=67";
			}
		},
		openCredits: function() {
			top.main.document.location.href = "index.php?a=18";
			setTimeout('modx.main.stopWork()', 2000);
		},
		keepMeAlive: function() {
			modx.get('includes/session_keepalive.php?tok=' + d.getElementById('sessTokenInput').value + '&o=' + Math.random(), function(data) {
				data = JSON.parse(data);
				if(data.status !== 'ok') w.location.href = 'index.php?a=8'
			})
		},
		updateMail: function(now) {
			try {
				if(now) {
					this.post('index.php', {
						updateMsgCount: true
					}, function(data) {
						let counts = data.split(','), el = d.getElementById('msgCounter');
						if(counts[0] > 0) {
							if(el) {
								el.innerHTML = counts[0];
								modx.animation.fadeIn(el)
							}
						} else {
							if(el) modx.animation.fadeOut(el)
						}
						if(counts[1] > 0) {
							el = d.getElementById('newMail');
							if(el) {
								el.innerHTML = '<a href="index.php?a=10" target="main">' + modx.style.email + modx.lang.inbox + ' (' + counts[0] + ' / ' + counts[1] + ')</a>';
								el.style.display = 'block'
							}
						}
					})
				}
			} catch(oException) {
				setTimeout('modx.updateMail(true)', 1000 * 60); // 1000 * 60
			}
		},
		openWindow: function(data) {
			if(typeof data !== 'object') {
				data = {
					"url": data
				}
			}
			if(data.width === undefined)
				data.width = parseInt(w.innerWidth * 0.9) + 'px';
			if(data.height === undefined)
				data.height = parseInt(w.innerHeight * 0.8) + 'px';
			if(data.left === undefined)
				data.left = parseInt(w.innerWidth * 0.05) + 'px';
			if(data.top === undefined)
				data.top = parseInt(w.innerHeight * 0.1) + 'px';
			if(data.title === undefined)
				data.title = Math.floor((Math.random() * 999999) + 1);
			if(data.url !== undefined) {
				if(modx.plugins.EVOmodal === 1) { // used EVO.modal
					top.EVO.modal.show(data)
				} else {
					w.open(data.url, data.title, 'width=' + data.width + ',height=' + data.height + ',top=' + data.top + ',left=' + data.left + ',toolbar=no,location=no,directories=no,status=no,menubar=no,scrollbars=yes,resizable=no');
				}
			}
		},
		getWindowDimension: function() {
			let width = 0, height = 0;
			if(typeof(window.innerWidth ) === 'number') {
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
		},
		animation: {
			fadeIn(a, b) {
				if(a.classList.contains('is-hidden')) a.classList.remove('is-hidden');
				a.style.opacity = 0;
				a.style.display = b || "block";
				(function fade() {
					let val = parseFloat(a.style.opacity);
					if(!((val += .1) > 1)) {
						a.style.opacity = val;
						requestAnimationFrame(fade)
					}
				})()
			},
			fadeOut(a, b) {
				a.style.opacity = 1;
				(function fade() {
					if((a.style.opacity -= .1) < 0) {
						a.style.display = 'none';
						a.classList.add('is-hidden');
						if(b) {
							a.remove();
							a.style.display = 'block';
							a.style.opacity = 1
						}
					} else {
						requestAnimationFrame(fade)
					}
				})()
			}
		},
		XHR: function() {
			let XHR = ('onload' in new XMLHttpRequest()) ? XMLHttpRequest : XDomainRequest;
			return new XHR();
		},
		get: function(a, b) {
			let xhr = this.XHR();
			xhr.open('GET', a, true);
			xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded; charset=UTF-8');
			xhr.onload = function() {
				if(this.status === 200 && typeof b === 'function') {
					return b(this.responseText);
				}
			};
			xhr.send()
		},
		post: function(a, b, c) {
			let xhr = this.XHR(), f = '';
			if(typeof b === 'function') c = b;
			if(typeof b === 'object') {
				let e = [], i = 0, k;
				for(k in b) e[i++] = k + '=' + b[k];
				f = e.join('&');
			}
			xhr.open('POST', a, true);
			xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded; charset=UTF-8');
			xhr.onload = function() {
				if(this.status === 200 && c !== undefined) {
					return c(this.responseText);
				}
			};
			xhr.send(f)
		},
	};

	d.addEventListener('DOMContentLoaded', function() {
		for(let o in _) modx[o] = _[o];
		modx.init()
	})

})(typeof(jQuery) !== 'undefined' ? jQuery : '', window, document, undefined);