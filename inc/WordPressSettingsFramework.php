<?php

/**
 * WordPress Settings Framework
 *
 * @author  Gilbert Pellegrom, James Kemp
 * @link    https://github.com/gilbitron/WordPress-Settings-Framework
 * @version 1.6.10
 * @license MIT
 */

if ( ! class_exists( 'WordPressSettingsFramework' ) ) {
	/**
	 * WordPressSettingsFramework class
	 */
	class WordPressSettingsFramework {
		/**
		 * @access private
		 * @var array
		 */
		private $settings_wrapper;

		/**
		 * @access private
		 * @var array
		 */
		private $settings;

		/**
		 * @access private
		 * @var array
		 */
		private $tabs;

		/**
		 * @access private
		 * @var string
		 */
		private $option_group;

		/**
		 * @access private
		 * @var array
		 */
		private $settings_page = array();

		/**
		 * @access private
		 * @var string
		 */
		private $options_path;

		/**
		 * @access private
		 * @var string
		 */
		private $options_url;

		/**
		 * @access protected
		 * @var array
		 */
		protected $setting_defaults = array(
			'id'          => 'default_field',
			'title'       => 'Default Field',
			'desc'        => '',
			'std'         => '',
			'type'        => 'text',
			'placeholder' => '',
			'choices'     => array(),
			'class'       => '',
			'subfields'   => array(),
		);

		/**
		 * WordPressSettingsFramework constructor.
		 *
		 * @param null|string $settings_file Path to a settings file, or null if you pass the option_group manually and construct your settings with a filter.
		 * @param bool|string $option_group  Option group name, usually a short slug.
		 */
		public function __construct( $settings_file = null, $option_group = false ) {
			$this->option_group = $option_group;

			if ( $settings_file ) {
				if ( ! is_file( $settings_file ) ) {
					return;
				}

				require_once( $settings_file );

				if ( ! $this->option_group ) {
					$this->option_group = preg_replace( "/[^a-z0-9]+/i", "", basename( $settings_file, '.php' ) );
				}
			}

			if ( empty( $this->option_group ) ) {
				return;
			}

			$this->options_path = plugin_dir_path( __FILE__ );
			$this->options_url  = plugin_dir_url( __FILE__ );

			$this->construct_settings();

			if ( is_admin() ) {
				global $pagenow;

				add_action( 'admin_init', array( $this, 'admin_init' ) );
				add_action( 'wpsf_do_settings_sections_' . $this->option_group, array( $this, 'do_tabless_settings_sections' ), 10 );

				if ( isset( $_GET['page'] ) && $_GET['page'] === $this->settings_page['slug'] ) {
					if ( $pagenow !== "options-general.php" ) {
						add_action( 'admin_notices', array( $this, 'admin_notices' ) );
					}
					add_action( 'admin_enqueue_scripts', array( $this, 'admin_enqueue_scripts' ) );
				}

				if ( $this->has_tabs() ) {
					add_action( 'wpsf_before_settings_' . $this->option_group, array( $this, 'tab_links' ) );

					remove_action( 'wpsf_do_settings_sections_' . $this->option_group, array( $this, 'do_tabless_settings_sections' ), 10 );
					add_action( 'wpsf_do_settings_sections_' . $this->option_group, array( $this, 'do_tabbed_settings_sections' ), 10 );
				}
			}
		}

		/**
		 * Construct Settings.
		 */
		public function construct_settings() {
			$this->settings_wrapper = apply_filters( 'wpsf_register_settings_' . $this->option_group, array() );

			if ( ! is_array( $this->settings_wrapper ) ) {
				return new WP_Error( 'broke', __( 'WPSF settings must be an array' ) );
			}

			// If "sections" is set, this settings group probably has tabs
			if ( isset( $this->settings_wrapper['sections'] ) ) {
				$this->tabs     = ( isset( $this->settings_wrapper['tabs'] ) ) ? $this->settings_wrapper['tabs'] : array();
				$this->settings = $this->settings_wrapper['sections'];
				// If not, it's probably just an array of settings
			} else {
				$this->settings = $this->settings_wrapper;
			}

			$this->settings_page['slug'] = sprintf( '%s-settings', str_replace( '_', '-', $this->option_group ) );
		}

		/**
		 * Get the option group for this instance
		 *
		 * @return string the "option_group"
		 */
		public function get_option_group() {
			return $this->option_group;
		}

		/**
		 * Registers the internal WordPress settings
		 */
		public function admin_init() {
			register_setting( $this->option_group, $this->option_group . '_settings', array( $this, 'settings_validate' ) );
			$this->process_settings();
		}

		/**
		 * Add Settings Page
		 *
		 * @param array $args
		 */
		public function add_settings_page( $args ) {
			$defaults = array(
				'parent_slug' => false,
				'page_slug'   => "",
				'page_title'  => "",
				'menu_title'  => "",
				'capability'  => 'manage_options',
			);

			$args = wp_parse_args( $args, $defaults );

			$this->settings_page['title']      = $args['page_title'];
			$this->settings_page['capability'] = $args['capability'];

			if ( $args['parent_slug'] ) {
				add_submenu_page(
					$args['parent_slug'],
					$this->settings_page['title'],
					$args['menu_title'],
					$args['capability'],
					$this->settings_page['slug'],
					array( $this, 'settings_page_content' )
				);
			} else {
				add_menu_page(
					$this->settings_page['title'],
					$args['menu_title'],
					$args['capability'],
					$this->settings_page['slug'],
					array( $this, 'settings_page_content' ),
					apply_filters( 'wpsf_menu_icon_url_' . $this->option_group, '' ),
					apply_filters( 'wpsf_menu_position_' . $this->option_group, null )
				);
			}
		}

		/**
		 * Settings Page Content
		 */

		public function settings_page_content() {
			if ( ! current_user_can( $this->settings_page['capability'] ) ) {
				wp_die( __( 'You do not have sufficient permissions to access this page.' ) );
			}
			?>
			<div class="wrap">
				<div id="icon-options-general" class="icon32"></div>
				<h2><?php echo $this->settings_page['title']; ?></h2>
				<?php
				// Output your settings form
				$this->settings();
				?>
			</div>
			<?php
		}

		/**
		 * Displays any errors from the WordPress settings API
		 */
		public function admin_notices() {
			settings_errors();
		}

		/**
		 * Enqueue scripts and styles
		 */
		public function admin_enqueue_scripts() {
			// scripts
			wp_register_script( 'jquery-ui-timepicker', FOCUSABLE_DIR_URI . 'assets/vendor/jquery-timepicker/jquery.ui.timepicker.js', array( 'jquery', 'jquery-ui-core' ), false, true );
			wp_register_script( 'wpsf', focusable_assets('js/wpsf.js'), array( 'jquery' ), false, true );

			wp_enqueue_script( 'jquery' );
			wp_enqueue_script( 'farbtastic' );
			wp_enqueue_script( 'media-upload' );
			wp_enqueue_script( 'thickbox' );
			wp_enqueue_script( 'jquery-ui-core' );
			wp_enqueue_script( 'jquery-ui-datepicker' );
			wp_enqueue_script( 'jquery-ui-timepicker' );
			wp_enqueue_script( 'wpsf' );

			// styles
			wp_register_style( 'jquery-ui-timepicker', FOCUSABLE_DIR_URI . 'assets/vendor/jquery-timepicker/jquery.ui.timepicker.css' );
			wp_register_style( 'wpsf', focusable_assets('css/wpsf.css'));
			wp_register_style( 'jquery-ui-css', focusable_assets('css/jquery-ui.css'));

			wp_enqueue_style( 'farbtastic' );
			wp_enqueue_style( 'thickbox' );
			wp_enqueue_style( 'jquery-ui-timepicker' );
			wp_enqueue_style( 'jquery-ui-css' );
			wp_enqueue_style( 'wpsf' );
		}

		/**
		 * Adds a filter for settings validation.
		 *
		 * @param $input
		 *
		 * @return array
		 */
		public function settings_validate( $input ) {
			return apply_filters( $this->option_group . '_settings_validate', $input );
		}

		/**
		 * Displays the "section_description" if specified in $this->settings
		 *
		 * @param array callback args from add_settings_section()
		 */
		public function section_intro( $args ) {
			if ( ! empty( $this->settings ) ) {
				foreach ( $this->settings as $section ) {
					if ( $section['section_id'] == $args['id'] ) {
						if ( isset( $section['section_description'] ) && $section['section_description'] ) {
							echo '<div class="wpsf-section-description wpsf-section-description--' . esc_attr( $section['section_id'] ) . '">' . $section['section_description'] . '</div>';
						}
						break;
					}
				}
			}
		}

		/**
		 * Processes $this->settings and adds the sections and fields via the WordPress settings API
		 */
		private function process_settings() {
			if ( ! empty( $this->settings ) ) {
				usort( $this->settings, array( $this, 'sort_array' ) );

				foreach ( $this->settings as $section ) {
					if ( isset( $section['section_id'] ) && $section['section_id'] && isset( $section['section_title'] ) ) {
						$page_name = ( $this->has_tabs() ) ? sprintf( '%s_%s', $this->option_group, $section['tab_id'] ) : $this->option_group;

						add_settings_section( $section['section_id'], $section['section_title'], array( $this, 'section_intro' ), $page_name );

						if ( isset( $section['fields'] ) && is_array( $section['fields'] ) && ! empty( $section['fields'] ) ) {
							foreach ( $section['fields'] as $field ) {
								if ( isset( $field['id'] ) && $field['id'] && isset( $field['title'] ) ) {
									$title = ! empty( $field['subtitle'] ) ? sprintf( '%s <span class="wpsf-subtitle">%s</span>', $field['title'], $field['subtitle'] ) : $field['title'];

									add_settings_field( $field['id'], $title, array( $this, 'generate_setting' ), $page_name, $section['section_id'], array( 'section' => $section, 'field' => $field ) );
								}
							}
						}
					}
				}
			}
		}

		/**
		 * Usort callback. Sorts $this->settings by "section_order"
		 *
		 * @param $a
		 * @param $b
		 *
		 * @return bool
		 */
		public function sort_array( $a, $b ) {
			if ( ! isset( $a['section_order'] ) ) {
				return false;
			}

			return $a['section_order'] > $b['section_order'];
		}

		/**
		 * Generates the HTML output of the settings fields
		 *
		 * @param array callback args from add_settings_field()
		 */
		public function generate_setting( $args ) {
			$section                = $args['section'];
			$this->setting_defaults = apply_filters( 'wpsf_defaults_' . $this->option_group, $this->setting_defaults );

			$args = wp_parse_args( $args['field'], $this->setting_defaults );

			$options = get_option( $this->option_group . '_settings' );

			$args['id']    = $this->has_tabs() ? sprintf( '%s_%s_%s', $section['tab_id'], $section['section_id'], $args['id'] ) : sprintf( '%s_%s', $section['section_id'], $args['id'] );
			$args['value'] = isset( $options[ $args['id'] ] ) ? $options[ $args['id'] ] : ( isset( $args['default'] ) ? $args['default'] : '' );
			$args['name']  = $this->generate_field_name( $args['id'] );

			do_action( 'wpsf_before_field_' . $this->option_group );
			do_action( 'wpsf_before_field_' . $this->option_group . '_' . $args['id'] );

			$this->do_field_method( $args );

			do_action( 'wpsf_after_field_' . $this->option_group );
			do_action( 'wpsf_after_field_' . $this->option_group . '_' . $args['id'] );
		}

		/**
		 * Do field method, if it exists
		 *
		 * @param array $args
		 */
		public function do_field_method( $args ) {
			$generate_field_method = sprintf( 'generate_%s_field', $args['type'] );

			if ( method_exists( $this, $generate_field_method ) ) {
				$this->$generate_field_method( $args );
			}
		}

		/**
		 * Generate: Text field
		 *
		 * @param array $args
		 */
		public function generate_text_field( $args ) {
			$args['value'] = esc_attr( stripslashes( $args['value'] ) );

			echo '<input type="text" name="' . $args['name'] . '" id="' . $args['id'] . '" value="' . $args['value'] . '" placeholder="' . $args['placeholder'] . '" class="regular-text ' . $args['class'] . '" />';

			$this->generate_description( $args['desc'] );
		}

		/**
		 * Generate: Hidden field.
		 *
		 * @param array $args
		 */
		public function generate_hidden_field( $args ) {
			$args['value'] = esc_attr( stripslashes( $args['value'] ) );

			echo '<input type="hidden" name="' . $args['name'] . '" id="' . $args['id'] . '" value="' . $args['value'] . '"  class="hidden-field ' . $args['class'] . '" />';
		}

		/**
		 * Generate: Number field
		 *
		 * @param array $args
		 */
		public function generate_number_field( $args ) {
			$args['value'] = esc_attr( stripslashes( $args['value'] ) );

			echo '<input type="number" name="' . $args['name'] . '" id="' . $args['id'] . '" value="' . $args['value'] . '" placeholder="' . $args['placeholder'] . '" class="regular-text ' . $args['class'] . '" />';

			$this->generate_description( $args['desc'] );
		}

		/**
		 * Generate: Time field
		 *
		 * @param array $args
		 */
		public function generate_time_field( $args ) {
			$args['value'] = esc_attr( stripslashes( $args['value'] ) );

			$timepicker = ! empty( $args['timepicker'] ) ? htmlentities( json_encode( $args['timepicker'] ) ) : null;

			echo '<input type="text" name="' . $args['name'] . '" id="' . $args['id'] . '" value="' . $args['value'] . '" class="timepicker regular-text ' . $args['class'] . '" data-timepicker="' . $timepicker . '" />';

			$this->generate_description( $args['desc'] );
		}

		/**
		 * Generate: Date field
		 *
		 * @param array $args
		 */
		public function generate_date_field( $args ) {
			$args['value'] = esc_attr( stripslashes( $args['value'] ) );

			$datepicker = ! empty( $args['datepicker'] ) ? htmlentities( json_encode( $args['datepicker'] ) ) : null;

			echo '<input type="text" name="' . $args['name'] . '" id="' . $args['id'] . '" value="' . $args['value'] . '" class="datepicker regular-text ' . $args['class'] . '" data-datepicker="' . $datepicker . '" />';

			$this->generate_description( $args['desc'] );
		}

		/**
		 * Generate: Group field
		 *
		 * Generates a table of subfields, and a javascript template for create new repeatable rows
		 *
		 * @param array $args
		 */
		public function generate_group_field( $args ) {
			$value     = (array) $args['value'];
			$row_count = ! empty( $value ) ? count( $value ) : 1;

			echo '<table class="widefat wpsf-group" cellspacing="0">';

			echo "<tbody>";

			for ( $row = 0; $row < $row_count; $row ++ ) {
				echo $this->generate_group_row_template( $args, false, $row );
			}

			echo "</tbody>";

			echo "</table>";

			printf( '<script type="text/html" id="%s_template">%s</script>', $args['id'], $this->generate_group_row_template( $args, true ) );

			$this->generate_description( $args['desc'] );
		}

		/**
		 * Generate group row template
		 *
		 * @param array $args  Field arguments
		 * @param bool  $blank Blank values
		 * @param int   $row   Iterator
		 *
		 * @return string|bool
		 */
		public function generate_group_row_template( $args, $blank = false, $row = 0 ) {
			$row_template = false;
			$row_id       = ! empty( $args['value'][ $row ]['row_id'] ) ? $args['value'][ $row ]['row_id'] : $row;
			$row_id_value = $blank ? '' : $row_id;

			if ( $args['subfields'] ) {
				$row_class = $row % 2 == 0 ? "alternate" : "";

				$row_template .= sprintf( '<tr class="wpsf-group__row %s">', $row_class );

				$row_template .= sprintf( '<td class="wpsf-group__row-index"><span>%d</span></td>', $row );

				$row_template .= '<td class="wpsf-group__row-fields">';

				$row_template .= '<input type="hidden" class="wpsf-group__row-id" name="' . sprintf( '%s[%d][row_id]', esc_attr( $args['name'] ), esc_attr( $row ) ) . '" value="' . esc_attr( $row_id_value ) . '" />';

				foreach ( $args['subfields'] as $subfield ) {
					$subfield = wp_parse_args( $subfield, $this->setting_defaults );

					$subfield['value'] = ( $blank ) ? "" : ( isset( $args['value'][ $row ][ $subfield['id'] ] ) ? $args['value'][ $row ][ $subfield['id'] ] : "" );
					$subfield['name']  = sprintf( '%s[%d][%s]', $args['name'], $row, $subfield['id'] );
					$subfield['id']    = sprintf( '%s_%d_%s', $args['id'], $row, $subfield['id'] );

					$class = sprintf( 'wpsf-group__field-wrapper--%s', $subfield['type'] );

					$row_template .= sprintf( '<div class="wpsf-group__field-wrapper %s">', $class );
					$row_template .= sprintf( '<label for="%s" class="wpsf-group__field-label">%s</label>', $subfield['id'], $subfield['title'] );

					ob_start();
					$this->do_field_method( $subfield );
					$row_template .= ob_get_clean();

					$row_template .= '</div>';
				}

				$row_template .= "</td>";

				$row_template .= '<td class="wpsf-group__row-actions">';

				$row_template .= sprintf( '<a href="javascript: void(0);" class="wpsf-group__row-add" data-template="%s_template"><span class="dashicons dashicons-plus-alt"></span></a>', $args['id'] );
				$row_template .= '<a href="javascript: void(0);" class="wpsf-group__row-remove"><span class="dashicons dashicons-trash"></span></a>';

				$row_template .= "</td>";

				$row_template .= '</tr>';
			}

			return $row_template;
		}

		/**
		 * Generate: Select field
		 *
		 * @param array $args
		 */
		public function generate_select_field( $args ) {
			$args['value'] = esc_html( esc_attr( $args['value'] ) );

			echo '<select name="' . $args['name'] . '" id="' . $args['id'] . '" class="' . $args['class'] . '">';

			foreach ( $args['choices'] as $value => $text ) {
				$selected = $value == $args['value'] ? 'selected="selected"' : '';

				echo sprintf( '<option value="%s" %s>%s</option>', $value, $selected, $text );
			}

			echo '</select>';

			$this->generate_description( $args['desc'] );
		}

		/**
		 * Generate: Password field
		 *
		 * @param array $args
		 */
		public function generate_password_field( $args ) {
			$args['value'] = esc_attr( stripslashes( $args['value'] ) );

			echo '<input type="password" name="' . $args['name'] . '" id="' . $args['id'] . '" value="' . $args['value'] . '" placeholder="' . $args['placeholder'] . '" class="regular-text ' . $args['class'] . '" />';

			$this->generate_description( $args['desc'] );
		}

		/**
		 * Generate: Textarea field
		 *
		 * @param array $args
		 */
		public function generate_textarea_field( $args ) {
			$args['value'] = esc_html( esc_attr( $args['value'] ) );

			echo '<textarea name="' . $args['name'] . '" id="' . $args['id'] . '" placeholder="' . $args['placeholder'] . '" rows="5" cols="60" class="' . $args['class'] . '">' . $args['value'] . '</textarea>';

			$this->generate_description( $args['desc'] );
		}

		/**
		 * Generate: Radio field
		 *
		 * @param array $args
		 */
		public function generate_radio_field( $args ) {
			$args['value'] = esc_html( esc_attr( $args['value'] ) );

			foreach ( $args['choices'] as $value => $text ) {
				$field_id = sprintf( '%s_%s', $args['id'], $value );
				$checked  = $value == $args['value'] ? 'checked="checked"' : '';

				echo sprintf( '<label><input type="radio" name="%s" id="%s" value="%s" class="%s" %s> %s</label><br />', $args['name'], $field_id, $value, $args['class'], $checked, $text );
			}

			$this->generate_description( $args['desc'] );
		}

		/**
		 * Generate: Checkbox field
		 *
		 * @param array $args
		 */
		public function generate_checkbox_field( $args ) {
			$args['value'] = esc_attr( stripslashes( $args['value'] ) );
			$checked       = $args['value'] ? 'checked="checked"' : '';

			echo '<input type="hidden" name="' . $args['name'] . '" value="0" />';
			echo '<label><input type="checkbox" name="' . $args['name'] . '" id="' . $args['id'] . '" value="1" class="' . $args['class'] . '" ' . $checked . '> ' . $args['desc'] . '</label>';
		}

		/**
		 * Generate: Checkboxes field
		 *
		 * @param array $args
		 */
		public function generate_checkboxes_field( $args ) {
			echo '<input type="hidden" name="' . $args['name'] . '" value="0" />';

			echo '<ul class="wpsf-list wpsf-list--checkboxes">';

			foreach ( $args['choices'] as $value => $text ) {
				$checked  = is_array( $args['value'] ) && in_array( strval( $value ), array_map( 'strval', $args['value'] ), true ) ? 'checked="checked"' : '';
				$field_id = sprintf( '%s_%s', $args['id'], $value );

				echo sprintf( '<li><label><input type="checkbox" name="%s[]" id="%s" value="%s" class="%s" %s> %s</label></li>', $args['name'], $field_id, $value, $args['class'], $checked, $text );
			}

			echo '</ul>';

			$this->generate_description( $args['desc'] );
		}

		/**
		 * Generate: Color field
		 *
		 * @param array $args
		 */
		public function generate_color_field( $args ) {
			$color_picker_id = sprintf( '%s_cp', $args['id'] );
			$args['value']   = esc_attr( stripslashes( $args['value'] ) );

			echo '<div style="position:relative;">';

			echo sprintf( '<input type="text" name="%s" id="%s" value="%s" class="%s">', $args['name'], $args['id'], $args['value'], $args['class'] );

			echo sprintf( '<div id="%s" style="position:absolute;top:0;left:190px;background:#fff;z-index:9999;"></div>', $color_picker_id );

			$this->generate_description( $args['desc'] );

			echo '<script type="text/javascript">
                jQuery(document).ready(function($){
                    var colorPicker = $("#' . $color_picker_id . '");
                    colorPicker.farbtastic("#' . $args['id'] . '");
                    colorPicker.hide();
                    $("#' . $args['id'] . '").on("focus", function(){
                        colorPicker.show();
                    });
                    $("#' . $args['id'] . '").on("blur", function(){
                        colorPicker.hide();
                        if($(this).val() == "") $(this).val("#");
                    });
                });
                </script>';

			echo '</div>';
		}

		/**
		 * Generate: File field
		 *
		 * @param array $args
		 */
		public function generate_file_field( $args ) {
			$args['value'] = esc_attr( $args['value'] );
			$button_id     = sprintf( '%s_button', $args['id'] );

			echo sprintf( '<input type="text" name="%s" id="%s" value="%s" class="regular-text %s"> ', $args['name'], $args['id'], $args['value'], $args['class'] );

			echo sprintf( '<input type="button" class="button wpsf-browse" id="%s" value="Browse" />', $button_id );

			echo '<script type="text/javascript">
                jQuery(document).ready(function($){
                    $("#' . $button_id . '").click(function() {

                        tb_show("", "media-upload.php?post_id=0&amp;type=image&amp;TB_iframe=true");

                        window.original_send_to_editor = window.send_to_editor;

                        window.send_to_editor = function(html) {
                            if($(html).is("img")) {
                              var imgurl = $(html).attr("src");
                            } else {
                              var imgurl = $("img",html).attr("src");
                            }
                            $("#' . $args['id'] . '").val(imgurl);
                            tb_remove();
                            window.send_to_editor = window.original_send_to_editor;
                        };

                        return false;

                    });
                });
            </script>';
		}

		/**
		 * Generate: Editor field
		 *
		 * @param array $args
		 */
		public function generate_editor_field( $args ) {
			wp_editor( $args['value'], $args['id'], array( 'textarea_name' => $args['name'] ) );

			$this->generate_description( $args['desc'] );
		}

		/**
		 * Generate: Custom field
		 *
		 * @param array $args
		 */
		public function generate_custom_field( $args ) {
			echo isset( $args['output'] ) ? $args['output'] : $args['default'];
		}

		/**
		 * Generate: Multi Inputs field
		 *
		 * @param array $args
		 */
		public function generate_multiinputs_field( $args ) {
			$field_titles = array_keys( $args['default'] );
			$values       = array_values( $args['value'] );

			echo '<div class="wpsf-multifields">';

			$i = 0;
			while ( $i < count( $values ) ):

				$field_id = sprintf( '%s_%s', $args['id'], $i );
				$value    = esc_attr( stripslashes( $values[ $i ] ) );

				echo '<div class="wpsf-multifields__field">';
				echo '<input type="text" name="' . $args['name'] . '[]" id="' . $field_id . '" value="' . $value . '" class="regular-text ' . $args['class'] . '" placeholder="' . $args['placeholder'] . '" />';
				echo '<br><span>' . $field_titles[ $i ] . '</span>';
				echo '</div>';

				$i ++; endwhile;

			echo '</div>';

			$this->generate_description( $args['desc'] );
		}

		/**
		 * Generate: Field ID
		 *
		 * @param mixed $id
		 *
		 * @return string
		 */
		public function generate_field_name( $id ) {
			return sprintf( '%s_settings[%s]', $this->option_group, $id );
		}

		/**
		 * Generate: Description
		 *
		 * @param mixed $description
		 */
		public function generate_description( $description ) {
			if ( $description && $description !== "" ) {
				echo '<p class="description">' . $description . '</p>';
			}
		}

		/**
		 * Output the settings form
		 */
		public function settings() {
			do_action( 'wpsf_before_settings_' . $this->option_group );
			?>
			<form action="options.php" method="post" novalidate>
				<?php do_action( 'wpsf_before_settings_fields_' . $this->option_group ); ?>
				<?php settings_fields( $this->option_group ); ?>

				<?php do_action( 'wpsf_do_settings_sections_' . $this->option_group ); ?>

				<?php if ( apply_filters( 'wpsf_show_save_changes_button_' . $this->option_group, true ) ) { ?>
					<p class="submit">
						<input type="submit" class="button-primary" value="<?php _e( 'Save Changes' ); ?>" />
					</p>
				<?php } ?>
			</form>
			<?php
			do_action( 'wpsf_after_settings_' . $this->option_group );
		}

		/**
		 * Helper: Get Settings
		 *
		 * @return array
		 */
		public function get_settings() {
			$settings_name = $this->option_group . '_settings';

			static $settings = array();

			if ( isset( $settings[ $settings_name ] ) ) {
				return $settings[ $settings_name ];
			}

			$saved_settings             = get_option( $this->option_group . '_settings' );
			$settings[ $settings_name ] = array();

			foreach ( $this->settings as $section ) {
				if ( empty( $section['fields'] ) ) {
					continue;
				}

				foreach ( $section['fields'] as $field ) {
					if ( ! empty( $field['default'] ) && is_array( $field['default'] ) ) {
						$field['default'] = array_values( $field['default'] );
					}

					$setting_key = $this->has_tabs() ? sprintf( '%s_%s_%s', $section['tab_id'], $section['section_id'], $field['id'] ) : sprintf( '%s_%s', $section['section_id'], $field['id'] );

					if ( isset( $saved_settings[ $setting_key ] ) ) {
						$settings[ $settings_name ][ $setting_key ] = $saved_settings[ $setting_key ];
					} else {
						$settings[ $settings_name ][ $setting_key ] = ( isset( $field['default'] ) ) ? $field['default'] : false;
					}
				}
			}

			return $settings[ $settings_name ];
		}

		/**
		 * Tabless Settings sections
		 */
		public function do_tabless_settings_sections() {
			?>
			<div class="wpsf-section wpsf-tabless">
				<?php do_settings_sections( $this->option_group ); ?>
			</div>
			<?php
		}

		/**
		 * Tabbed Settings sections
		 */
		public function do_tabbed_settings_sections() {
			$i = 0;
			foreach ( $this->tabs as $tab_data ) {
				?>
				<div id="tab-<?php echo $tab_data['id']; ?>" class="wpsf-section wpsf-tab wpsf-tab--<?php echo $tab_data['id']; ?> <?php if ( $i == 0 ) {
					echo 'wpsf-tab--active';
				} ?>">
					<div class="postbox">
						<?php do_settings_sections( sprintf( '%s_%s', $this->option_group, $tab_data['id'] ) ); ?>
					</div>
				</div>
				<?php
				$i ++;
			}
		}

		/**
		 * Output the tab links
		 */
		public function tab_links() {
			if ( ! apply_filters( 'wpsf_show_tab_links_' . $this->option_group, true ) ) {
				return;
			}

			do_action( 'wpsf_before_tab_links_' . $this->option_group );
			?>
			<h2 class="nav-tab-wrapper">
				<?php
				$i = 0;
				foreach ( $this->tabs as $tab_data ) {
					$active = $i == 0 ? 'nav-tab-active' : '';
					?>
					<a class="nav-tab wpsf-tab-link <?php echo $active; ?>" href="#tab-<?php echo $tab_data['id']; ?>"><?php echo $tab_data['title']; ?></a>
					<?php
					$i ++;
				}
				?>
			</h2>
			<?php
			do_action( 'wpsf_after_tab_links_' . $this->option_group );
		}

		/**
		 * Check if this settings instance has tabs
		 */
		public function has_tabs() {
			if ( ! empty( $this->tabs ) ) {
				return true;
			}

			return false;
		}
	}
}
