(function($) {
		/*
		* 検索機能付き セレクトボックス
		*
		* Copyright (c) 2020 iseyoshitaka
		*/
		$.fn.searchBox = function(opts) {

		// 引数に値が存在する場合、デフォルト値を上書きする
		var settings = $.extend({}, $.fn.searchBox.defaults, opts);

		var init = function (obj) {

			var self = $(obj),
				parent = self.closest('div,tr'),
				searchWord = ''; // 絞り込み文字列

			// 絞り込み検索用のテキスト入力欄の追加
			self.before('<input type="text" class="refineText formTextbox" />');
			var refineText = parent.find('.refineText');
			if (settings.mode === MODE.NORMAL) {
				refineText.attr('readonly', 'readonly');
			}

			// 初期表示で選択済みの場合、絞り込み文言入力欄に選択済みの文言を表示
			var selectedOption = self.find('option:selected');
			if(selectedOption){
				refineText.val(selectedOption.text());
				if (selectedOption.val() === '') {
					if (settings.mode === MODE.TAG) {
						refineText.val("");
					}
				}
			}

			// セレクトボックスの代わりに表示するダミーリストを作成
			var visibleTarget =self.find('option').map(function(i, e) {
				return '<li data-selected="off" data-searchval="' + $(e).val() + '"><span>' + $(e).text() + '</span></li>';
			}).get();
			self.after($('<ul class="searchBoxElement"></ul>').hide());

			// ダミーリストの表示幅をセレクトボックスにあわせる
			var refineTextWidth = (settings.elementWidth) ? settings.elementWidth : self.width();
			refineText.css('width', refineTextWidth);
			parent.find('.searchBoxElement').css('width', refineTextWidth);

			// 元のセレクトボックスは非表示にする
			self.hide();

			// Helper function to select an item
			function selectItem(item) {
				var searchval = item.data('searchval');
				self.val(searchval).change();
				parent.find('li').attr('data-selected', 'off');
				item.attr('data-selected', 'on');
				parent.find('.searchBoxElement').hide();
				
				// Set focus to another element to prevent further typing in the input
				refineText.blur();
				
				// Make the input field read-only after selection
				if (settings.mode !== MODE.TAG) {
					setTimeout(function() {
						// We need to delay this a bit to ensure it works after change events
						refineText.prop('readonly', true);
						
						// Give it a visual indication that it's read-only but can be clicked
						refineText.css('cursor', 'pointer');
					}, 100);
				}
				
				currentIndex = -1;
			}

			// ダミーリストを検索条件で絞り込みます。
			var changeSearchBoxElement = function() {
				if (searchWord !== '') {
					var matcher = new RegExp(searchWord.replace(/\\/g, '\\\\'), "i");
					var filterTarget = $(visibleTarget.join()); // 配列のコピー
					filterTarget = filterTarget.filter(function(){
						return $(this).text().match(matcher);
					});
					parent.find('.searchBoxElement').empty();
					parent.find('.searchBoxElement').html(filterTarget);
					parent.find('.searchBoxElement').show();
				} else {
					parent.find('.searchBoxElement').empty();
					parent.find('.searchBoxElement').html(visibleTarget.slice(0, settings.optionMaxSize).join(''));
					parent.find('.searchBoxElement').show();
				}

				// Reset keyboard navigation index when list changes
				currentIndex = -1;

				// 選択中のLIタグの背景色を変更します。
				var selectedOption = self.find('option:selected');
				if(selectedOption){
					parent.find('.searchBoxElement').find('li').removeClass('selected');
					parent.find('.searchBoxElement').find('li[data-searchval="' + selectedOption.val() + '"]').addClass('selected');
				}

				// Add hover effect for mouse interaction
				parent.find('.searchBoxElement').find('li')
					.off('mouseenter mouseleave')
					.hover(
						function() {
							// On mouse enter
							parent.find('.searchBoxElement').find('li').removeClass('selected');
							$(this).addClass('selected');
							// Update current index for keyboard navigation coordination
							currentIndex = $(this).index();
						},
						function() {
							// On mouse leave
							$(this).removeClass('selected');
							// Reset current index when mouse leaves
							if (currentIndex === $(this).index()) {
								currentIndex = -1;
							}
						}
					);

				// ダミーリスト選択時
				parent.find('.searchBoxElement').find('li').click(function(e){
					e.preventDefault();
					// e.stopPropagation();
					selectItem($(this));
				});

			};

			// Keyboard navigation variables
			var currentIndex = -1;
			
			// Handle keyboard navigation events
			refineText.on('keydown', function(e) {
				// Only process if the dropdown is visible
				if (!parent.find('.searchBoxElement').is(':visible')) {
					if (e.keyCode === 40 || e.keyCode === 38) {
						parent.find('.searchBoxElement').show();
						changeSearchBoxElement();
					} else {
						return true;
					}
				}
				
				var visibleItems = parent.find('.searchBoxElement li:visible');
				var itemCount = visibleItems.length;
				
				switch (e.keyCode) {
					// Arrow down
					case 40:
						e.preventDefault();
						currentIndex = (currentIndex < itemCount - 1) ? currentIndex + 1 : 0;
						highlightItem(visibleItems, currentIndex);
						return false;
						
					// Arrow up
					case 38:
						e.preventDefault();
						currentIndex = (currentIndex > 0) ? currentIndex - 1 : itemCount - 1;
						highlightItem(visibleItems, currentIndex);
						return false;
						
					// Enter key - select the current item
					case 13:
						if (currentIndex >= 0) {
							e.preventDefault();
							var selectedItem = visibleItems.eq(currentIndex);
							if (selectedItem.length) {
								selectItem(selectedItem);
								return false;
							}
						}
						break;
						
					// Tab key - select highlighted item and move to next element
					case 9:
						if (currentIndex >= 0) {
							var selectedItem = visibleItems.eq(currentIndex);
							if (selectedItem.length) {
								e.preventDefault();
								selectItem(selectedItem);
								// Focus on the next focusable element
								setTimeout(function() {
									refineText.closest('form').find('input,select,textarea').not(refineText).first().focus();
								}, 10);
								return false;
							}
						}
						// If no item is selected, hide the dropdown
						parent.find('.searchBoxElement').hide();
						break;
						
					// Escape key - hide dropdown
					case 27:
						parent.find('.searchBoxElement').hide();
						currentIndex = -1;
						break;
				}
			});
			
			// Helper function to highlight the selected item
			function highlightItem(items, index) {
				items.removeClass('selected');
				if (index >= 0 && items.eq(index).length) {
					var item = items.eq(index);
					item.addClass('selected');
					
					// Ensure the selected item is visible in the dropdown
					var container = parent.find('.searchBoxElement');
					container.scrollTop(item.offset().top - container.offset().top + container.scrollTop() - (container.height() / 2));
				}
			}
			
			// keyup for regular typing/searching
			refineText.keyup(function(e){
				// Skip for navigation keys
				if (e.keyCode !== 40 && e.keyCode !== 38 && e.keyCode !== 13 && e.keyCode !== 27) {
					searchWord = $(this).val();
					// Reset the current index when search query changes
					currentIndex = -1;
					// ダミーリストをリフレッシュ
					changeSearchBoxElement();
				}
			});

			// セレクトボックス変更時
			self.change(function(){
				// 直近の絞り込み文言エリアへ選択オプションのテキストを反映
				var selectedOption = $(this).find('option:selected');
				searchWord = selectedOption.text();
				refineText.val(selectedOption.text());

				if (settings.selectCallback) {
					settings.selectCallback({
						selectVal: selectedOption.attr('value'),
						selectLabel: selectedOption.text()
					});
				}
			});

			// テキストボックスをクリックした場合はダミーリストを表示する
			refineText.click(function(e) {
				e.preventDefault();

				// Make input editable again when clicked
				refineText.prop('readonly', false);
				
				// モードに合わせて設定
				if (settings.mode === MODE.NORMAL) {
					searchWord = '';
				} else if (settings.mode === MODE.INPUT) {
					refineText.val('');
					searchWord = '';
				} else if (settings.mode === MODE.TAG) {
					var selectedOption = self.find('option:selected');
					if (selectedOption.val() === '') {
						refineText.val('');
						searchWord = '';
					}
				}

				// ダミーリストをリフレッシュ
				parent.find('.searchBoxElement').hide();
				changeSearchBoxElement();

			});

			// セレクトボックスの外をクリックした場合はダミーリストを非表示にする。
			$(document).click(function(e){
				if($(e.target).hasClass('refineText')){
					return;
				}
				parent.find('.searchBoxElement').hide();
				if (settings.mode !== MODE.TAG) {
					var selectedOption = self.find('option:selected');
					searchWord = selectedOption.text();
					refineText.val(selectedOption.text());
				}
			});

		}

		$(this).each(function (){
			init(this);
		});

		return this;
	}

	var MODE = {
		NORMAL: 0, // 通常のセレクトボックス
		INPUT: 1, // 入力式セレクトボックス
		TAG: 2 // タグ追加式セレクトボックス
	};

	$.fn.searchBox.defaults = {
		selectCallback: null, // 選択後に呼ばれるコールバック
		elementWidth: null, // セレクトボックスの表示幅
		optionMaxSize: 100, // セレクトボックス内に表示する最大数
		mode: MODE.INPUT // 表示モード
	};

})(jQuery);
