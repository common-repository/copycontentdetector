<?php

/**
 * The file that defines the core plugin class
 *
 * A class definition that includes attributes and functions used across both the
 * public-facing side of the site and the admin area.
 *
 * @link       http://new-system-create.co.jp
 * @since      1.0.0
 *
 * @package    Ccd_Copycontentdetector
 * @subpackage Ccd_Copycontentdetector/includes
 */

/**
 * The core plugin class.
 *
 * This is used to define internationalization, admin-specific hooks, and
 * public-facing site hooks.
 *
 * Also maintains the unique identifier of this plugin as well as the current
 * version of the plugin.
 *
 * @since      1.0.0
 * @package    Ccd_Copycontentdetector
 * @subpackage Ccd_Copycontentdetector/includes
 * @author     Sumito Umeda <umeda@new-system-create.co.jp>
 */
class Ccd_Copycontentdetector {

	/**
	 * The loader that's responsible for maintaining and registering all hooks that power
	 * the plugin.
	 *
	 * @since    1.0.0
	 * @access   protected
	 * @var      Ccd_Copycontentdetector_Loader    $loader    Maintains and registers all hooks for the plugin.
	 */
	protected $loader;

	/**
	 * The unique identifier of this plugin.
	 *
	 * @since    1.0.0
	 * @access   protected
	 * @var      string    $plugin_name    The string used to uniquely identify this plugin.
	 */
	protected $plugin_name;

	/**
	 * The current version of the plugin.
	 *
	 * @since    1.0.0
	 * @access   protected
	 * @var      string    $version    The current version of the plugin.
	 */
	protected $version;

	/**
	 * Define the core functionality of the plugin.
	 *
	 * Set the plugin name and the plugin version that can be used throughout the plugin.
	 * Load the dependencies, define the locale, and set the hooks for the admin area and
	 * the public-facing side of the site.
	 *
	 * @since    1.0.0
	 */
	public function __construct() {
		if ( defined( 'PLUGIN_NAME_VERSION' ) ) {
			$this->version = PLUGIN_NAME_VERSION;
		} else {
			$this->version = '1.0.0';
		}
		$this->plugin_name = 'ccd-copycontentdetector';

		$this->load_dependencies();
		$this->set_locale();
		$this->define_admin_hooks();
		$this->define_public_hooks();

	}

