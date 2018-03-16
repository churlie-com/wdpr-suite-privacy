<?php
/*
Plugin Name: WDPR Suite: Privacy
Plugin URI: https://www.wdpr.eu
Description: Generate a GDPR-compliant privacy statement.
Text Domain: wdpr-suite-privacy
Version: 1.0.1
Author: Peter Forret for wdpr.eu
Author URI: https://www.wdpr.eu
License: MIT License
*/
/*
DISCLAIMER: 

THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE
SOFTWARE.
*/


defined( 'ABSPATH' ) or die(); //do not allow plugin file to be called directly (security protection)


// plugin folder path
if(!defined('TCPP_PLUGIN_DIR')) {
	define('TCPP_PLUGIN_DIR', plugin_dir_path( __FILE__ ));
}
// plugin folder url
if(!defined('TCPP_PLUGIN_URL')) {
	define('TCPP_PLUGIN_URL', plugin_dir_url( __FILE__ ));
}


// Add settings link on plugin page from http://bavotasan.com/2009/a-settings-link-for-your-wordpress-plugins/
function gtp_plugin_settings_link($links) {
  $settings_link = '<a href="options-general.php?page=wdpr-suite-privacy/wdpr-suite-privacy.php">Settings</a>';
  array_unshift($links, $settings_link);
  return $links;
}
$plugin = plugin_basename(__FILE__);
add_filter("plugin_action_links_$plugin", 'gtp_plugin_settings_link' );



/* --------------------- START OPTIONS PAGE ------------------ */
class wsp_Options{

	public $options;

	public function __construct(){
		//delete_option('gtp_plugin_options');
		$this->options = get_option('wsp_plugin_options');
		$this->register_settings_and_fields();
	}

	public static function add_menu_page(){
		add_options_page('WDPR Suite: Privacy Options', 'WDPR Suite: Privacy', 'administrator', __FILE__, array('wsp_Options', 'display_options_page'));
	}

	public static function display_options_page(){
		?>

		<div class="wrap">

			<h2>WDPR Suite: Privacy Options</h2>

			<form action="options.php" method="post" enctype="multipart/form-data">
				<?php
					if ( function_exists('settings_fields') ) {
						settings_fields('wsp_plugin_options'); // Output nonce, action, and option_page fields for a settings page. This should match the group name used in register_setting()
					}
					do_settings_sections(__FILE__);					
				?>

				<p class="submit">
					<input name="submit" type="submit" class="button-primary" value="Save Changes">
				</p>
			</form>

		</div>

		<?php
	}
	
	public function replace_and_render($fname,$replaces=false){
		$page = plugin_dir_path( __FILE__ ) . "/" . $fname;
		$found=false;
		if(file_exists($page)){
			$html=file_get_contents($page);
			$found=true;
		} else {
			$html="<!-- not found: $page -->";
		}
		if($found and $replaces){
			foreach($replaces as $key => $val){
				$html=str_replace("{{$key}}",$val,$html);
			}
		}
		return $html;
	}

	public function settings_page_greetbox(){
		$html=$this->replace_and_render("tpl/creator.html");
		return $html;
	}
	

