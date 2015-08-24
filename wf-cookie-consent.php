<?php 
	/*
	Plugin Name: WF Cookie Consent
	Plugin URI: http://www.wunderfarm.com/plugins/wf-cookie-consent
	Description: The wunderfarm-way to show how your website complies with the EU Cookie Law.
	Version: 0.9.5
	License: GNU General Public License v2 or later
	License URI: http://www.gnu.org/licenses/gpl-2.0.html
	Author: wunderfarm
	Author URI: http://www.wunderfarm.com
	*/

	defined( 'ABSPATH' ) or die( 'No script kiddies please!' );

/*
* Enqueue JS
*/

	function wf_cookieconsent_scripts() {

		wp_enqueue_script( 'wf-cookiechoices', plugin_dir_url( __FILE__ ) . 'js/cookiechoices.js', array(), '0.0.4', true );

	}
	
	add_action( 'wp_enqueue_scripts', 'wf_cookieconsent_scripts' );

/*
* Load cookie consent
*/

	function wf_cookieconsent_load() {

		$options = get_option('wf_cookieconsent_options');
		$language = wf_get_language();
		$linkHref = (empty($options[$language]['wf_linkhref']) ? '' : $options[$language]['wf_linkhref']);
		$linkText = (empty($options[$language]['wf_linktext']) ? '' : $options[$language]['wf_linktext']);
		$cookieText = (empty($options[$language]['wf_cookietext']) ? '' : $options[$language]['wf_cookietext']);
		$position = (empty($options['wf_position']) ? '' : $options['wf_position']);
		$dismissText = (empty($options[$language]['wf_dismisstext']) ? '' : $options[$language]['wf_dismisstext']);

		if(is_numeric($linkHref))
			$linkHref = get_page_link($linkHref);
	
?>
	<script type="text/javascript">

		(function(){

			var cookieBar = function() { cookieChoices.showCookieBar({ linkHref: '<?php echo esc_js($linkHref); ?>', dismissText: '<?php echo esc_js($dismissText); ?>', position: '<?php echo esc_js($position); ?>', cookieText:'<?php echo esc_js($cookieText); ?>', linkText: '<?php echo esc_js($linkText); ?>', language: '<?php echo esc_js($language); ?>'}) };

			if(document.addEventListener) {
				document.addEventListener('DOMContentLoaded', cookieBar);
			}
			else {
				document.attachEvent('onreadystatechange', function(event) { 
		            if ( document.readyState === "complete" ) {
		            	cookieBar();
					}
				});
			}
		})();

	</script>
<?php

}

	add_action('wp_footer', 'wf_cookieconsent_load', 10, 1);

/*
* Admin Page
*/

// add the admin options page
add_action('admin_menu', 'wf_cookieconsent_admin_add_page');

function wf_cookieconsent_admin_add_page() {
	add_options_page('WF Cookie Consent Settings', 'WF Cookie Consent', 'manage_options', 'wf-cookieconsent', 'wf_cookieconsent_options_page');
}

// display the admin options page
function wf_cookieconsent_options_page(){

?>

	<div class="wrap">
		<h2>WF Cookie Consent - Settings</h2>
		Here you can choose a page to link for more information, change all the texts or leave the default options.
		<form action="options.php" method="post">
		<?php settings_fields('wf_cookieconsent_options'); ?>
		<?php do_settings_sections('wf-cookieconsent'); ?>
		 
		<input name="Submit" type="submit" class="button button-primary" value="<?php esc_attr_e('Save Changes'); ?>" />
		</form>
	</div>

<?php 

}

// add the admin settings and such
add_action('admin_init', 'wf_cookieconsent_admin_init');

function wf_cookieconsent_admin_init(){
	register_setting( 'wf_cookieconsent_options', 'wf_cookieconsent_options' );

	add_settings_section('plugin_main', 'General settings', '', 'wf-cookieconsent');
	add_settings_field('wf_position', esc_html__('Position'), 'wf_cookieconsent_setting_radio', 'wf-cookieconsent', 'plugin_main', array( 'fieldname' => 'wf_position', 'fielddescription' => 'Choose the position for the infobar', 'radioFields' => array( 'top' , 'bottom') ) );

	foreach(wf_get_languages() as $lang){
		add_settings_section('plugin_main_' . $lang, 'Custom settings (' . $lang . ')', '', 'wf-cookieconsent');
		add_settings_field('wf_linkhref', esc_html__('Page to provide more information'), 'wf_cookieconsent_setting_page_selector', 'wf-cookieconsent', 'plugin_main_' . $lang, array( 'fieldname' => 'wf_linkhref', 'fielddescription' => '', 'lang' => $lang ) );
		add_settings_field('wf_linktext', esc_html__('Link text to provide more information'), 'wf_cookieconsent_setting_string', 'wf-cookieconsent', 'plugin_main_' . $lang, array( 'fieldname' => 'wf_linktext', 'fielddescription' => '', 'lang' => $lang ) );
		add_settings_field('wf_cookietext', esc_html__('Info text'), 'wf_cookieconsent_setting_string', 'wf-cookieconsent', 'plugin_main_' . $lang, array( 'fieldname' => 'wf_cookietext', 'fielddescription' => '', 'lang' => $lang ) );
		add_settings_field('wf_dismisstext', esc_html__('Dismiss text'), 'wf_cookieconsent_setting_string', 'wf-cookieconsent', 'plugin_main_' . $lang, array( 'fieldname' => 'wf_dismisstext', 'fielddescription' => '', 'lang' => $lang ) );
	}
}

