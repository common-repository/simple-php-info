<?php
/**
 * Plugin Name: Simple PHP Info
 * Version: 1.0.4
 * Plugin URI: https://kibb.in/sphp
 * Author: Josh Mckibbin
 * Author URI: https://joshmckibbin.com
 * Description: Shows phpinfo() in a dashboard widget and creates a [phpinfo] shortcode.
 * Text Domain: simple-php-info
 */


// Don't call the file directly
if ( !defined( 'ABSPATH' ) ) exit;


class SimplePHPInfo {

    /**
     * Default Options
     */
    private const DEFAULT_OPTS = array(
        'widget' => 'yes',
        'shortcode' => 'yes',
    );


    /**
     * Variables
     */
    private $options;
    private $version;
    private $field_pre = 'simple_php_info';
    private $allowed_tags = array();


    /**
     * Initialize the class
     */
    function __construct() {

        $this->version();
        $this->initialize_options();
        $this->allowed_tags();

        add_action( 'admin_menu', array($this, 'settings_page') );
        add_action( 'admin_init', array($this, 'register_settings') );
        add_filter( 'plugin_action_links_' . plugin_basename(__FILE__), array($this, 'settings_link') );

        if ($this->options['widget'] == 'yes')
            add_action( 'wp_dashboard_setup', array($this, 'dashboard_widget') );

        if ($this->options['shortcode'] == 'yes')
            add_shortcode('phpinfo', array($this, 'phpinfo_shortcode'));
    }


    /**
     * Store the version
     */
    private function version() {
        $plugin_data = get_file_data(__FILE__, array(
            'Version' => 'Version',
        ), 'plugin');

        $this->version = $plugin_data['Version'];
    }


    /**
     * Add Administration Menu
     */
    private function initialize_options() {
		$options = get_option( $this->field_pre . '_options' );

		if ( false === $options || empty( $options ) ) {
			// The options don't exist in the DB. Add them with default values.
			$options = self::DEFAULT_OPTS;
			add_option( $this->field_pre . '_options', $options );
		}

		$this->options = $options;
	}

    public function settings_page() {
        add_options_page(
            __('Simple PHP Info', 'simple-php-info'),
            __('Simple PHP Info', 'simple-php-info'),
            'manage_options',
            'simple-php-info',
            array($this, 'settings'));
    }

    public function settings() {?>
        <div class="wrap">
            <h1><?php _e('Simple PHP Info Settings', 'simple-php-info') ?></h1>
            <form action="options.php" method="post">
                <?php 
                settings_fields( $this->field_pre );
                wp_nonce_field( $this->field_pre . '_options', $this->field_pre . '_options_nonce' ); ?>

                <table class="form-table" role="presentation">
                    <tbody>
                    <tr>
                        <th scope="row"><label for="<?php echo esc_attr($this->field_pre . '-widget'); ?>"><?php _e('Enable the Dashboard Widget', 'simple-php-info'); ?></label></th>
                        <td><select id="<?php echo esc_attr($this->field_pre . '-widget'); ?>" name="<?php echo esc_attr($this->field_pre . '-widget'); ?>">
                            <option value="yes"<?php if($this->options['widget'] == 'yes') echo ' selected'; ?>><?php _e('Yes', 'simple-php-info'); ?></option>
                            <option value="no"<?php if($this->options['widget'] == 'no') echo ' selected'; ?>><?php _e('No', 'simple-php-info'); ?></option>
                        </select></td>
                    </tr>
                    <tr>
                        <th scope="row"><label for="<?php echo esc_attr($this->field_pre . '-shortcode'); ?>"><?php _e('Enable the Shortcode', 'simple-php-info'); ?></label></th>
                        <td><select id="<?php echo esc_attr($this->field_pre . '-shortcode'); ?>" name="<?php echo esc_attr($this->field_pre . '-shortcode'); ?>">
                            <option value="yes"<?php if($this->options['shortcode'] == 'yes') echo ' selected'; ?>><?php _e('Yes', 'simple-php-info'); ?></option>
                            <option value="no"<?php if($this->options['shortcode'] == 'no') echo ' selected'; ?>><?php _e('No', 'simple-php-info'); ?></option>
                        </select> <?php _e('Use the [phpinfo] shortcode to show phpinfo() on a post or page.', 'simple-php-info'); ?></td>
                    </tr>
                    </tbody>
                </table>

                <?php submit_button(); ?>
            </form>
        </div>
    <?php
    }