	public function register_settings_and_fields(){
		$required='<sup style="color:red;" title="This field is required, cannot be empty">*</sup>';
		
		register_setting('wsp_plugin_options', 'wsp_plugin_options'); // https://codex.wordpress.org/Function_Reference/register_setting
		
		$greetbox = $this->settings_page_greetbox();
		add_settings_section('wsp_section', $greetbox, array($this, 'wsp_section_callback'), __FILE__);

		add_settings_field('wsp_onoff', 'On/Off:<br><small><span style="color:red;">Will not allow you to Turn On until you enter all required $required fields.</span></small>', array($this, 'wsp_onoff_setting'), __FILE__, 'wsp_section');
		
		add_settings_field('wsp_tos_heading', "$required TOS Heading:", array($this, 'wsp_tos_heading_setting'), __FILE__, 'wsp_section');
		add_settings_field('wsp_pp_heading', "$required PP Heading:", array($this, 'wsp_pp_heading_setting'), __FILE__, 'wsp_section');
		
		add_settings_field('wsp_companyfull', "$required Full Company Name:", array($this, 'wsp_companyfull_setting'), __FILE__, 'wsp_section');
		add_settings_field('wsp_company', "$required Company Short Name:", array($this, 'wsp_company_setting'), __FILE__, 'wsp_section');
		add_settings_field('wsp_person', "$required Person to be contacted:", array($this, 'wsp_person_setting'), __FILE__, 'wsp_section');
		add_settings_field('wsp_company_s', "$required Possessive Name:", array($this, 'wsp_company_s_setting'), __FILE__, 'wsp_section');
		
		add_settings_field('wsp_domainname', "$required Domain Name:", array($this, 'wsp_domainname_setting'), __FILE__, 'wsp_section');
		add_settings_field('wsp_websiteurl', "$required Official Website URL:", array($this, 'wsp_websiteurl_setting'), __FILE__, 'wsp_section');
		
		add_settings_field('wsp_minage', "$required Minimum Age:", array($this, 'wsp_minage_setting'), __FILE__, 'wsp_section');
		add_settings_field('wsp_time_fees', "$required Time Period for notifications:", array($this, 'wsp_time_fees_setting'), __FILE__, 'wsp_section');
		add_settings_field('wsp_time_reply', "$required Time Period for replying to priority email:", array($this, 'wsp_time_reply_setting'), __FILE__, 'wsp_section');
		add_settings_field('wsp_time_damage', "$required Time Period for determining damages:", array($this, 'wsp_time_damage_setting'), __FILE__, 'wsp_section');
		add_settings_field('wsp_dmcanoticeurl', 'DMCA Notice URL:', array($this, 'wsp_dmcanoticeurl_setting'), __FILE__, 'wsp_section');
		
		add_settings_field('wsp_venue', "$required State/Country:<br><small>e.g. France, U.K.</small>", array($this, 'wsp_venue_setting'), __FILE__, 'wsp_section');
		add_settings_field('wsp_courtlocation', "$required Court Location (City):<br><small>e.g. Paris, France</small>", array($this, 'wsp_courtlocation_setting'), __FILE__, 'wsp_section');
		add_settings_field('wsp_arbitrationlocation', "$required Arbitration Location (City):<br><small>e.g. Frankfurt, Germany</small>", array($this, 'wsp_arbitrationlocation_setting'), __FILE__, 'wsp_section');

	}

	public function wsp_section_callback(){
		//Optional
	}

  /*
      INPUTS
  */

	public function wsp_tos_heading_setting(){
		$tos_heading = "Terms of Service";
		if(!empty($this->options['wsp_tos_heading'])){
			$tos_heading = $this->options['wsp_tos_heading'];
		}
		printf( '<input name="wsp_plugin_options[wsp_tos_heading]" type="text" value="%s">', $tos_heading );
	}

	public function wsp_pp_heading_setting(){
		$pp_heading = "Privacy Policy";
		if(!empty($this->options['wsp_pp_heading'])){
			$pp_heading = $this->options['wsp_pp_heading'];
		}
		printf( '<input name="wsp_plugin_options[wsp_pp_heading]" type="text" value="%s">', $pp_heading );
	}

	public function wsp_companyfull_setting(){
		$namefull = '';
		if(!empty($this->options['wsp_companyfull'])){
			$namefull = $this->options['wsp_companyfull'];
		}
		printf( '<input name="wsp_plugin_options[wsp_companyfull]" type="text" value="%s">', $namefull );
	}

	public function wsp_company_setting(){
		$name = '';
		if(!empty($this->options['wsp_company'])){
			$name = $this->options['wsp_company'];
		}
		printf( '<input name="wsp_plugin_options[wsp_company]" type="text" value="%s">', $name );
	}

	public function wsp_person_setting(){
		$name = '';
		if(!empty($this->options['wsp_person'])){
			$name = $this->options['wsp_person'];
		}
		printf( '<input name="wsp_plugin_options[wsp_person]" type="text" value="%s">', $name );
	}