function wf_cookieconsent_setting_string($args) {
	$options = get_option('wf_cookieconsent_options');
	
	if(empty($options[$args['lang']][$args['fieldname']]))
		$options[$args['lang']][$args['fieldname']] = '';
		$esc_value = esc_attr($options[$args['lang']][$args['fieldname']]);
	echo "<input id='plugin_text_string' name='wf_cookieconsent_options[{$args['lang']}][{$args['fieldname']}]' size='40' type='text' value='{$esc_value}' />";
	echo (empty($args['fielddescription']) ? '' :  "<p class='description'>". $args['fielddescription'] ."</p>");
}

function wf_cookieconsent_setting_page_selector($args) {
	$options = get_option('wf_cookieconsent_options');
	
	if(empty($options[$args['lang']][$args['fieldname']]))
		$options[$args['lang']][$args['fieldname']] = '';

	$wf_page_query = new WP_Query( array( 
	     'post_type' => 'page',
	     'suppress_filters' => true, // With this option, WPML will not use any filter
	     'orderby' => 'title', 
	     'order'=>'asc',
	     'lang'=>'all', // With this option, Polylang will return all languages
	     'nopaging'=>true
	 ) );

	echo "<select name='wf_cookieconsent_options[".$args['lang']."][".$args['fieldname']."]' id='wf_cookieconsent_options[".$args['lang']."][".$args['fieldname']."]'>";
	foreach ( $wf_page_query->posts as $post ) {
		$wf_language_information = wf_get_language_information($post->ID);
		if(!empty($wf_language_information))
			$wf_language_information = "(" .  $wf_language_information . ")";
 
		if($options[$args['lang']][$args['fieldname']] == $post->ID)
		    echo "<option class='level-0' value='" . $post->ID . "' selected='selected'>" . $post->post_title . " " . $wf_language_information . "</option>";
		else
		    echo "<option class='level-0' value='" . $post->ID . "'>" . $post->post_title . " " . $wf_language_information . "</option>";

	}
	echo "</select>";

	echo (empty($args['fielddescription']) ? '' :  "<p class='description'>". $args['fielddescription'] ."</p>");
}

function wf_cookieconsent_setting_radio($args) {
	$options = get_option('wf_cookieconsent_options');
	
	if(empty($options[$args['fieldname']]))
		$options[$args['fieldname']] = '';

	echo "<fieldset>";
	if(!empty($args['radioFields'])) {
		foreach ($args['radioFields'] as $radioField) {
			echo "<input type='radio' id='wf_rad_" . $radioField . "' name='wf_cookieconsent_options[{$args['fieldname']}]' value='{$radioField}'" . ($radioField == $options[$args['fieldname']] ? 'checked' : '')."><label for='wf_rad_" . $radioField . "'>" . $radioField . "</label><br />";
		}
	}
	echo (empty($args['fielddescription']) ? '' :  "<p class='description'>". $args['fielddescription'] ."</p>");
  	echo "</fieldset>";
}


/*
* Helpers
*/
if (!function_exists('wf_get_language')) {

	function wf_get_language() {
		$language = null;
		//get language from polylang plugin https://wordpress.org/plugins/polylang/
		if(function_exists('pll_current_language'))
			$language = pll_current_language();
		//get language from wpml plugin https://wpml.org
		elseif(defined('ICL_LANGUAGE_CODE'))
			$language = ICL_LANGUAGE_CODE;
		//return wp get_locale() - first 2 chars (en, it, de ...)
		else
			$language = substr(get_locale(),0,2);

		return $language;
	}

}

if (!function_exists('wf_get_languages')) {

	function wf_get_languages() {
		$languages = array();
		//get all languages from polylang plugin https://wordpress.org/plugins/polylang/
		global $polylang;
		if (isset($polylang)) {
			//get all languages
			$pl_languages = $polylang->model->get_languages_list();
			foreach ($pl_languages as $pl_language) {
				$languages[] = $pl_language->slug;
			}
		} else if(function_exists('icl_get_languages')) {
			//get all languages with icl_get_languages for wpml
			$wpml_languages = icl_get_languages('skip_missing=0');
			foreach ($wpml_languages as $wpml_language) {
				$languages[] = $wpml_language['language_code'];
			}
		}
		else {
			//return wp get_locale() - first 2 chars (en, it, de ...)
			$languages[] = substr(get_locale(),0,2);
		}
		return $languages;
	}

}

if (!function_exists('wf_get_language_information')) {

	function wf_get_language_information($post_id) {
		$locale = '';

		if (function_exists('pll_get_post_language')) {
			$locale = pll_get_post_language($post_id);
		} else if (function_exists('wpml_get_language_information')) {
			$language_information = wpml_get_language_information($post_id);
			$locale = $language_information['display_name'];
		}
		
		return $locale;
	}

}

?>