    public function register_settings() {
        register_setting($this->field_pre, $this->field_pre . '_options', array($this, 'settings_callback'));
    }

    public function settings_callback() {

        if ( !isset( $_POST[$this->field_pre . '_options_nonce'] ) || !wp_verify_nonce($_POST[$this->field_pre . '_options_nonce'], $this->field_pre . '_options') ) {

            wp_die( __('Sorry, your nonce did not verify.', 'simple-php-info') );

        } else {

            $widget = sanitize_text_field( $_POST[$this->field_pre . '-widget'] );
            $shortcode = sanitize_text_field( $_POST[$this->field_pre . '-shortcode'] );
            
            $this->options = array(
                'widget' => $widget,
                'shortcode' => $shortcode
            );

            return $this->options;
        }
    }


    /**
     * Add settings link in the plugin list
     */
    public function settings_link( $links ) {
        $links[] = '<a href="' . admin_url( 'options-general.php?page=simple-php-info' ) . '">' . __('Settings', 'simple-php-info') . '</a>';
	    return $links;
    }


    /**
     * Allowed HTML Tags for widget and shortcode
     */
    private function allowed_tags() {
        $this->allowed_tags = array(
            'div' => array(
                'id' => array(),
                'class' => array()
            ),
            'a' => array(
                'href' => array(),
                'name' => array(),
            ),
            'table' => array(),
            'tbody' => array(),
            'tr' => array(
                'class' => array(),
            ),
            'th' => array(),
            'td' => array(
                'class' => array(),
            ),
            'h1' => array(
                'class' => array(),
            ),
            'h2' => array(),
            'font' => array(
                'style' => array()
            ),
        );
    }


    /**
     * Add the PHP Info Dashboard Widget
     */
    public function dashboard_widget() {
        wp_add_dashboard_widget(
            'simple-php-info__widget',
            __('PHP Info', 'simple-php-info'),
            array($this, 'phpinfo_widget')
        );
    }

    public function phpinfo_widget() {
        wp_enqueue_style('simple-php-info-styles', plugins_url('/css/main.min.css', __FILE__), array(), $this->version);

        $phpinfo = $this->phpinfo_html();
        echo wp_kses($phpinfo, $this->allowed_tags);
    }


    /**
     * PHP Info Shortcode
     */
    public function phpinfo_shortcode($atts) {
        $a = shortcode_atts(array(
            'output' => 'table',
        ), $atts);

        switch( $a['output'] ) {
            case 'table-no-css':
            case 'table-nocss':
            case 'table-nostyles':
            case 'table-no-styles':
                $phpinfo = $this->phpinfo_html();
                $output = '<div id="simple-php-info__shortcode">' . $phpinfo . '</div>';
                return wp_kses($output, $this->allowed_tags);
                break;

            case 'table':
            case 'table-css':
            default:
                wp_enqueue_style('simple-php-info-styles', plugins_url('/css/main.min.css', __FILE__), array(), $this->version);
                $phpinfo = $this->phpinfo_html();
                $output = '<div id="simple-php-info__shortcode">' . $phpinfo . '</div>';
                return wp_kses($output, $this->allowed_tags);
                break;
        }

    }

    public function phpinfo_html() {
        ob_start();
        phpinfo();
        $phpinfo = ob_get_contents();
        ob_end_clean();
        $phpinfo = preg_replace('%^.*<body>(.*)</body>.*$%ms', '$1', $phpinfo);

        return $phpinfo;
    }

}

new SimplePHPInfo();