	public function wsp_company_s_setting(){
		$namepossessive = '';
		if(!empty($this->options['wsp_company_s'])){
			$namepossessive = $this->options['wsp_company_s'];
		}
		printf( '<input name="wsp_plugin_options[wsp_company_s]" type="text" value="%s">', $namepossessive );
	}

	public function wsp_domainname_setting(){
		$websiteurl = get_site_url();
		$domainname = parse_url($websiteurl,PHP_URL_HOST);
		if(!empty($this->options['wsp_domainname'])){
			$domainname = $this->options['wsp_domainname'];
		}
		printf( '<input name="wsp_plugin_options[wsp_domainname]" type="text" value="%s">', $domainname );
	}

	public function wsp_websiteurl_setting(){
		$websiteurl = get_site_url();
		if(!empty($this->options['wsp_websiteurl'])){
			$websiteurl = $this->options['wsp_websiteurl'];
		}
		printf( '<input name="wsp_plugin_options[wsp_websiteurl]" type="text" size="40" value="%s">', $websiteurl );
	}

	public function wsp_minage_setting(){
		$minage = "13";
		if(!empty($this->options['wsp_minage'])){
			$minage = $this->options['wsp_minage'];
		}
		printf( '<input name="wsp_plugin_options[wsp_minage]" type="text" size="4" value="%s"> years', $minage );
	}

	public function wsp_time_fees_setting(){
		$timefeesnotifications = "thirty (30) days";
		if(!empty($this->options['wsp_time_fees'])){
			$timefeesnotifications = $this->options['wsp_time_fees'];
		}
		printf( '<input name="wsp_plugin_options[wsp_time_fees]" type="text" value="%s">', $timefeesnotifications );
	}

	public function wsp_time_reply_setting(){
		$timereplytopriorityemail = "one business day";
		if(!empty($this->options['wsp_time_reply'])){
			$timereplytopriorityemail = $this->options['wsp_time_reply'];
		}
		printf( '<input name="wsp_plugin_options[wsp_time_reply]" type="text" value="%s">', $timereplytopriorityemail );
	}

	public function wsp_time_damage_setting(){
		$timedeterminingmaxdamages = "twelve (12) month";
		if(!empty($this->options['wsp_time_damage'])){
			$timedeterminingmaxdamages = $this->options['wsp_time_damage'];
		}
		printf( '<input name="wsp_plugin_options[wsp_time_damage]" type="text" value="%s">', $timedeterminingmaxdamages );
	}

	public function wsp_dmcanoticeurl_setting(){
		$dmcanoticeurl = '';
		if(!empty($this->options['wsp_dmcanoticeurl'])){
			$dmcanoticeurl = $this->options['wsp_dmcanoticeurl'];
		}
		printf( '<input name="wsp_plugin_options[wsp_dmcanoticeurl]" type="text" size="40" value="%s">', $dmcanoticeurl );
	}

	public function wsp_venue_setting(){
		$venue = '';
		if(!empty($this->options['wsp_venue'])){
			$venue = $this->options['wsp_venue'];
		}
		printf( '<input name="wsp_plugin_options[wsp_venue]" type="text" value="%s">', $venue );
	}

	public function wsp_courtlocation_setting(){
		$courtlocation = '';
		if(!empty($this->options['wsp_courtlocation'])){
			$courtlocation = $this->options['wsp_courtlocation'];
		}
		printf( '<input name="wsp_plugin_options[wsp_courtlocation]" type="text" value="%s">', $courtlocation );
	}

	public function wsp_arbitrationlocation_setting(){
		$arbitrationlocation = '';
		if(!empty($this->options['wsp_arbitrationlocation'])){
			$arbitrationlocation = $this->options['wsp_arbitrationlocation'];
		}
		printf( '<input name="wsp_plugin_options[wsp_arbitrationlocation]" type="text" value="%s">', $arbitrationlocation );
	}



