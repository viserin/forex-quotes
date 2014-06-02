<?php
/**
 * Plugin Name: Forex Quotes
 * Plugin URI: http://www.ccfin.co/
 * Description: Stream live FX rates.
 * Author: Cloud Connected Financial
 * Author URI: http://www.ccfin.co/
 * Version: 1.0
 * License: GPLv2
 * License URI: http://www.gnu.org/licenses/gpl-2.0.html
 */

/*
		Copyright 2014  Cloud Connected Financial (email : hugo@ccfin.co)

		This program is free software; you can redistribute it and/or modify
		it under the terms of the GNU General Public License, version 2, as
		published by the Free Software Foundation.

		This program is distributed in the hope that it will be useful,
		but WITHOUT ANY WARRANTY; without even the implied warranty of
		MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
		GNU General Public License for more details.

		You should have received a copy of the GNU General Public License
		along with this program; if not, write to the Free Software
		Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
*/

if (preg_match('#' . basename(__FILE__) . '#', $_SERVER['PHP_SELF'])) {
	die('You are not allowed to call this page directly.');
}

if (!class_exists('FxTicker')) {
	class FxTicker {
		var $opt;

		private $fxTickerPairs = array(
			"EURUSD",
			"GBPUSD",
			"USDCAD",
			"USDJPY",
			"USDCHF",
			"AUDUSD",
			"NZDUSD",
			"EURGBP",
			"EURCHF",
			"EURSEK",
			"EURJPY",
			"AUDJPY",
			"GBPJPY",
			"USDSGD",
			"XAUUSD",
			"XAGUSD"
		);

		private $fxQuoteType = array(
			"ticker",
			"table"
		);

		private $fxQuoteTypePath = array(
			"ticker" 	=> "ticker.php",
			"table"		=> "table.php"
		);

		private $fxTickerSources = array(
			"MT4" 		=> "mt4.php",
			"FXCM"		=> "fxcm.php"
		);

		private $postFields = array(
			"fxticker_source" 				=> 1,
			"fxticker_ip"							=> 1,
			"fxticker_flags"					=> 1,
			"fxticker_header_color"		=> 1,
			"fxticker_header_color_2"	=> 1,
			"fxticker_expiration"			=> 2,
			"fxticker_pairs" 					=> 3
		);

		function FxTicker() {
			define("FXTICKER_PLUGIN_DIR", WP_PLUGIN_DIR . "/" . dirname(plugin_basename(__FILE__)));
			define("FXTICKER_VIEW_DIR", FXTICKER_PLUGIN_DIR . "/views/");
			define("FXTICKER_ASSETS_DIR", WP_PLUGIN_URL . "/forex-quotes/assets/");

			$this->opt = get_option("forex-ticker");

			add_action('widgets_init', array( &$this, 'register_widgets'));
			add_action("admin_menu", array( &$this, "addOptionsPage"));
			add_filter("plugin_action_links", array( &$this, "pluginActions"), 10, 2);
			add_action("wp_print_styles", array(&$this, "site_load_styles"));
			add_action("wp_print_scripts", array(&$this, "site_load_scripts"));
			add_action("wp_ajax_refreshQuotesTicker", array(&$this, "refreshQuotesTicker"));
			add_action("wp_ajax_nopriv_refreshQuotesTicker", array(&$this, "refreshQuotesTicker"));
			add_action("wp_ajax_refreshQuotesTable", array(&$this, "refreshQuotesTable"));
			add_action("wp_ajax_nopriv_refreshQuotesTable", array(&$this, "refreshQuotesTable"));
			add_shortcode("forex-quotes", array(&$this, "fxTickerSite"));

			if (function_exists("register_uninstall_hook"))
				register_uninstall_hook(FXTICKER_PLUGIN_DIR."/forex-ticker.php", array( &$this, "uninstall"));
		}

		function optionsPage() {
			if (!current_user_can("manage_options"))
				wp_die(__("Sorry, you don't have enough privileges."));

			if (isset($_POST['fxticker_save'])) {
				foreach ($this->postFields as $k => $v) {
					if (isset($_POST[$k]) && !empty($_POST[$k]) && $v == 1) {
						$this->opt[$k] = stripslashes($_POST[$k]);
					} else if (isset($_POST[$k]) && !empty($_POST[$k]) && $v == 2) {
						$this->opt[$k] = intval($_POST[$k]);

						if ($this->opt[$k] < 5) {
							$this->opt[$k] = 5;
						}
					} else if (isset($_POST[$k]) && !empty($_POST[$k]) && $v == 3) {
						$this->opt[$k] = $_POST[$k];
					} else {
						$this->opt[$k] = null;
					}
				}

				update_option("forex-ticker", $this->opt);
			}

			ob_start();
      include_once(FXTICKER_VIEW_DIR . "admin/settings.php");
      $output = ob_get_contents();
      ob_end_clean();

      echo $output;
		}

		function addOptionsPage() {
			global $wp_version;
			$menutitle = "";

			if (version_compare( $wp_version, "2.6.999", ">"))
				$menutitle .= "Forex Quotes";

			add_options_page("Forex Quotes", $menutitle, 9, "forex-ticker", array( &$this, "optionsPage"));
		}

		function pluginActions ($links, $file) {
			if($file == plugin_basename(__FILE__) && strpos( $_SERVER["SCRIPT_NAME"], "/network/") === false) {
				$link = "<a href='options-general.php?page=forex-ticker'>".__("Settings")."</a>";
				array_unshift($links, $link);
			}

			return $links;
		}

		function fxTickerSite($atts) {
			if (empty($this->opt["fxticker_expiration"])
			|| !isset($atts["type"])
			|| !in_array($atts["type"], $this->fxQuoteType)) {
				return false;
			} else {
				require_once("lib/" . $this->fxTickerSources[$this->opt["fxticker_source"]]);
				require_once("lib/wrapper.php");

				try {
					$output = false;
					$qt = implode(":,", $this->opt["fxticker_pairs"]).":,";
					$cacheId = "fxticker_".crc32($qt);
					$cachedQt = get_transient($cacheId);

					if(isset($cachedQt) && !empty($cachedQt)) {
            $q = unserialize($cachedQt);
            ob_start();
            include_once(FXTICKER_VIEW_DIR . "site/".$this->fxQuoteTypePath[$atts["type"]]);
            $output = ob_get_contents();
            ob_end_clean();
					} else {
						$wrap = new Wrapper(
							new $this->opt["fxticker_source"](
								$this->opt["fxticker_ip"],
								$this->opt["fxticker_pairs"]
							)
						);

						$q = $wrap->GetQuotes();

						if (is_array($q) && !empty($q)) {
							ob_start();
	            include_once(FXTICKER_VIEW_DIR . "site/".$this->fxQuoteTypePath[$atts["type"]]);
	            $output = ob_get_contents();
	            ob_end_clean();

	            set_transient($cacheId, serialize($q), $this->opt["fxticker_expiration"]);
						}
					}

					return $output;
				} catch (Exception $e) {
					return false;
				}
			}
		}

		/*function register_widgets() {
			register_widget("FxTicker_Widget");
		}*/

		public function refreshQuotesTicker() {
    	if (empty($this->opt["fxticker_source"]) || empty($this->opt["fxticker_expiration"])) {
				echo false;
			} else {
				require_once("lib/" . $this->fxTickerSources[$this->opt["fxticker_source"]]);
				require_once("lib/wrapper.php");

				try {
					$output = false;
					$qt = implode(":,", $this->opt["fxticker_pairs"]).":,";
					$cacheId = "fxticker_".crc32($qt);
					$cachedQt = get_transient($cacheId);

					if(isset($cachedQt) && !empty($cachedQt)) {
						$q = unserialize($cachedQt);
            ob_start();
            include_once(FXTICKER_VIEW_DIR . "site/refresh_ticker.php");
            $output = ob_get_contents();
            ob_end_clean();
					} else {
						$wrap = new Wrapper(
							new $this->opt["fxticker_source"](
								$this->opt["fxticker_ip"],
								$this->opt["fxticker_pairs"]
							)
						);

						$q = $wrap->GetQuotes();

						if (is_array($q) && !empty($q)) {
							ob_start();
	            include_once(FXTICKER_VIEW_DIR . "site/refresh_ticker.php");
	            $output = ob_get_contents();
	            ob_end_clean();

	            set_transient($cacheId, serialize($q), $this->opt["fxticker_expiration"]);
						}
					}

					echo $output;
				} catch (Exception $e) {
					echo false;
				}
			}
    	die();
    }

    public function refreshQuotesTable() {
    	if (empty($this->opt["fxticker_source"]) || empty($this->opt["fxticker_expiration"])) {
				echo false;
			} else {
				require_once("lib/" . $this->fxTickerSources[$this->opt["fxticker_source"]]);
				require_once("lib/wrapper.php");

				try {
					$output = false;
					$qt = implode(":,", $this->opt["fxticker_pairs"]).":,";
					$cacheId = "fxticker_".crc32($qt);
					$cachedQt = get_transient($cacheId);

					if(isset($cachedQt) && !empty($cachedQt)) {
						$q = unserialize($cachedQt);
            ob_start();
            include_once(FXTICKER_VIEW_DIR . "site/table.php");
            $output = ob_get_contents();
            ob_end_clean();
					} else {
						$wrap = new Wrapper(
							new $this->opt["fxticker_source"](
								$this->opt["fxticker_ip"],
								$this->opt["fxticker_pairs"]
							)
						);

						$q = $wrap->GetQuotes();

						if (is_array($q) && !empty($q)) {
							ob_start();
	            include_once(FXTICKER_VIEW_DIR . "site/table.php");
	            $output = ob_get_contents();
	            ob_end_clean();

	            set_transient($cacheId, serialize($q), $this->opt["fxticker_expiration"]);
						}
					}

					echo $output;
				} catch (Exception $e) {
					echo false;
				}
			}
    	die();
    }

		function site_load_styles() {
			wp_register_style("FxTickerCSS", plugins_url("assets/css/style.css", __FILE__));
			wp_enqueue_style("FxTickerCSS");
		}

		function site_load_scripts() {
			wp_enqueue_script("jquery");
			wp_enqueue_style("wp-color-picker");

			wp_enqueue_script("admin-script", plugin_dir_url( __FILE__ ) . "assets/js/admin-script.js", array("wp-color-picker"));

			wp_enqueue_script("ajax-req", plugin_dir_url( __FILE__ ) . "assets/js/script.js", array("jquery"));
			wp_localize_script("ajax-req", "ajax_action", array("ajaxurl" => admin_url("admin-ajax.php")));
			wp_localize_script("ajax-req", "php_data", array("expiration" => $this->opt["fxticker_expiration"]));
		}

		function register_widgets() {
			register_widget('FxTicker_Widget');
		}

		function uninstall() {
			delete_option("forex-ticker");
		}
	}
}