	/**
	 * Load the required dependencies for this plugin.
	 *
	 * Include the following files that make up the plugin:
	 *
	 * - Ccd_Copycontentdetector_Loader. Orchestrates the hooks of the plugin.
	 * - Ccd_Copycontentdetector_i18n. Defines internationalization functionality.
	 * - Ccd_Copycontentdetector_Admin. Defines all hooks for the admin area.
	 * - Ccd_Copycontentdetector_Public. Defines all hooks for the public side of the site.
	 *
	 * Create an instance of the loader which will be used to register the hooks
	 * with WordPress.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function load_dependencies() {

		/**
		 * The class responsible for orchestrating the actions and filters of the
		 * core plugin.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-ccd-copycontentdetector-loader.php';

		/**
		 * The class responsible for defining internationalization functionality
		 * of the plugin.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-ccd-copycontentdetector-i18n.php';

		/**
		 * The class responsible for defining all actions that occur in the admin area.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'admin/class-ccd-copycontentdetector-admin.php';

		/**
		 * The class responsible for defining all actions that occur in the public-facing
		 * side of the site.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'public/class-ccd-copycontentdetector-public.php';

		$this->loader = new Ccd_Copycontentdetector_Loader();

	}

	/**
	 * Define the locale for this plugin for internationalization.
	 *
	 * Uses the Ccd_Copycontentdetector_i18n class in order to set the domain and to register the hook
	 * with WordPress.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function set_locale() {

		$plugin_i18n = new Ccd_Copycontentdetector_i18n();

		$this->loader->add_action( 'plugins_loaded', $plugin_i18n, 'load_plugin_textdomain' );

	}

	/**
	 * Register all of the hooks related to the admin area functionality
	 * of the plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function define_admin_hooks() {

		$plugin_admin = new Ccd_Copycontentdetector_Admin( $this->get_plugin_name(), $this->get_version() );

		$this->loader->add_action( 'admin_enqueue_scripts', $plugin_admin, 'enqueue_styles' );
		$this->loader->add_action( 'admin_enqueue_scripts', $plugin_admin, 'enqueue_scripts' );

		// 追加 add_actionのこのパラメータには注意。
		$this->loader->add_action( 'admin_init', $plugin_admin, 'ccd_setting_init'); // メニュー用のバリデーションとフォームの設定
		$this->loader->add_action( 'admin_menu', $plugin_admin, 'ccd_add_menu' ); // メニュー表示
		$this->loader->add_action( 'edit_form_after_editor', $plugin_admin, 'ccd_add_execute_button_for_edit' ); // ボタン追加
		$this->loader->add_action( 'restrict_manage_posts', $plugin_admin, 'ccd_add_execute_button_for_list' ); // 一覧の上にくっつける

		$this->loader->add_action( 'wp_ajax_ccd_check_execute', $plugin_admin, 'ccd_check_execute'); // チェック事項AJAXの定義
		$this->loader->add_action( 'wp_ajax_ccd_check_execute_from_list', $plugin_admin, 'ccd_check_execute_from_list'); // チェック事項AJAXの定義
		$this->loader->add_action( 'wp_ajax_ccd_check_remain', $plugin_admin, 'ccd_check_remain'); // 回数取得のAJAX定義
		$this->loader->add_action( 'wp_ajax_ccd_check_result', $plugin_admin, 'ccd_check_result'); // 結果のAJAX定義
		$this->loader->add_action( 'wp_ajax_ccd_check_result_from_list', $plugin_admin, 'ccd_check_result_from_list'); // 結果のAJAX定義
		$this->loader->add_action( 'wp_ajax_ccd_check_restart', $plugin_admin, 'ccd_check_restart'); // 再実行のAJAX定義

		$this->loader->add_filter( 'manage_posts_columns', $plugin_admin, 'ccd_list_result_column' ); // 記事投稿一覧にフック
		$this->loader->add_action( 'manage_posts_custom_column', $plugin_admin, 'ccd_list_result_column_2args', 10, 2 );
		$this->loader->add_filter( 'manage_pages_columns', $plugin_admin, 'ccd_list_result_column' ); // 固定記事一覧にフック
		$this->loader->add_action( 'manage_pages_custom_column', $plugin_admin, 'ccd_list_result_column_2args', 10, 2 );

		$this->loader->add_action( 'restrict_manage_posts', $plugin_admin, 'ccd_add_search_list' ); // 一覧の上にくっつける
		$this->loader->add_filter( 'query_vars', $plugin_admin, 'ccd_add_query_vars_filter' ); // 検索用のフィルター追加
		$this->loader->add_filter( 'posts_where', $plugin_admin, 'ccd_postwhere_query'); // 検索結果一覧の取得
		$this->loader->add_filter( 'posts_join', $plugin_admin, 'ccd_postwhere_join'); // JOINフック

	}

	/**
	 * Register all of the hooks related to the public-facing functionality
	 * of the plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function define_public_hooks() {

		$plugin_public = new Ccd_Copycontentdetector_Public( $this->get_plugin_name(), $this->get_version() );

		$this->loader->add_action( 'wp_enqueue_scripts', $plugin_public, 'enqueue_styles' );
		$this->loader->add_action( 'wp_enqueue_scripts', $plugin_public, 'enqueue_scripts' );

	}

	/**
	 * Run the loader to execute all of the hooks with WordPress.
	 *
	 * @since    1.0.0
	 */
	public function run() {
		$this->loader->run();
	}

	/**
	 * The name of the plugin used to uniquely identify it within the context of
	 * WordPress and to define internationalization functionality.
	 *
	 * @since     1.0.0
	 * @return    string    The name of the plugin.
	 */
	public function get_plugin_name() {
		return $this->plugin_name;
	}

	/**
	 * The reference to the class that orchestrates the hooks with the plugin.
	 *
	 * @since     1.0.0
	 * @return    Ccd_Copycontentdetector_Loader    Orchestrates the hooks of the plugin.
	 */
	public function get_loader() {
		return $this->loader;
	}

	/**
	 * Retrieve the version number of the plugin.
	 *
	 * @since     1.0.0
	 * @return    string    The version number of the plugin.
	 */
	public function get_version() {
		return $this->version;
	}

}