	// last so it can check required fields!
	public function wsp_onoff_setting(){
		$onoff = 'wsp_off';
		if(
			!empty($this->options['wsp_onoff'])
			&& !empty($this->options['wsp_tos_heading'])
			&& !empty($this->options['wsp_pp_heading'])
			&& !empty($this->options['wsp_companyfull'])
			&& !empty($this->options['wsp_company'])
			&& !empty($this->options['wsp_company_s'])
			&& !empty($this->options['wsp_domainname'])
			&& !empty($this->options['wsp_websiteurl'])
			&& !empty($this->options['wsp_minage'])
			&& !empty($this->options['wsp_time_fees'])
			&& !empty($this->options['wsp_time_reply'])
			&& !empty($this->options['wsp_time_damage'])
			&& !empty($this->options['wsp_venue'])
			&& !empty($this->options['wsp_courtlocation'])
			&& !empty($this->options['wsp_arbitrationlocation'])
		){
			$onoff = $this->options['wsp_onoff'];
		}

		$off = '';
		if($onoff == 'wsp_off'){
			$off = "selected='selected'";
		}

		$on = '';
		if($onoff == 'wsp_on'){
			$on = "selected='selected'";
		}

		echo "<select name='wsp_plugin_options[wsp_onoff]'>";
		echo "<option value='wsp_off' $off>Off / Coming Soon</option>";
		echo "<option value='wsp_on' $on>On / Displaying</option>";
		echo "</select>";
	}



}

add_action('admin_menu', 'initOptionsATOSPP');

function initOptionsATOSPP(){
	wsp_Options::add_menu_page();
}

add_action('admin_init', 'initAdminATOSPP');

function initAdminATOSPP(){
	new wsp_Options();
}



/* --------------------- END OPTIONS PAGE ------------------ */




/* --------------------- START SETUP ------------------ */

function wsp_settings_url() {
	$settingspage = admin_url('options-general.php?page=wdpr-suite-privacy/wdpr-suite-privacy.php');
	
	return $settingspage;
}


function wsp_back_to_top() {
	$backtotoptext = '<p><a class="wsp-back-to-top" href="#wsp-toc">Back to top</a></p>';
	
	return $backtotoptext;
}


function wsp_separator() {
	$separator = '<div class="wsp-separator" style="width: 100%; border-bottom: 1px black solid; margin: 20px 0 20px 0;"></div>';
	
	return $separator;
}


function wsp_plugin_version() {
	if ( ! function_exists( 'get_plugins' ) ) {
		require_once( ABSPATH . 'wp-admin/includes/plugin.php' );
	}
	$plugin_folder = get_plugins( '/' . plugin_basename( dirname( __FILE__ ) ) );
	$plugin_file = basename( ( __FILE__ ) );
	$plugin_version = $plugin_folder[$plugin_file]['Version'];
	
	return $plugin_version;
}	

function wsp_publish_on_off() {
	$options = get_option('wsp_plugin_options');

	if(
		empty($options['wsp_onoff'])
		|| empty($options['wsp_tos_heading'])
		|| empty($options['wsp_pp_heading'])
		|| empty($options['wsp_companyfull'])
		|| empty($options['wsp_company'])
		|| empty($options['wsp_company_s'])
		|| empty($options['wsp_domainname'])
		|| empty($options['wsp_websiteurl'])
		|| empty($options['wsp_minage'])
		|| empty($options['wsp_time_fees'])
		|| empty($options['wsp_time_reply'])
		|| empty($options['wsp_time_damage'])
		|| empty($options['wsp_venue'])
		|| empty($options['wsp_courtlocation'])
		|| empty($options['wsp_arbitrationlocation'])
	){
		$tcpp_publish = 'wsp_off';
	} else {
		$tcpp_publish = $options['wsp_onoff'];
	}
	
	return $tcpp_publish;
}



