(function( $ ) {
	'use strict';

	/**
	 * All of the code for your admin-facing JavaScript source
	 * should reside in this file.
	 *
	 * Note: It has been assumed you will write jQuery code here, so the
	 * $ function reference has been prepared for usage within the scope
	 * of this function.
	 *
	 * This enables you to define handlers, for when the DOM is ready:
	 *
	 * $(function() {
	 *
	 * });
	 *
	 * When the window is loaded:
	 *
	 * $( window ).load(function() {
	 *
	 * });
	 *
	 * ...and/or other possibilities.
	 *
	 * Ideally, it is not considered best practise to attach more than a
	 * single DOM-ready or window-load handler for a particular page.
	 * Although scripts in the WordPress core, Plugins and Themes may be
	 * practising this, we should strive to set a better example in our own work.
	 */

	// 一覧画面のやつ
	$(function() {

		// 結果取得中のやつが存在するか確認する。
		$('.ccd_result_get').each(function(index, element) {
			var queueId = $(this).data('ccd_queue_id');
			var postId = $(this).data('ccd_post_id');

			getResult(queueId, postId);
		});

		// 残りのAPI回数を取得
		getApiRemainCount();

		// 一覧チェック実行ボタンを押した。
		$('.ccd_list_header_button').click(function(){

			if(confirm("チェックされている記事の一括チェックを行います。よろしいですか？")) {
				$('[name="post\[\]"]:checked').each(function(){
					$("#ccd_execute_button_" + $(this).val()).trigger('click');
				});
			}
		});

		// ボタンを押した時の処理
		$('.js-ccd_execute_check_button-list').click(function(){

			// 対象の自分を無効にする。
			$(this).attr("disabled", "disabled");

			var postId = $(this).data('ccd_post_id');

			// 表記をクリア
			$("#ccd_execute_result_" + postId).html('');

			// AJAXで投稿開始
			$.ajax({
				type: 'POST',
				url: ajaxurl,
				data: {
					"action": "ccd_check_execute_from_list",
					"postId" : postId
				}
			}).done(function (res) {
				//console.log(res);
				getApiRemainCount();
				if (res['status'] == 1 && res['queue_id']) {
					// 次へ移動する。
					getResult(res['queue_id'], postId);
				} else {
					//console.log(res);
					$('#ccd_execute_result_' + res['post_id']).html(res['message']);
					$('#ccd_execute_button_').removeAttr("disabled");
				}
			}).fail(function (xhr, status, error) {
				console.log(res);
				alert('データの登録に失敗しました。再読み込み後に再度実行してください。');
			}).always(function () {

			});
		});

		// APIの残り回数の取得
		function getApiRemainCount() {
			$.ajax({
				type: 'POST',
				url: ajaxurl,
				data: {
					"action": "ccd_check_remain",
				}
			}).done(function (res) {
				//console.log(res);
				if (res['status'] == 1) {
					// 取得できた
					$('#ccd_list_header_button').val('コピペチェック一括チェック(API残:' + res['result']['api_remain'] + ' 文字残:' + res['result']['string_remain'] + ')');

				} else {
					// 取得できなかった
					$('#ccd_list_header_button').val('コピペチェック一括チェック(API残数が取得出来ませんでした)');
					$('#ccd_list_header_button').attr("disabled", "disabled");
				}
			}).fail(function (xhr, status, error) {

			}).always(function () {

			});
		}

		// 対象の結果情報を取得する。
		// 結果を取得します。
		function getResult(queueId, postId) {

			var buttonId = '#ccd_execute_button_' + postId;
			$(buttonId).attr("disabled", "disabled");

			$.ajax({
				type: 'POST',
				url: ajaxurl,
				data: {
					"action": "ccd_check_result_from_list",
					"queueId": queueId,
					"postId" : postId
				}
			}).done(function (res) {
				console.log(res);
				// データが取得できた。
				if (res['status'] == 1) {
					// 結果を表示
					$('#ccd_execute_button_' + res['post_id']).removeAttr("disabled");

					var display = res['check_date'] + 'に実施 <br>';

					display += "<span class='ccd-result-item'>";
					display += "<b>";
					display += "<div class='ccd-list-width'>";
					display += "■類似度: ";
					display += "<span class='" + res['result']['ruiji']['color'] + "'>";
					display += res['result']['ruiji']['percent'] + "%";
					display += "</span>";
					display += "</div>";
					display += "<div class='" + res['result']['ruiji']['color'] + "'>";
					display += "【"+ res['result']['ruiji']['string'] +"】";
					display += "</div>";
					display += "</b>";
					display += "</span>";

					display += "<span class='ccd-result-item'>";
					display += "<b>";
					display += "<div class='ccd-list-width'>";
					display += "■一致率: ";
					display += "<span class='" + res['result']['kikai']['color'] + "'>";
					display += res['result']['kikai']['percent'] + "%";
					display += "</span>";
					display += "</div>";
					display += "<div class='" + res['result']['kikai']['color'] + "'>";
					display += "【"+ res['result']['kikai']['string'] +"】";
					display += "</div>";
					display += "</b>";
					display += "</span>";

					display += "<span class='ccd-result-item'>";
					display += "<b>";
					display += "<div class='ccd-list-width'>";
					display += "■類似度: ";
					display += "<span class='" + res['result']['text']['color'] + "'>";
					display += res['result']['text']['percent'] + "%";
					display += "</span>";
					display += "</div>";
					display += "<div class='" + res['result']['text']['color'] + "'>";
					display += "【"+ res['result']['text']['string'] +"】";
					display += "</div>";
					display += "</b>";

					display += "</span>";

					display += "<a href='" + res['site_url'] + "Result/detail?id=" + res['post_id'] + "' target='_blank' class='ccd-link'>【結果を見る】</a>";

					$('#ccd_execute_result_' + res['post_id']).html(display);

				} else if (res['status'] == 2) {
					// まだ取得中。もう一回
					setTimeout(function(){getResult(res['queue_id'], res['post_id'])},10000);

					var text = $("#ccd_execute_result_" + res['post_id']).html();
					$("#ccd_execute_result_" + res['post_id']).html(text + '.');

				} else if (res['status'] == 3) {

					var buttonId = '#ccd_execute_button_' + res['post_id'];

					// エラー発生。再実行の対象とする。
					$(buttonId).val('コピペチェックでエラー(再実行)'); // ボタンの表記を変更
					//$('.ccd_result_disp').html('エラーが発生しました。しばらく待ってから再度実行してください。');
					$(buttonId).data('ccd-error', 1); // エラーフラグを立てる。
					$(buttonId).data('ccd-restart', res['queue_id']); // リスタートのIDを設定する。
					$(buttonId).removeAttr("disabled");

				} else {

					var buttonId = '#ccd_execute_button_' + res['post_id'];

					// なんかよくわからん例外。
					console.log(res);
					$('#ccd_execute_result_' + res['post_id']).html('エラーが発生しました。しばらく待ってから再度実行してください。');
					$(buttonId).removeAttr("disabled");
				}
			}).fail(function (xhr, status, error) {
				//alert('CopyContentDetector 結果の取得に失敗しました。再読込してください。');
			}).always(function () {

			});
		}

	});

	// 管理画面のヤツ
	$(function() {
		var is_ccd_running = false;
		var buttonObject = $('.js-ccd_execute_check_button');

		// 初期実行のチェック。
		var restartQueueId = $(buttonObject).data('ccd-restart');
		var errorFlg = $(buttonObject).data('ccd-error');
		if (restartQueueId) {
			//エラーのときは再実行ボタンを表示
			// 途中のときは途中を表示
			if (!errorFlg) {
				var postId = $(buttonObject).data('ccd-post-id');
				$('.ccd_result_disp').html('コピペチェック実行中です');

				//console.log('取得途中のデータが見つかった。');
				getResult(restartQueueId, postId);
			}
		}

		// エラーのときは表示を変更しない。再実行ボタン。
		if (!errorFlg) {
			getApiRemainCount();
		}

		//ここから関数宣言
		$('.js-ccd_execute_check_button').click(function(){
			if (is_ccd_running) {
				alert('現在コピペチェック実行中です。しばらくお待ち下さい。');
				return;
			}

			// リスタートのIDとエラーフラグを取得。
			var restartQueueId = $(buttonObject).data('ccd-restart');
			var errorFlg = $(buttonObject).data('ccd-error');

			// オブジェクト情報を設定
			buttonObject = this;
			$(this).attr("disabled", "disabled");
			$('.ccd_result_disp').html('コピペチェック実行中です');
			$('.wp-editor-tabs').addClass('wp-editor-tabs-heightexecute');

			if (errorFlg && restartQueueId) {
				restartCopyContentDetector($(this).data('ccd-post-id'));
			} else {
				executeCopyContentDetector($("#wp-content-wrap textarea").val(), $(this).data('ccd-post-id'));
			}
		});

		// 結果の表示
		$(document).on('click', '.ccd-js-dispresult', function(){
			var dispStatus = $(this).data('resultdisp');

			var labelDisp = $(this).data('labeldisp');
			var labelHide = $(this).data('labelhide');

			if (!labelDisp) {
				labelDisp = '【結果の表示】';
			}
			if (!labelHide) {
				labelHide = '【結果の非表示】';
			}

			if(dispStatus == false) {
				$(this).data('resultdisp', true);
				$(this).html(labelHide);
				$('.ccd-iframe').slideDown('slow');
			} else {
				$(this).data('resultdisp', false);
				$(this).html(labelDisp);
				$('.ccd-iframe').slideUp('slow');
			}
		});

		// API回数を取得する
		function getApiRemainCount() {
			// APIの残りを取得
			$.ajax({
				type: 'POST',
				url: ajaxurl,
				data: {
					"action": "ccd_check_remain",
				}
			}).done(function (res) {
				if (res['status'] == 1) {
					// 取得できた
					$('#ccd_execute_check').val('コピペチェック実行(API残:' + res['result']['api_remain'] + ' 文字残:' + res['result']['string_remain'] + ')');

				} else {
					// 取得できなかった
					$('#ccd_execute_check').val('コピペチェック実行(API残数が取得出来ませんでした)');
					$('#ccd_execute_check').attr("disabled", "disabled");
				}
			}).fail(function (xhr, status, error) {

			}).always(function () {

			});
		}

		/**
		* エラーIDの再実行
		*/
		function restartCopyContentDetector(postId) {
			$.ajax({
				type: 'POST',
				url: ajaxurl,
				data: {
					"action": "ccd_check_restart",
					"postId" : postId
				}
			}).done(function (res) {
				if (res['status'] == 1 && res['queue_id']) {
					// 次へ移動する。
					getResult(res['queue_id'], postId);
				} else {
					alert(res['message']);
					is_ccd_running = false;
					$(buttonObject).removeAttr("disabled");
				}
			}).fail(function (xhr, status, error) {
				console.log(res);
				$('.ccd_result_disp').html('エラーが発生しました。しばらく待ってから再度実行してください。');
				alert('データの登録に失敗しました。再度実行してください。');
				is_ccd_running = false;
				$(buttonObject).removeAttr("disabled");
			}).always(function () {

			});

			return false;
		}

		//　実行開始。
		function executeCopyContentDetector(textData, postId) {
			$.ajax({
				type: 'POST',
				url: ajaxurl,
				data: {
					"action": "ccd_check_execute",
					"textData": textData,
					"postId" : postId
				}
			}).done(function (res) {
				// 回数の再取得
				getApiRemainCount();
				if (res['status'] == 1 && res['queue_id']) {
					// 次へ移動する。
					getResult(res['queue_id'], postId);
				} else {
					is_ccd_running = false;
					$('.ccd_result_disp').html(res['message']);
					$(buttonObject).removeAttr("disabled");
				}
			}).fail(function (xhr, status, error) {
				console.log(res);
				$('.ccd_result_disp').html('エラーが発生しました。しばらく待ってから再度実行してください。');
				alert('データの登録に失敗しました。再度実行してください。');
				is_ccd_running = false;
				$(buttonObject).removeAttr("disabled");
			}).always(function () {

			});

			return false;
		}

		// 結果を取得します。
		function getResult(queueId, postId) {

			$(buttonObject).attr("disabled", "disabled");
			is_ccd_running = true;

			$.ajax({
				type: 'POST',
				url: ajaxurl,
				data: {
					"action": "ccd_check_result",
					"queueId": queueId,
					"postId" : postId
				}
			}).done(function (res) {
				if (res['status'] == 1) {
					// 結果を表示
					is_ccd_running = false;
					$(buttonObject).removeAttr("disabled");

					var display = res['check_date'] + 'に実施 ';
					display += '<span class="ccd-result-item"><b>■類似度:<span class="' + res['result']['ruiji']['color'] + '">' + res['result']['ruiji']['percent'] + '%【' + res['result']['ruiji']['string'] + '】</span></b></span>';
					display += ' <span class="ccd-result-item"><b>■一致率:<span class="' + res['result']['kikai']['color'] + '">' + res['result']['kikai']['percent'] + '%【' + res['result']['kikai']['string'] + '】</span></b></span>';
					display += ' <span class="ccd-result-item"><b>■ﾃｷｽﾄ間:<span class="' + res['result']['text']['color'] + '">' + res['result']['text']['percent'] + '% 【' + res['result']['text']['string'] + '】</span></b></span>';


					display += "<span class='ccd-js-dispresult ccd-link' data-resultdisp='false'>【結果の表示】</span>";
					display += "<iframe src='" + res['site_url'] + "Result/detail?id=" + res['queue_id'] + "&api_key=" + $('input:hidden[name="ccd_api_key"]').val() + "' width='100%' height='600px' frameborder='0' class='ccd-iframe'></iframe>";

					$('.ccd_result_disp').html(display);

				} else if (res['status'] == 2) {
					// まだ取得中。もう一回
					setTimeout(function(){getResult(queueId, postId)},10000);

					var text = $('.ccd_result_disp').html();
					$('.ccd_result_disp').html(text + '.');

				} else if (res['status'] == 3) {

					// エラー発生。再実行の対象とする。
					is_ccd_running = false;
					$(buttonObject).val('コピペチェックでエラー(再実行)'); // ボタンの表記を変更
					$('.ccd_result_disp').html('エラーが発生しました。しばらく待ってから再度実行してください。');
					$(buttonObject).data('ccd-error', 1); // エラーフラグを立てる。
					$(buttonObject).data('ccd-restart', queueId); // リスタートのIDを設定する。
					$(buttonObject).removeAttr("disabled");

				} else {
					// なんかよくわからん例外。
					console.log(res);
					$('.ccd_result_disp').html('エラーが発生しました。しばらく待ってから再度実行してください。');
					alert(res['message']);
					is_ccd_running = false;
					$(buttonObject).removeAttr("disabled");
				}
			}).fail(function (xhr, status, error) {
				$('.ccd_result_disp').html('エラーが発生しました。しばらく待ってから再度実行してください。');
				alert('データの登録に失敗しました。再度実行してください。');
				is_ccd_running = false;
				$(buttonObject).removeAttr("disabled");
			}).always(function () {

			});
		}
	});

})( jQuery );
