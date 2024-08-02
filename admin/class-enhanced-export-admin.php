<?php

/**
 * The admin-specific functionality of the plugin.
 *
 * Defines the hooks used by the plugin
 *
 * @package    Enhanced_Export
 * @subpackage Enhanced_Export/admin
 * @author     Alex Denche <daext3r@gmail.com>
 */
class Enhanced_Export_Admin {

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
		 * defined in Enhanced_Export_Loader as all of the hooks are defined
		 * in that particular class.
		 *
		 * The Enhanced_Export_Loader will then create the relationship
		 * between the defined hooks and the functions defined in this
		 * class.
		 */

		wp_enqueue_style( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'css/enhanced-export-admin.css', array(), time(), 'all' );

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
		 * defined in Enhanced_Export_Loader as all of the hooks are defined
		 * in that particular class.
		 *
		 * The Enhanced_Export_Loader will then create the relationship
		 * between the defined hooks and the functions defined in this
		 * class.
		 */

		wp_enqueue_script( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'js/enhanced-export-admin.js', array( 'jquery' ), time(), false );

	}


	public function admin_menu() {
		add_menu_page( 'Enhanced export', 'Enhanced export', 'administrator', 'enhanced-export', [$this, 'create_export'], 'dashicons-download', 100 );

		add_submenu_page( 'enhanced-export', 'Exportaciones', 'Exportaciones', 'administrator', 'enhanced-export-exports', [$this, 'exports']);
		add_submenu_page( 'enhanced-export', 'Plantillas', 'Plantillas', 'administrator', 'enhanced-export-templates', [$this, 'templates']);
		add_submenu_page( 'enhanced-export', 'Campos personalizados', 'Campos personalizados', 'administrator', 'enhanced-export-custom-fields', [$this, 'custom_fields']);
		
		add_submenu_page( 'enhanced-export', '', '', 'administrator', 'enhanced-export-run-export', [$this, 'run_export']);

		//add_submenu_page( 'enhanced-export', '', '', 'administrator', 'enhanced-export-dummy', [$this, 'create_export']);
		
	}

	public function rest_api_init() {
		// register_rest_route( 'enhanced-export/v1', '/export-status/(?P<id>\d+)', array(
		// 	'methods' => 'GET',
		// 	'callback' => [$this, 'get_status'],
		// ));

		// register_rest_route( 'enhanced-export/v1', '/resume-export/(?P<id>\d+)', array(
		// 	'methods' => 'GET',
		// 	'callback' => [$this, 'resume_export'],
		// ));

		register_rest_route( 'enhanced-export/v1', '/run-export/(?P<id>\d+)', array(
			'methods' => 'GET',
			'callback' => [$this, 'api__run_export'],
		));


		
	}

	public function create_export() {
		require_once plugin_dir_path(__FILE__) . '/views/create-export.php';
	}

	public function templates() {
		require_once plugin_dir_path(__FILE__) . '/views/list-templates.php';
	}
	public function exports() {
		require_once plugin_dir_path(__FILE__) . '/views/list-exports.php';
	}

	public function custom_fields() {
		require_once plugin_dir_path(__FILE__) . '/views/custom-fields.php';
	}

	public function run_export() {
		require_once plugin_dir_path(__FILE__) . '/views/run-export.php';
	}

	// public function get_status($request) {
	// 	$id = $request->get_param('id');
  
	// 	$delta = new Enhanced_Export_Delta_Exports();
	
	// 	$data =  $delta->get_export($id);
	
	// 	$return = [
	// 		'status' => $data->status,
	// 		'records' => $data->records,
	// 		'processed' => $data->processed,
	// 		'file_name' => WP_CONTENT_URL . '/enhanced-export/exports/' . $data->file_name
	// 	];

	// 	return $return;
	// }

	// public function resume_export($request) {
	// 	$id = $request->get_param('id');

	// 	$delta = new Enhanced_Export_Delta_Exports();
	
	// 	$data =  $delta->get_export($id);

	// 	if($data && !wp_next_scheduled('process_batch_export')) {
	// 		do_action('process_batch_export', $id, 20, 0);
	// 	}
	// }

	public function api__run_export($request) {
		$id = $request->get_param('id');

		$delta = new Enhanced_Export_Delta_Exports();
		
		do_action('process_batch_export', $id);
		
		$export = $delta->get_export($id);

		return [
			'status' => $export->status,
			'records' => $export->records,
			'processed' => $export->processed
		];
	}
}