class FxTicker_Widget extends WP_Widget {
	private $fields = array("type");

	private $fxQuoteType = array(
		"ticker",
		"table"
	);

	function FxTicker_Widget() {
		parent::WP_Widget("fxticker_widget", "Forex Quotes", array("description" => "Live FX rates"));
	}

	function widget($args, $instance) {
		global $FxTicker;
		extract($args, EXTR_SKIP);
		$title = empty($instance["title"]) ? "&nbsp;" : apply_filters("widget_title", $instance["title"]);
		echo $before_widget;
		if (!empty($title))
		echo $before_title.$title.$after_title;
		echo $FxTicker->fxTickerSite($instance);
		echo $after_widget;
	}

	function update($new_instance, $old_instance) {
		$instance = $old_instance;
		foreach ($this->fields as $f)
			$instance[strtolower($f)] = strip_tags($new_instance[strtolower($f)]);
		return $instance;
	}

	function form($instance) {
		$default = array("title" => "Forex Quotes");
		$instance = wp_parse_args((array) $instance, $default);

		foreach ($this->fields as $field) {
			$f = strtolower($field);
			$field_id = $this->get_field_id($f);
			$field_name = $this->get_field_name($f);

			echo "\r\n".'<p><label for="'.$field_id.'">'.__($field, 'fxticker-lang').':';
			echo '<select name="'.$field_name.'" id="'.$field_id.'" class="widefat">';
			foreach ($this->fxQuoteType as $k => $v) {
				echo '<option '.(($instance[$f] == $v) ? "selected" : "").' value="'.$v.'">'.$v.'</option>';
			}
			echo '</select></label></p>';
		}
	}
}

global $FxTicker;
$FxTicker = new FxTicker();