<?php
/*
 * Plugin Name: WP Hooks Browser
 * Plugin URL: 
 * Description: A very simple plugin to document all the used and or defined hooks inside any of the installed theme and or plugins
 * Version: 1.1
 * Author: Prince Singh
 * Author URI: https://profiles.wordpress.org/curi0us_s0ul
 */
  
// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;
 
if ( ! class_exists( 'WP_HOOKS_BROWSER' ) ) :
	/*
	 * WP_HOOKS_BROWSER
	 *
	 * @since 1.0.0
	 */
	final class WP_HOOKS_BROWSER {
		
		/*
		 * contains instance of the class
		 *
		 * @since 1.0.0
		 *
		 */
		private static $instance;
		
		
		public $prefix 		= 'hb_';
		
		public $text_domain	= 'HB';
	
		private $version = '1.1.0';

		/*
		 * patterns to match different action hooks
		 *
		 * @since 1.0.0
		 *
		 */
		private $patterns;
		
		public $colors;

		/*
		 * list of hook found as per query
		 *
		 * @since 1.0.0
		 *
		 */
		private $hooks;
		
		/*
		 * ensures only one instance of this class is running
		 *
		 * @since 1.0.0
		 *
		 * @return WP_HOOKS_BROWSER instance
		 */
		public static function instance() {
			if ( ! isset( self::$instance ) && ! ( self::$instance instanceof WP_HOOKS_BROWSER ) ) {
				self::$instance = new WP_HOOKS_BROWSER;
				self::$instance->setup_constants();
				self::$instance->includes();
				self::$instance->setup();
				self::$instance->hooks();

			}
			return self::$instance;
		}
		
		/**
		 * Setup the patterns etc
		 *
		 * @since 1.0.0
		 *
		 * @return void
		 */
		private function setup() {
		
			$this->patterns = apply_filters($this->prefix.'matching_patterns', 
				array(
					'do_action'		=>	'@do_action[ ]*\([\'\"]*([\w-$>\.\'\" ]*)?\',@',
					'apply_filters'	=>	'@apply_filters[ ]*\([\'\"]*([\w-$>\.\'\" ]*)?\',@',
					'add_filter'	=>	'@add_filter[ ]*\([\'\"]*([\w-$>\.\'\" ]*)?\',@',
					'add_action'	=>	'@add_action[ ]*\([\'\"]*([\w-$>\.\'\" ]*)?\',@',
					'string'		=>	'@*****@'
				)
			);
			
			/** colors not being used in this version **/
			$this->colors = apply_filters($this->prefix.'colors', 
				array(
					'do_action'		=>	'#43ac6d',
					'apply_filters'	=>	'#459bc4',
					'add_filter'	=>	'#f9845b',
					'add_action'	=>	'#7d669e',
					'string'		=>	'#ed5a5a'
				)
			);
		}

		/**
		 * Setup the default hooks and actions
		 *
		 * @since 1.0.0
		 *
		 * @return void
		 */
		private function hooks() {


			// activation
			add_action( 'admin_init', array( $this, 'display_hooks' ) );

			// hooks browser menu
			add_action('admin_menu', array($this,'hooks_browser_menu'),99);

			// admin styles and scripts
			add_action('admin_enqueue_scripts', array($this,'admin_enqueue_scripts'),99);
		}
		
		/**
		 * Registers hooks browser submenu
		 * @since 1.0
		 * @access public
		 *
		 * @return void
		 */
		public function hooks_browser_menu() {
			 add_menu_page(  
			  	__( 'Hooks Browser', $this->text_domain ),
			  	__( 'Hooks Browser', $this->text_domain ), 
			  	'manage_options',
			  	'hb-hooks-browser',
			  	array($this,'hooks_page_callback')
		  	); 
		}

		/**
		 * Activation function fires when the plugin is activated.
		 * @since 1.0
		 * @access public
		 *
		 * @return void
		 */
		public function display_hooks() {
			if( isset($_POST['option_page']) && $_POST['option_page'] == 'hooks-browser-settings'  ) {
				$this->scope 			= isset($_POST['hb_where_to_look']) ? sanitize_text_field($_POST['hb_where_to_look']) : 'both';
				$this->theme 			= isset($_POST['hb_which_theme']) ? sanitize_text_field($_POST['hb_which_theme']) : '';
				$this->plugin  			= isset($_POST['hb_which_plugin']) ? sanitize_text_field($_POST['hb_which_plugin']) : '';
				$this->hook_type		= isset($_POST['hb_actions_or_filters']) ? sanitize_text_field($_POST['hb_actions_or_filters']) : '';
				$this->search_string	= isset($_POST['hb_string_val']) ? $_POST['hb_string_val'] : '';

				switch($this->scope) {
					
					case 'plugins':
						if($this->plugin != '') {
							$dir_path = plugin_dir_path(__DIR__).$this->plugin;
							$dirInfo=$this->parse_directory($dir_path);
							$this->scan_file_contents($dirInfo);
						}
					break;
					case 'themes':
						if($this->theme != '') {
							$dir_path = trailingslashit(get_theme_root()).$this->theme;
							$dirInfo=$this->parse_directory($dir_path);
							$this->scan_file_contents($dirInfo);
						}
					break;
					case 'both':
						$dirInfo=$this->parse_directory(WP_CONTENT_DIR);
						$this->scan_file_contents($dirInfo);
					break;
				}		
			}
		}
		
		/*
		 * Setup plugin constants
		 *
		 * @access public
		 * @since 1.0
		 * @return void
		 */
		public function setup_constants() {		
			
			// Plugin File
			if ( ! defined( 'HB_PLUGIN_FILE' ) ) {
				define( 'HB_PLUGIN_FILE', __FILE__ );
			}
			
			// Plugin Folder URL
			if ( ! defined( 'HB_PLUGIN_URL' ) ) {
				define( 'HB_PLUGIN_URL', plugin_dir_url( __FILE__ ) );
			}
			
			// Plugin Folder Path
			if ( ! defined( 'HB_PLUGIN_PATH' ) ) {
				define( 'HB_PLUGIN_PATH', plugin_dir_path( __FILE__ ) );
			}
			
			// Plugin dir path
			if ( ! defined( 'HB_PLUGIN_DIR' ) ) {
				define( 'HB_PLUGIN_DIR', plugin_dir_path( __FILE__ ) );
			}

		}

		/*
		 * Include required files
		 *
		 * @access private
		 * @since 1.0
		 * @return void
		 */
		 
		private function includes() {
		
			// Common files required everywhere
			$this->common_includes();
		
		    // Admin Specific files
			if ( is_admin() ) {
				$this->admin_includes();
			}
			
			// Ajax Specific files
			if ( defined( 'DOING_AJAX' ) ) {
				$this->ajax_includes();
			}

			// frontend specific files
			if ( ! is_admin() || defined( 'DOING_AJAX' ) ) {
				$this->frontend_includes();
			}
			
		}

		/*
		 * Include common required files
		 *
		 * @access public
		 * @since 1.0
		 * @return void
		 */
		function common_includes() {
			do_action('hb_common_includes');
		}
		
		/*
		 * Include admin specific required files
		 *
		 * @access public
		 * @since 1.0
		 * @return void
		 */
		function admin_includes() {
			do_action('hb_admin_includes');
		}
		
		/*
		 * Include ajax required required files
		 *
		 * @access public
		 * @since 1.0
		 * @return void
		 */
		function ajax_includes() {
			do_action('hb_ajax_includes');
		}
		
		/*
		 * Include frontend specific required files
		 *
		 * @access public
		 * @since 1.0
		 * @return void
		 */
		function frontend_includes() {
			do_action('hb_frontend_includes');
		}
		
		/*
		 * Admin menu callback
		 *
		 * @access public
		 * @since 1.0
		 * @return void
		 */
		function hooks_page_callback() {
			include_once(HB_PLUGIN_DIR.'lib/admin-page.php');
		}

		/*
		 * Admin scripts and styles 
		 *
		 * @access public
		 * @since 1.0
		 * @return void
		 */
		function admin_enqueue_scripts() {
			wp_enqueue_style( 'hb-admin-stylesheet', HB_PLUGIN_URL . 'assets/css/hb-admin.css', array(), $this->version);
			wp_enqueue_script( 'hb-admin-script', HB_PLUGIN_URL . 'assets/js/hb-admin.js', array('jquery'), $this->version);
		}
		
		/**
		 * Function to return all the directories, sub directors and files of the root directory
		 *
		 * @param string $rootPath
		 * @param string $seperator
		 * @return array
		 */
		private function parse_directory($rootPath, $seperator="/"){
			$fileArray=array();
			if (($handle = opendir($rootPath))!==false) {
				while( ($file = readdir($handle))!==false) {
					if($file !='.' && $file !='..'){
						if (is_dir($rootPath.$seperator.$file) && $file[0] != '.'){
							$array=$this->parse_directory($rootPath.$seperator.$file);
							$fileArray=array_merge($array,$fileArray);
							$fileArray[]=$rootPath.$seperator.$file;
						}
						else {
							$fileArray[]=$rootPath.$seperator.$file;
						}
					}
				}
			}
			return $fileArray;
		}

		/**
		 * Look for hooks inside all files in a dir
		 *
		 */
		private function scan_file_contents($dirInfo) {
			if( ($count=count($dirInfo))>0 ) {
				for($i=0;$i<$count;$i++){
					if (is_file($dirInfo[$i])) {
						$file=str_replace("\\","/",$dirInfo[$i]);
						$fileContent=file_get_contents($file);
						if( !empty($this->patterns) ) {
							foreach($this->patterns as $key	=>	$pattern) {
								if( $this->hook_type == $key || $this->hook_type == 'all') {
									if($this->hook_type == 'string') {
										$pattern = $this->search_string;
									}
									$this->parse_hooks($key,$fileContent,$file,$pattern);
								}
							}
						}
					}
				}
			}
		}
		
		/**
		 * extract hooks from file
		 *
		 */
		function parse_hooks($hook='do_action',$fileContent='', $file,$pattern) {

			if($hook == 'string') {
				//epl_print_r( func_get_args(), true);
				$substrCount=substr_count($fileContent,$pattern);
				if ($substrCount>0){
					$this->hooks[basename($file)]['path'] = $file;
					$this->hooks[basename($file)]['file'] = basename($file);
					$this->hooks[basename($file)][$hook] = "$pattern was found in $file";
				}

			} else {
				preg_match_all($pattern,$fileContent,$found);
				if( isset($found[1]) && !empty($found[1]) ) {
					$this->hooks[basename($file)]['path'] = $file;
					$this->hooks[basename($file)]['file'] = basename($file);
					$this->hooks[basename($file)][$hook] = $found[1];
				}
			}
			
		}
		
		function list_hooks() {
			if( !empty($this->hooks) ) {
				foreach( array_reverse($this->hooks,true) as $file_name	=>	$file_hooks) {
					echo "<div class='hb-file-wrap'>";
						echo '<div class="hb-file-summary">
								<div class="hb-file-path">'
									.$this->generate_path_html($file_hooks["path"]).'
								</div>
							</div>';
						foreach($this->colors as $key	=>	$color) {
							if(isset( $file_hooks[$key] ) ) {
								echo '<div style="color:'.$color.'" class="hb-hook-type">'.$key.'</div>';
								foreach( (array) $file_hooks[$key] as $hook_name) {
									if( trim($hook_name) != '')
										echo '<div class="hb-hook-name">'.$hook_name.'</div>';
								}
							}
						}
					echo "</div>";
				}
			}
		}
		
		function generate_path_html($path) {

			if($this->scope == 'both') {
				$path = explode(wp_normalize_path(plugin_dir_path(dirname(__DIR__))),$path);
			}
			else{
				if($this->scope == 'plugins')
					$path = explode(wp_normalize_path(plugin_dir_path(__DIR__)),$path);
				else
					$path = explode(wp_normalize_path(get_theme_root()),$path);
			}
			$path = explode('/',$path[1]);
			$html = '';
			if(!empty($path)) {
				foreach($path as $chunk) {
					if($chunk != '' && $chunk != '/') {
						$html .= '<div class="hb-path-chunk">'.$chunk.'</div><div class="hb-path-chunk">/</div>';
					}
				}
			}
			return $html;
		}
		
	}
endif; // End if class_exists check

/*
 * @since 1.0.0
 * @return object The one true WP_HOOKS_BROWSER Instance
 */
function WPHB() {
	return WP_HOOKS_BROWSER::instance();
}
// Get WP_HOOKS_BROWSER Running
WPHB();
?>