function wsp_create_tos() {

	$options = get_option('wsp_plugin_options');
	
	$tcpp_dmcanoticeurl = $options['wsp_dmcanoticeurl'];
	if(!empty($tcpp_dmcanoticeurl)) {
		$options["wsp_dmcanotice"] = "$tcpp_bizname in accordance with <a href=\"$tcpp_dmcanoticeurl\">$tcpp_biznamepossessive Digital Millennium Copyright Act (&quot;DMCA&quot;) Policy</a>";
	} else {
		$options["wsp_dmcanotice"] = "$tcpp_bizname in accordance with $tcpp_biznamepossessive Digital Millennium Copyright Act (&quot;DMCA&quot;) Policy";
	}

	$html=$this->replace_and_render("tpl/privacy.html",$options);

	return $html;
}



function wsp_create_pp() {

	$options = get_option('wsp_plugin_options');
	$html=$this->replace_and_render("tpl/privacy.html",$options);

	return $html;
}




function wsp_create_tos_pp() {

	$options = get_option('wsp_plugin_options');

	$tcpp_termsheading = $options['wsp_tos_heading'];
	$tcpp_privacypolicyheading = $options['wsp_pp_heading'];
	// do not need the rest of this code block
	
	
	// Add Separators and Back To Top links ONLY when the Table of Contents exists, which only exists when both TOS and PP are displayed together
	// Using priority 9 so default priority of 10 is not needed in child theme functions.php
		add_filter('wsp_tos_before_heading', 'wsp_separator', 9);
		add_filter('wsp_pp_before_heading', 'wsp_separator', 9);
	
		add_filter('wsp_tos_after_end', 'wsp_back_to_top', 9);
		add_filter('wsp_pp_after_end', 'wsp_back_to_top', 9);
	
	
	$tcpp_combinedtermsandprivacy = sprintf('
	<h2 id="wsp-toc" class="wsp- tospptocheading">Contents:</h2>
	<ol class="wsp- tospptoc">
		<li><a href="#wsp-terms">%1$s</a></li>
		<li><a href="#wsp-privacy">%2$s</a></li>
	</ol>
	%3$s
	%4$s',
	$tcpp_termsheading,
	$tcpp_privacypolicyheading,
	wsp_create_tos(),
	wsp_create_pp()
	);
	
	
	return $tcpp_combinedtermsandprivacy;

	
}



/* --------------------- END SETUP ------------------ */




/* --------------------- START SHORTCODES ------------------ */


// shortcode [my_terms_of_service]
function wdpr_tos_func() {
	
	$tcpp_tcond = wsp_create_tos();
	
    $b = sprintf('<!-- wdpr-suite-privacy: %s, wdpr_tos -->', wsp_plugin_version() );
    if(!empty($tcpp_tcond) && wsp_publish_on_off() == 'wsp_on')
		{ $b .= $tcpp_tcond; }
	elseif( current_user_can('edit_plugins') ) {
		$b .= sprintf('<p>Terms are coming soon. <a href="%s">Configure this plugin\'s settings.</a></p>', wsp_settings_url() ); }
	  else { $b .= '<p>Terms are coming soon.</p>'; }

	return $b;
}
add_shortcode('wdpr_tos', 'wdpr_tos_func');


// shortcode [my_privacy_policy]
function wdpr_privacy_func() {
	
	$tcpp_privacypolicy = wsp_create_pp();
	
    $c = sprintf('<!-- wdpr-suite-privacy: %s, my_privacy_policy -->', wsp_plugin_version() );
	if(!empty($tcpp_privacypolicy) && wsp_publish_on_off() == 'wsp_on')
		{ $c .= $tcpp_privacypolicy; }
	elseif( current_user_can('edit_plugins') ) {
		$c .= sprintf('<p>Privacy Policy is coming soon. <a href="%s">Configure this plugin\'s settings.</a></p>', wsp_settings_url() ); }
	else { $c .= '<p>Privacy Policy is coming soon.</p>'; }

	return $c;
}
add_shortcode('wdpr_privacy', 'wdpr_privacy_func');



/* --------------------- END SHORTCODES ------------------ */


// End of plugin
// Do not add closing PHP tag