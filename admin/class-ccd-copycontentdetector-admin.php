<?php

/**
 * The admin-specific functionality of the plugin.
 *
 * @link       http://new-system-create.co.jp
 * @since      1.0.0
 *
 * @package    Ccd_Copycontentdetector
 * @subpackage Ccd_Copycontentdetector/admin
 */

/**
 * The admin-specific functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the admin-specific stylesheet and JavaScript.
 *
 * @package    Ccd_Copycontentdetector
 * @subpackage Ccd_Copycontentdetector/admin
 * @author     Sumito Umeda <umeda@new-system-create.co.jp>
 */
class Ccd_Copycontentdetector_Admin {

	/**
	 * The ID of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $plugin_name    The ID of this plugin.
	 */
	private $plugin_name;

	/**
	 * The version of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $version    The current version of this plugin.
	 */
	private $version;

	/** 設定値 */
	private $options;

	/**
	 * Initialize the class and set its properties.
	 *
	 * @since    1.0.0
	 * @param      string    $plugin_name       The name of this plugin.
	 * @param      string    $version    The version of this plugin.
	 */
	public function __construct( $plugin_name, $version ) {

		$this->plugin_name = $plugin_name;
		$this->version = $version;

	}

	/**
	 * Register the stylesheets for the admin area.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_styles() {

		/**
		 * This function is provided for demonstration purposes only.
		 *
		 * An instance of this class should be passed to the run() function
		 * defined in Ccd_Copycontentdetector_Loader as all of the hooks are defined
		 * in that particular class.
		 *
		 * The Ccd_Copycontentdetector_Loader will then create the relationship
		 * between the defined hooks and the functions defined in this
		 * class.
		 */

		wp_enqueue_style( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'css/ccd-copycontentdetector-admin.css', array(), $this->version, 'all' );

	}

	/**
	 * Register the JavaScript for the admin area.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_scripts() {

		/**
		 * This function is provided for demonstration purposes only.
		 *
		 * An instance of this class should be passed to the run() function
		 * defined in Ccd_Copycontentdetector_Loader as all of the hooks are defined
		 * in that particular class.
		 *
		 * The Ccd_Copycontentdetector_Loader will then create the relationship
		 * between the defined hooks and the functions defined in this
		 * class.
		 */

		wp_enqueue_script( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'js/ccd-copycontentdetector-admin.js', array( 'jquery' ), $this->version, false );

	}

	/**
	* CCDのメニューを追加します。
	*/
	public function ccd_add_menu() {
		// メニュー表示設定
		add_menu_page( 'CCD設定', 'CCD設定', 'administrator', 'ccd_setting', array( $this, 'ccd_setting_view' ) );
	}

	// 3つ目、設定画面用のHTML
	public function ccd_setting_view() {

		// 値を戻す。
		$this->options = get_option( 'ccd_setting' );

		global $parent_file;
		if ( $parent_file != 'options-general.php' ) {
			require(ABSPATH . 'wp-admin/options-head.php');
		}

		$siteUrl = 'https://ccd.cloud/';
		if (!empty($this->options['api_url'])) {
			$siteUrl = $this->options['api_url'];
		}

		echo '<form method="post" action="options.php">';
		echo '<h1>CopyContentDetector管理画面</h1>';

		// 隠しフィールドなどを出力します(register_setting()の$option_groupと同じものを指定)。
		settings_fields( 'ccd_setting' );
		// 入力項目を出力します(設定ページのslugを指定)。
		do_settings_sections( 'ccd_setting' );

		// 送信ボタンを出力します。
		submit_button();
		echo '</form>';

		echo "<a href='{$siteUrl}User/registration' target='_blank'>■ユーザ登録はこちらから</a><br>";
		echo "<a href='{$siteUrl}Pay/pay_setting' target='_blank'>■有料プランの契約はこちらから</a><br>";
		echo "<a href='{$siteUrl}MyPage/wpApi' target='_blank'>■プラン契約後のAPIキーの確認はこちらから</a>";
	}

	/**
	*　設定画面の設定
	*/
	public function ccd_setting_init() {
		// 項目の設定
		register_setting( 'ccd_setting', 'ccd_setting', array( $this, 'sanitize' ) );

		// セクション情報の設定
		add_settings_section( 'ccd_setting_section_id', '', '', 'ccd_setting' );

		// フィールド情報の設定
		add_settings_field( 'api_key', 'CCDのAPIキー', array( $this, 'api_key_callback' ), 'ccd_setting', 'ccd_setting_section_id' );

		add_settings_field( 'del_domain', 'このブログのURL', array( $this, 'del_domain_callback' ), 'ccd_setting', 'ccd_setting_section_id' );

		add_settings_field( 'api_url', 'CCD_APIURL', array( $this, 'api_url_callback' ), 'ccd_setting', 'ccd_setting_section_id' );

		// 検索フォーム表示・非表示
		add_settings_field( 'search_like_dropdown', '類似度絞り込み', array( $this, 'search_like_dropdown_callback' ), 'ccd_setting', 'ccd_setting_section_id' );
		add_settings_field( 'search_match_dropdown', '一致率絞り込み', array( $this, 'search_match_dropdown_callback' ), 'ccd_setting', 'ccd_setting_section_id' );
		add_settings_field( 'search_text_dropdown', 'テキスト間絞り込み', array( $this, 'search_text_dropdown_callback' ), 'ccd_setting', 'ccd_setting_section_id' );
	}

	/**
	* 入力項目(「メッセージ」)のHTMLを出力します。
	*/
	public function api_key_callback() {
		// 値を取得
		$apiKey = isset( $this->options['api_key'] ) ? $this->options['api_key'] : '';

		// nameの[]より前の部分はregister_setting()の$option_nameと同じ名前にします。
		echo '<input type="text" id="api_key" name="ccd_setting[api_key]" class="ccd-input-form" value="';
		esc_attr_e($apiKey);
		echo '"/>';
	}

	/**
	* ブログのURL入力
	*/
	public function del_domain_callback() {
		// 値を取得
		$delDomain = isset( $this->options['del_domain'] ) ? $this->options['del_domain'] : '';

		// nameの[]より前の部分はregister_setting()の$option_nameと同じ名前にします。
		echo '<input type="text" id="del_domain" name="ccd_setting[del_domain]" class="ccd-input-form" value="';
		esc_attr_e($delDomain);
		echo '"/><br>※このURLを除外してコピペチェックを行います。設定が間違っていると正しくチェック出来ない場合があります。';
	}

	/**
	* APIURLの設定
	*/
	public function api_url_callback() {
		// 値を取得
		$apiUrl = isset( $this->options['api_url'] ) ? $this->options['api_url'] : 'https://ccd.cloud/';

		// nameの[]より前の部分はregister_setting()の$option_nameと同じ名前にします。
		echo '<input type="text" id="api_url" name="ccd_setting[api_url]" class="ccd-input-form" value="';
		esc_attr_e($apiUrl);
		echo '"/><br>※このパラメータは通常は変更しないでください。企業アカウント、専有サーバ運用のときのみ変更が必要です。';
	}

	// 類似度の検索ドロップダウン表示
	public function search_like_dropdown_callback() {
		$option = isset( $this->options['search_like_dropdown'] ) ? $this->options['search_like_dropdown'] : 0;
		echo '<input type="checkbox" id="search_like_dropdown" name="ccd_setting[search_like_dropdown]" value="1" ' . checked( 1, $option, false ) . ' /> 表示する';
	}

	// 一致率の検索ドロップダウン表示
	public function search_match_dropdown_callback() {
		$option = isset( $this->options['search_match_dropdown'] ) ? $this->options['search_match_dropdown'] : 0;
		echo '<input type="checkbox" id="search_match_dropdown" name="ccd_setting[search_match_dropdown]" value="1" ' . checked( 1, $option, false ) . ' /> 表示する';
	}

	// テキスト間の検索ドロップダウン表示
	public function search_text_dropdown_callback() {
		$option = isset( $this->options['search_text_dropdown'] ) ? $this->options['search_text_dropdown'] : 0;
		echo '<input type="checkbox" id="search_text_dropdown" name="ccd_setting[search_text_dropdown]" value="1" ' . checked( 1, $option, false ) . ' /> 表示する';
	}

	/**
	* 送信された入力値の調整を行います。
	*
	* @param array $input 設定値
	*/
	public function sanitize( $input ) {

		// DBの設定値を取得します。
		$this->options = get_option( 'ccd_setting' );

		$newInput = array();

		// メッセージがある場合値を調整
		if( isset( $input['api_key'] ) && trim( $input['api_key'] ) !== '' ) {
			$newInput['api_key'] = sanitize_text_field( $input['api_key'] );
		} else {
			add_settings_error( 'ccd_setting', 'api_key', 'APIキーを入力してください。' );

			// 値をDBの設定値に戻します。
			$newInput['api_key'] = isset( $this->options['api_key'] ) ? $this->options['api_key'] : '';
		}

		// メッセージがある場合値を調整
		if( isset( $input['del_domain'] ) && trim( $input['del_domain'] ) !== '' ) {
			$newInput['del_domain'] = sanitize_text_field( $input['del_domain'] );
		} else {
			add_settings_error( 'ccd_setting', 'del_domain', 'このブログのURLを入力してください。' );

			// 値をDBの設定値に戻します。
			$newInput['del_domain'] = isset( $this->options['del_domain'] ) ? $this->options['del_domain'] : '';
		}

		// メッセージがある場合値を調整
		if( isset( $input['api_url'] ) && trim( $input['api_url'] ) !== '' ) {
			$newInput['api_url'] = sanitize_text_field( $input['api_url'] );

			// 一番うしろが/で終わっていないときは/をつける
			$lastWord = mb_substr($newInput['api_url'], -1);
			if ($lastWord !== '/') {
				$newInput['api_url'] = $newInput['api_url'] . '/';
			}
		} else {
			// からのときはデフォルトにもどす
			$newInput['api_url'] = 'https://ccd.cloud/';
		}

		// 類似度検索表示
		if( isset( $input['search_like_dropdown'] ) && trim( $input['search_like_dropdown'] ) !== '' ) {
			$newInput['search_like_dropdown'] = 1;			
		} else {
			// からのときは0にする
			$newInput['search_like_dropdown'] = 0;
		}

		// 一致率検索表示
		if( isset( $input['search_match_dropdown'] ) && trim( $input['search_match_dropdown'] ) !== '' ) {
			$newInput['search_match_dropdown'] = 1;			
		} else {
			// からのときは0にする
			$newInput['search_match_dropdown'] = 0;
		}

		// テキスト間検索表示
		if( isset( $input['search_text_dropdown'] ) && trim( $input['search_text_dropdown'] ) !== '' ) {
			$newInput['search_text_dropdown'] = 1;			
		} else {
			// からのときは0にする
			$newInput['search_text_dropdown'] = 0;
		}

		return $newInput;
	}

	/**
	* 編集画面したにボタンを登録します。
	*/
	public function ccd_add_execute_button_for_edit() {

		// 記事投稿以外の場合は表示しない
		$postType = get_post_type( get_the_ID() );
		if ($postType != 'post' && $postType != 'edit' && $postType != 'page') {
			return;
		}


		echo '<h1>CopyContentDetectorによるコピペチェック</h1>';

		$options = get_option( 'ccd_setting' );
		if(empty($options['api_key'])) {
			echo '<a href="https://ccd.cloud/" target="_blank">【WEB版CopyContentDetectorを開く】</a><br>';
			echo "APIキーが未設定のため直接コピペチェックは無効です。APIキーを設定すると直接コピペチェックが可能になります。";
			return;
		}

		echo '<div><small>ACFなどカスタムフィールドを利用した記事をチェックする場合は、ショートコードを作成する必要があります。</small></div>';
		echo "<input type='hidden' name='ccd_api_key' value='{$options['api_key']}'>";

		// メタ情報に入っている情報があれば取得する。
		$resultInfo = get_post_meta(get_the_ID(), 'ccd-result');
		$resultString = '';
		$restartFlg = 0;
		$errorFlg = 0;
		$siteUrl = 'https://ccd.cloud/';
		$buttonString = 'コピペチェック実行(-)';

		// オプションからサイトのURLを取得する。
		if (!empty($options['api_url'])) {
			$siteUrl = $options['api_url'];
		}

		// 設定一覧
		if (!empty($resultInfo[0][$options['api_key']])) {
			$queueId = $resultInfo[0][$options['api_key']]['queue_id'];
			$resultList = $resultInfo[0][$options['api_key']]['result'];

			// もしこの時点で結果がない場合は、途中で離脱したことが考えられるので再度取得する。
			if (!empty($resultInfo[0][$options['api_key']]['result'])) {

				// エラーになっているときは再実行させる。
				if ($resultInfo[0][$options['api_key']]['result'] == 'error_result') {
					$errorFlg = 1;
					$restartFlg = $queueId;
					$buttonString = "コピペチェックでエラーが発生しました(再実行)";
				} else {

					$resultString = "{$resultInfo[0][$options['api_key']]['check_date']} に実施 ";

					$ruijiInfo = $this->getResultString($resultList['web_like_info']['like_status']);
					$resultString .= "<span class='ccd-result-item'><b>■類似度:<span class='{$ruijiInfo['color']}'>{$resultList['web_like_info']['like_percent']}%【{$ruijiInfo['string']}】</span></b></span> ";

					$kikaiInfo = $this->getResultString($resultList['web_match_info']['match_status']);
					$resultString .= "<span class='ccd-result-item'><b>■一致率:<span class='{$kikaiInfo['color']}'>{$resultList['web_match_info']['match_percent']}%【{$kikaiInfo['string']}】</span></b></span> ";

					$textInfo = $this->getResultString($resultList['text_match_info']['text_match_status']);
					$resultString .= "<span class='ccd-result-item'><b>■ﾃｷｽﾄ間:<span class='{$textInfo['color']}'>{$resultList['text_match_info']['text_match_percent']}% 【{$textInfo['string']}】</span></b></span> ";
					$resultString .= "<span class='ccd-js-dispresult ccd-link' data-resultdisp='false'>【結果の表示】</span>";

					// IRFAMEによる結果表示。
					$resultString .= "<iframe src='{$siteUrl}Result/detail?id={$queueId}&api_key={$options['api_key']}' width='100%' height='600px' frameborder='0' class='ccd-iframe'></iframe>";
				}
			} else {
				$restartFlg = $queueId;
			}
		}

		echo '<input type="button" name="ccd_execute_check" id="ccd_execute_check" value="' . $buttonString . '" class="button js-ccd_execute_check_button" data-ccd-post-id="' . get_the_ID() . '" data-ccd-restart="' . $restartFlg . '" data-ccd-error="' . $errorFlg .'"/><div class="ccd_result_disp">' . $resultString . '</div>';
	}

	/**
	* 一覧画面上　ヘッダ部分に登録します。
	*/
	public function ccd_add_execute_button_for_list() {

		// APIキーが存在しなければ、何も表示市内
		$options = get_option( 'ccd_setting' );
		if(empty($options['api_key'])) {
			return;
		}

		$siteUrl = 'https://ccd.cloud/';
		$buttonString = 'コピペチェック一括チェック(-)';

		// オプションからサイトのURLを取得する。
		if (!empty($options['api_url'])) {
			$siteUrl = $options['api_url'];
		}

		$echoString = "<input type='button' id='ccd_list_header_button' class='button ccd_list_header_button' value='{$buttonString}'>";

		echo $echoString;
	}

	/**
	* 残りを取得する。
	*/
	public function ccd_check_remain() {
		try {
			$options = get_option( 'ccd_setting' );

			$siteUrl = 'https://ccd.cloud/';
			if (!empty($options['api_url'])) {
				$siteUrl = $options['api_url'];
			}

			$postParamList = array(
				'key' => $options['api_key'],
			);

			$args = array(
				'headers' => array(array('CCD_API_HEADER' => '1714220c7f6177331cb8b7c7d47d4b94', 'user-agent' => 'ccd-plugin')),
			);

			$response = wp_remote_get($siteUrl . '/UserApiV1/getApiRemain?' . http_build_query($postParamList), $args);

			$result = json_decode($response['body'], true);
			if (!isset($result['result_data'])) {
				throw new Exception('API残りの取得に失敗');
			}

			// 結果を戻す。
			$resultInfo = array(
				'status' => 1,
				'result' => array(
					'api_remain' => $result['result_data']['api_remain'],
					'string_remain' => $result['result_data']['month_remain'],
				),
			);

		} catch (Exception $e) {

			$resultInfo = array(
				'status' => 9,
				'message' => $e->getMessage(),
			);

		}

		wp_send_json($resultInfo);
	}

	/**
	* 結果の文字列を取得します。
	*/
	private function getResultString($resultId) {
		$resultString = array(
			1 => '良好',
			2 => '要注意',
			3 => 'コピーの疑い',
		);

		$color = array(
			1 => 'ccd-result-success',
			2 => 'ccd-result-warning',
			3 => 'ccd-result-danger',
		);


		$returnInfo = array(
			'string' => $resultString[$resultId],
			'color' => $color[$resultId],
		);

		return $returnInfo;
	}

	/**
	* 一覧からの登録開始。
	*/
	public function ccd_check_execute_from_list() {

		try {

			if (empty($_POST['postId'])) {
				$status = 9;
				throw new Exception('記事IDが設定されていません。');
			}

			//対象の投稿情報を取得する。
			// ショートコードなどに対応。
			$targetPostData = get_post($_POST['postId']);
			if (empty($targetPostData)) {
				throw new Exception('記事情報が取得できませんでした。');
			}

			global $post;
			$post = $targetPostData;

			$_POST['textData'] = strtr(apply_filters('the_content', $targetPostData->post_content), array('&nbsp;' => ''));

			// 実施
			$this->ccd_check_execute();
		} catch (Exception $e) {

			$resultInfo = array(
				'status' => 9,
				'message' => $e->getMessage(),
			);
		}
	}

	/**
	* 対象の配列を全部一列にする。
	*/
	private function arraySerialize($array) {

		$returnList = array();
		foreach($array as $dataList) {
			if (is_array($dataList)) {
				$tmpDataList = $this->arraySerialize($dataList);
				$returnList = $returnList + $tmpDataList;
			} else {
				$returnList[] = $dataList;
			}
		}

		return $returnList;
	}

	/**
	* チェック実行
	*/
	public function ccd_check_execute() {

		try {
			$options = get_option( 'ccd_setting' );
			if (empty($options['api_key'])) {
				$status = 9;
				throw new Exception('APIキーが設定されていません。');
			}

			if (empty($options['del_domain'])) {
				$status = 9;
				throw new Exception('ブログのURLが設定されていません。');
			}

			if (empty($_POST['textData'])) {
				$status = 9;
				throw new Exception('文章が設定されていません。');
			}

			if (empty($_POST['postId'])) {
				$status = 9;
				throw new Exception('記事IDが設定されていません。');
			}

			// ショートコードに対応。ACFなどIDが必要なものの場合はIDを入れる。
			global $post;
			if (!empty($post->ID)) $post->ID = $_POST['postId'];
			
			// フィルター処理を実施
			$_POST['textData'] = strtr(apply_filters('the_content', $_POST['textData']), array('&nbsp;' => ''));

			$postParamList = array(
				'key' => $options['api_key'],
				'del_domain' => $options['del_domain'],
				'text' => $_POST["textData"],
			);

			// メタ情報を取得　この記事に関するqueue_id一覧を取得。除外IDのために設定。
			$ignoreIdList = get_post_meta($_POST['postId'], 'ccd-ignoreids');
			if (!empty($ignoreIdList[0])) {

				// もし、万が一無視ID一覧が存在する場合は一列にして戻す。
				$ignoreIdList[0][$options['api_key']] = $this->arraySerialize($ignoreIdList[0][$options['api_key']]);

				$postParamList['ignore_ids'] = $ignoreIdList[0][$options['api_key']];
				$ignoreIdList = $ignoreIdList[0];
			} else {
				$ignoreIdList = array(
					$options['api_key'] => array(),
				);
			}

			$siteUrl = 'https://ccd.cloud/';
			if (!empty($options['api_url'])) {
				$siteUrl = $options['api_url'];
			}

			$args = array( 'body' => $postParamList,  'headers' => array('CCD_API_HEADER' => '1714220c7f6177331cb8b7c7d47d4b94','user-agent' => 'ccd-plugin'));
			$args['timeout'] = 45;
			$response = wp_remote_post(  $siteUrl . 'UserApiV1/postText', $args);

			// 結果の調査。
			$result = json_decode($response['body'], true);
			if ($result['status'] != 1) {
				$errorString = "";
				foreach($result['error']['validate'] as $list) {
					foreach($list as $error) {
						$errorString .= $error;
					}
				}

				if (empty($result['error']['message'])) {
					$result['error']['message'] = '不明なエラーが発生しました。';
				}

				throw new Exception($result['error']['message'] . "-->" . $errorString);
			}

			if (empty($result['queue_id'])) {
				throw new Exception('APIへアクセスできません。しばらくしてから再度実行してください。');
			}

			$wpPostParamList = array(
				'key' => $options['api_key'],
				'queue_id' => $result['queue_id'],
			);
			$wpResult = $this->getWpApiKey($siteUrl, $wpPostParamList);

			// 結果のセット
			$resultInfo = array(
				'status' => 1,
				'queue_id' => $result['queue_id'],
			);

			// 入っている情報を取得する。
			// 何もなければ登録する。
			$metaInfo = get_post_meta($_POST['postId'], 'ccd-result');
			if (empty($metaInfo[0])) {
				$metaInfo = array(0 => array());
			}
			$metaInfo = $metaInfo[0];

			// meta情報に登録しておくやつ
			$metaInfo[$options['api_key']] = array(
				'queue_id' => $result['queue_id'],
				'wp_key' => $wpResult['ApiWpKey']['wp_key'],
				'result' => array(),
			);

			// 保存する。
			if ( !add_post_meta( $_POST['postId'], 'ccd-result', $metaInfo, true ) ) {
				update_post_meta ( $_POST['postId'], 'ccd-result', $metaInfo );
			}

			// 無視ID一覧を保存する。
			$ignoreIdList[$options['api_key']][] = $result['queue_id'];
			if ( !add_post_meta( $_POST['postId'], 'ccd-ignoreids', $ignoreIdList, true ) ) {
				update_post_meta ( $_POST['postId'], 'ccd-ignoreids', $ignoreIdList );
			}

		} catch (Exception $e) {

			$resultInfo = array(
				'status' => 9,
				'post_id' => $_POST['postId'],
				'message' => $e->getMessage(),
			);

		}

		wp_send_json($resultInfo);
	}

	/**
	* エラー時の再実行
	*/
	public function ccd_check_restart() {

		try {
			$options = get_option( 'ccd_setting' );
			if (empty($options['api_key'])) {
				$status = 9;
				throw new Exception('APIキーが設定されていません。');
			}

			if (empty($options['del_domain'])) {
				$status = 9;
				throw new Exception('ブログのURLが設定されていません。');
			}

			if (empty($_POST['postId'])) {
				$status = 9;
				throw new Exception('記事IDが設定されていません。');
			}


			//　現状の結果情報を取得する。
			$resultInfo = get_post_meta($_POST['postId'], 'ccd-result');

			if (empty($resultInfo[0][$options['api_key']]) || $resultInfo[0][$options['api_key']]['result'] !== 'error_result') {
				throw new Exception('再実行可能なコピペチェック結果情報が見つかりません。再度読み込みしてから再実行してください。');
			}

			$postParamList = array(
				'key' => $options['api_key'],
				'queue_id' => $resultInfo[0][$options['api_key']]['queue_id'],
			);

			// メタ情報を取得　この記事に関するqueue_id一覧を取得。除外IDのために設定。
			$siteUrl = 'https://ccd.cloud/';
			if (!empty($options['api_url'])) {
				$siteUrl = $options['api_url'];
			}

			$args = array(
				'headers' => array(array('CCD_API_HEADER' => '1714220c7f6177331cb8b7c7d47d4b94', 'user-agent' => 'ccd-plugin')),
			);
			$response = wp_remote_get($siteUrl . '/UserApiV1/setRestart?' . http_build_query($postParamList), $args);
			$result = json_decode($response['body'], true);

			// 対象のWPキーを取得する。
			$wpPostParamList = array(
				'key' => $options['api_key'],
				'queue_id' => $result['queue_id'],
			);
			$wpResult = $this->getWpApiKey($siteUrl, $wpPostParamList);

			// 結果の調査。
			if ($result['status'] != 1) {

				// 再実行したけど、エラーじゃないよ。
				if ($result['status'] == 302) {
					// meta情報に登録しておくやつ
					// 入っている情報を取得する。
					// 何もなければ登録する。
					$metaInfo = get_post_meta($_POST['postId'], 'ccd-result');
					if (empty($metaInfo[0])) {
						$metaInfo = array(0 => array());
					}
					$metaInfo = $metaInfo[0];

					// meta情報に登録しておくやつ
					$metaInfo[$options['api_key']] = array(
						'queue_id' => $resultInfo[0][$options['api_key']]['queue_id'],
						'wp_key' => $wpResult['ApiWpKey']['wp_key'],
						'result' => array(),
					);

					// 保存する。
					if ( !add_post_meta( $_POST['postId'], 'ccd-result', $metaInfo, true ) ) {
						update_post_meta ( $_POST['postId'], 'ccd-result', $metaInfo );
					}
				}

				throw new Exception($result['error']['message']);
			}

			if (empty($result['queue_id'])) {
				throw new Exception('APIへアクセス障害が発生しています。しばらくしてから再度実行してください。');
			}

			$resultInfo = array(
				'status' => 1,
				'queue_id' => $result['queue_id'],
			);

			// meta情報に登録しておくやつ
			$metaInfo = array(
				$options['api_key'] => array(
					'queue_id' => $result['queue_id'],
					'wp_key' => $wpResult['ApiWpKey']['wp_key'],
					'result' => array(),
				),
			);

			// 保存する。
			if ( !add_post_meta( $_POST['postId'], 'ccd-result', $metaInfo, true ) ) {
				update_post_meta ( $_POST['postId'], 'ccd-result', $metaInfo );
			}

		} catch (Exception $e) {

			$resultInfo = array(
				'status' => 9,
				'message' => $e->getMessage(),
			);

		}

		wp_send_json($resultInfo);
	}

	/**
	* WP接続用キーを生成
	*/
	private function getWpApiKey($siteUrl, $wpPostParamList) {

		$args = array( 'body' => $wpPostParamList,  'headers' => array('CCD_API_HEADER' => '1714220c7f6177331cb8b7c7d47d4b94','user-agent' => 'ccd-plugin'));
		$response = wp_remote_post(  $siteUrl . 'UserApiV1/getWpKey', $args);

		// 結果の調査。
		$wpResult = json_decode($response['body'], true);
		if ($wpResult['status'] != 1) {
			throw new Exception('error->' . $wpResult['error']['message']);
		}

		if (empty($wpResult['result_data']['ApiWpKey']['wp_key'])) {
			throw new Exception('WP APIへアクセスできません。しばらくしてから再度実行してください。');
		}

		return $wpResult['result_data'];
	}

	/**
	* 一覧
	*/
	public function ccd_check_result_from_list() {
		$resultInfo = $this->getResultFromCcd();
		wp_send_json($resultInfo);
	}

	/**
	* チェック取得
	*/
	public function ccd_check_result() {
		$resultInfo = $this->getResultFromCcd();
		wp_send_json($resultInfo);
	}

	private function getResultFromCcd() {
		try {
			$options = get_option( 'ccd_setting' );
			if (empty($options['api_key'])) {
				$status = 9;
				throw new Exception('APIキーが設定されていません。');
			}

			if (empty($options['del_domain'])) {
				$status = 9;
				throw new Exception('ブログのURLが設定されていません。');
			}

			if (empty($_POST['postId'])) {
				$status = 9;
				throw new Exception('記事IDが設定されていません。');
			}

			if (empty($_POST['queueId'])) {
				$status = 9;
				throw new Exception('結果取得IDが設定されていません。再度実行してください');
			}

			// meta上にある情報を取得する。
			$resultMetaInfo = get_post_meta($_POST['postId'], 'ccd-result');
			if (empty($resultMetaInfo[0][$options['api_key']]['wp_key'])) {
				throw new Exception('実行に必要な情報が設定されていません。再度実行してください ID-->' . $_POST['postId']);
			}

			$postParamList = array(
				'key' => $options['api_key'],
				'queue_id' => $_POST["queueId"],
			);

			$siteUrl = 'https://ccd.cloud/';
			if (!empty($options['api_url'])) {
				$siteUrl = $options['api_url'];
			}

			$args = array( 'body' => $postParamList,  'headers' => array('CCD_API_HEADER' => '1714220c7f6177331cb8b7c7d47d4b94','user-agent' => 'ccd-plugin', 'CCD_API_HEADER_WP_KEY' => $resultMetaInfo[0][$options['api_key']]['wp_key']));
			$response = wp_remote_post(  $siteUrl . 'UserApiV1/getResult', $args);

			// 結果の調査。
			$result = json_decode($response['body'], true);


			if ($result['status'] != 1 && $result['status'] != 202 && $result['status'] != 203) {
				throw new Exception($result['error']['message']);
			}

			if ($result['status'] == 202) {
				// もう一回取得する
				// 結果を戻す。
				$resultInfo = array(
					'status' => 2,
					'queue_id' => $_POST["queueId"],
					'post_id' => $_POST['postId'],
				);
			} else if ($result['status'] == 203) {

				// エラー発生。再実行対象
				$resultInfo = array(
					'status' => 3,
					'queue_id' => $_POST["queueId"],
					'post_id' => $_POST['postId'],
				);

				// 一旦記事を抜けた時の処理のためにデータを保持する。
				$metaInfo = array(
					$options['api_key'] => array(
						'queue_id' => $_POST["queueId"],
						'result' => 'error_result',
						'check_date' => $checkDate,
					),
				);

				// 対象の投稿に対してデータを保存しておく。
				if ( !add_post_meta( $_POST['postId'], 'ccd-result', $metaInfo, true ) ) {
					update_post_meta ( $_POST['postId'], 'ccd-result', $metaInfo );
				}

			} else {

				$ruijiInfo = $this->getResultString($result['result_data']['web_like_info']['like_status']);
				$ruijiInfo['percent'] = $result['result_data']['web_like_info']['like_percent'];

				$kikaiInfo = $this->getResultString($result['result_data']['web_match_info']['match_status']);
				$kikaiInfo['percent'] = $result['result_data']['web_match_info']['match_percent'];

				$textInfo = $this->getResultString($result['result_data']['text_match_info']['text_match_status']);
				$textInfo['percent'] = $result['result_data']['text_match_info']['text_match_percent'];

				// チェックした日
				$checkDate = date('Y年m月d日 H:i:s');

				// オプションからサイトのURLを取得する。
				$siteUrl = 'https://ccd.cloud/';
				if (!empty($options['api_url'])) {
					$siteUrl = $options['api_url'];
				}

				// 結果を戻す。
				$resultInfo = array(
					'status' => 1,
					'result' => array(
						'ruiji' => $ruijiInfo,
						'kikai' => $kikaiInfo,
						'text' => $textInfo,
					),
					'check_date' => $checkDate,
					'queue_id' => $_POST["queueId"],
					'post_id' => $_POST['postId'],
					'site_url' => $siteUrl,
				);

				$metaInfo = array(
					$options['api_key'] => array(
						'queue_id' => $_POST["queueId"],
						'result' => $result['result_data'],
						'check_date' => $checkDate,
					),
				);

				// 対象の投稿に対してデータを保存しておく。
				if ( !add_post_meta( $_POST['postId'], 'ccd-result', $metaInfo, true ) ) {
					update_post_meta ( $_POST['postId'], 'ccd-result', $metaInfo );
				}
			}

		} catch (Exception $e) {

			$resultInfo = array(
				'status' => 9,
				'message' => $e->getMessage(),
				'queue_id' => $_POST["queueId"],
				'post_id' => $_POST['postId'],
			);

		}

		return $resultInfo;
	}

	/**
	* 一覧表示のフック
	*/
	public function ccd_list_result_column( $columns ) {
		$columns['ccd_result'] = 'コピペチェック結果';
		return $columns;
	}

	/**
	* 一覧表示用コピペチェック結果表示
	*/
	public function ccd_list_result_column_2args( $columnName, $postId ) {
		// 対象のカラムのときにデータの表示
		if ( 'ccd_result' == $columnName ) {

			// APIキーが存在しなければ、何も表示市内
			$options = get_option( 'ccd_setting' );
			if(empty($options['api_key'])) {
				echo "<a href='https://ccd.cloud/' target='_blank'>WEB版のCopyContentDetectorを開く</a><br>APIキーが未設定のため直接コピペチェックは無効です。";
				return;
			}

			$siteUrl = 'https://ccd.cloud/';

			// オプションからサイトのURLを取得する。
			if (!empty($options['api_url'])) {
				$siteUrl = $options['api_url'];
			}

			$resultInfo = get_post_meta($postId, 'ccd-result');
			$buttonString = '<div><input type="button" id="ccd_execute_button_' . $postId . '" value="コピペチェック実行" class="button js-ccd_execute_check_button-list" data-ccd_post_id="' . $postId . '"/></div>';
			$resultString = "{$buttonString}<div id='ccd_execute_result_{$postId}'>コピペチェック未実行</div>";

			// 結果の一覧を設定する。
			if (!empty($resultInfo[0][$options['api_key']])) {
				$queueId = $resultInfo[0][$options['api_key']]['queue_id'];
				$resultList = $resultInfo[0][$options['api_key']]['result'];

				// もしこの時点で結果がない場合は、途中で離脱したことが考えられるので再度取得する。
				if (!empty($resultInfo[0][$options['api_key']]['result'])) {

					$resultString = $buttonString . "<div id='ccd_execute_result_{$postId}'>{$resultInfo[0][$options['api_key']]['check_date']} に実施<br> ";

					$ruijiInfo = $this->getResultString($resultList['web_like_info']['like_status']);
					$resultString .= "<span class='ccd-result-item'>";
					$resultString .= "<b>";
					$resultString .= "<div class='ccd-list-width'>";
					$resultString .= "■類似度: ";
					$resultString .= "<span class='{$ruijiInfo['color']}'>";
					$resultString .= "{$resultList['web_like_info']['like_percent']}%";
					$resultString .= "</span>";
					$resultString .= "</div>";
					$resultString .= "<div class='{$ruijiInfo['color']}'>";
					$resultString .= "【{$ruijiInfo['string']}】";
					$resultString .= "</div>";
					$resultString .= "</b>";
					$resultString .= "</span>";

					$kikaiInfo = $this->getResultString($resultList['web_match_info']['match_status']);
					$resultString .= "<span class='ccd-result-item'>";
					$resultString .= "<b>";
					$resultString .= "<div class='ccd-list-width'>";
					$resultString .= "■一致率: ";
					$resultString .= "<span class='{$kikaiInfo['color']}'>";
					$resultString .= "{$resultList['web_match_info']['match_percent']}%";
					$resultString .= "</span>";
					$resultString .= "</div>";
					$resultString .= "<div class='{$kikaiInfo['color']}'>";
					$resultString .= "【{$kikaiInfo['string']}】";
					$resultString .= "</div>";
					$resultString .= "</b>";
					$resultString .= "</span>";

					$textInfo = $this->getResultString($resultList['text_match_info']['text_match_status']);
					$resultString .= "<span class='ccd-result-item'>";
					$resultString .= "<b>";
					$resultString .= "<div class='ccd-list-width'>";
					$resultString .= "■ﾃｷｽﾄ間: ";
					$resultString .= "<span class='{$textInfo['color']}'>";
					$resultString .= "{$resultList['text_match_info']['text_match_percent']}%";
					$resultString .= "</span>";
					$resultString .= "</div>";
					$resultString .= "<div class='{$textInfo['color']}'>";
					$resultString .= "【{$textInfo['string']}】";
					$resultString .= "</div>";
					$resultString .= "</b>";
					$resultString .= "</span>";

					$resultString .= "<a href='{$siteUrl}Result/detail?id={$queueId}' target='_blank' class='ccd-link'>【結果を見る】</a>";

					$resultString .= "</div>"; // ccd_execute_result_のとじ
				} else {
					$resultString = $buttonString;
					$resultString .= "<div id='ccd_execute_result_{$postId}'><div id='ccd_execute_result_{$postId}' data-ccd_queue_id='{$queueId}' data-ccd_post_id='{$postId}' class='ccd_result_get'>コピペチェック実行中</div></div>";
				}
			}

			echo $resultString;
		}
	}

	function ccd_add_query_vars_filter( $vars ){
		$vars[] = "ccd_like_status";
		$vars[] = "ccd_match_status";
		$vars[] = "ccd_text_match_status";
		return $vars;
	}

	// POSTSのWHEREに追加する。
	public function ccd_postwhere_query($where) {

		global $wpdb;
		if ( $searchParam = get_query_var( 'ccd_like_status', '') ) {
			$searchParam = '"like_status";s:1:"' . $searchParam . '"';
			$where .= ' AND ' . 'wp_postmeta_ccd.meta_value LIKE \'%' . esc_sql( $wpdb->esc_like( $searchParam ) ) . '%\'';
		}

		if ( $searchParam = get_query_var( 'ccd_match_status', '') ) {
			$searchParam = '"match_status";s:1:"' . $searchParam . '"';
			$where .= ' AND ' . 'wp_postmeta_ccd.meta_value LIKE \'%' . esc_sql( $wpdb->esc_like( $searchParam ) ) . '%\'';
		}

		if ( $searchParam = get_query_var( 'ccd_text_match_status', '') ) {
			$searchParam = '"text_match_status";s:1:"' . $searchParam . '"';
			$where .= ' AND ' . 'wp_postmeta_ccd.meta_value LIKE \'%' . esc_sql( $wpdb->esc_like( $searchParam ) ) . '%\'';
		}

		return $where;
	}

	// POSTSのJOINを追加
	public function ccd_postwhere_join($join) {

		$likeStatus = get_query_var( 'ccd_like_status', '');
		$matchStatus = get_query_var( 'ccd_match_status', '');
		$textStatus = get_query_var( 'ccd_text_match_status', '');

		if (!empty($likeStatus) || !empty($matchStatus) || !empty($textStatus)) {
			global $wpdb;
			$join .= "LEFT JOIN {$wpdb->prefix}postmeta as wp_postmeta_ccd ON ( {$wpdb->prefix}posts.ID = wp_postmeta_ccd.post_id and wp_postmeta_ccd.meta_key = 'ccd-result' )";
		}

		return $join;
	}

	/**
	* 一覧画面上　ヘッダ部分に登録します。
	*/
	public function ccd_add_search_list() {

		// APIキーが存在しなければ、何も表示市内
		$options = get_option( 'ccd_setting' );
		if(empty($options['api_key'])) {
			return;
		}

		$echoString = "";
		if (!empty($options['search_like_dropdown'])) {
			$echoString .= $this->ccd_generateSearchDropdownList('ccd_like_status', '類似度');
		}

		if (!empty($options['search_match_dropdown'])) {
			$echoString .= $this->ccd_generateSearchDropdownList('ccd_match_status', '一致率');
		}

		if (!empty($options['search_text_dropdown'])) {
			$echoString .= $this->ccd_generateSearchDropdownList('ccd_text_match_status', 'テキスト間');
		}

		echo $echoString;
	}

	private function ccd_generateSearchDropdownList($key, $label) {
		$likeStatus = get_query_var( $key, '');
		$selectedLikeList = array(
			1 => '',
			2 => '',
			3 => '',
		);

		if (isset($selectedLikeList[$likeStatus])) {
			$selectedLikeList[$likeStatus] = 'selected';
		}


		$echoString = "";
		$echoString .= '<select name="' . $key . '" id="' . $key . '" class="postform">';
		$echoString .= '<option value="">【' . $label . '】絞り込みなし</option>';
		$echoString .= '<option class="level-0" value="1" ' . $selectedLikeList[1] . '>【' . $label . '】良好</option>';
		$echoString .= '<option class="level-0" value="2" ' . $selectedLikeList[2] . '>【' . $label . '】要注意</option>';
		$echoString .= '<option class="level-0" value="3" ' . $selectedLikeList[3] . '>【' . $label . '】コピーの疑い</option>';
		$echoString .= '</select>';

		return $echoString;
	}
}
