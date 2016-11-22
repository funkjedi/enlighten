<?php

namespace Enlighten\Admin;

class Sass
{
	/**
	 * Create an instance.
	 */
	public function __construct()
	{
		add_action('admin_menu', array($this, 'admin_menu'));
		add_action('admin_init', array($this, 'admin_init'));
	}

	/**
	 * Register the options page with the Wordpress menu.
	 */
	function admin_menu()
	{
		add_options_page('Sass Compiler', 'Sass Compiler', 'manage_options', 'enlighten-sass', array($this, 'options_page'));
	}

	/**
	 * Register settings and default fields.
	 */
	function admin_init()
	{
		register_setting('sass', 'enlighten-options-sass');

		// Compiling Options
		add_settings_section(
			'sass_compile_section',
			'Compiling Options',
			array($this, 'section_compiling_options'),
			'sass'
		);
		add_settings_field(
			'Always Compile',
			'Always Compile',
			array($this, 'field_always_compile'),
			'sass',
			'sass_compile_section'
		);
		add_settings_field(
			'Compiling Mode',
			'Compiling Mode',
			array($this, 'field_compiling_mode'),
			'sass',
			'sass_compile_section'
		);
		add_settings_field(
			'Error Display',
			'Error Display',
			array($this, 'field_errors_mode'),
			'sass',
			'sass_compile_section'
		);
	}

	/**
	 * Render the options page.
	 */
	function options_page()
	{
		?>
		<form action="options.php" method="post">
			<div class="wrap">
				<h2>Sass Compiler Settings</h2>
				<p>
					Sass works by automatically compiling and caching SCSS stylesheets added using <b>wp_enqueue_style</b>.<br>
					<em>Note: compiling stylesheets hosted on CDNs or other domains is <b><u>NOT</u></b> supported.</em>
				</p>
				<br>
				<?php
					settings_fields('sass');
					do_settings_sections('sass');
					submit_button();
				?>
			</div>
		</form>
		<?php
	}

	/**
	 * Render the compiling options section.
	 */
	public function section_compiling_options()
	{
		//
	}

	/**
	 * Render the always compile field.
	 */
	function field_always_compile()
	{
		$always_compile = enlighten_get_option('sass', 'always_compile', 0);
		?>
		<input type="checkbox" id="always_compile" name="enlighten-options-sass[always_compile]" <?php checked($always_compile, 1); ?> value="1">
		<label for="always_compile">Enabled</label>
		<div style="margin-top:8px;font-size:80%;font-style:italic;line-height:1.2;">
			When enabled stylesheets will always be compiled for each request<br>
			regardless of whether it has been updated or not.
		</div>
		<?php
	}

	/**
	 * Render the compiling mode field.
	 */
	public function field_compiling_mode()
	{
		$compiling_mode = enlighten_get_option('sass', 'compiling_mode', 'Leafo\ScssPhp\Formatter\Nested');
		?>
		<select id="compiling_options" name="enlighten-options-sass[compiling_mode]">
			<option value="Leafo\ScssPhp\Formatter\Compact"     <?php selected($compiling_mode, 'Leafo\ScssPhp\Formatter\Compact')     ?>>Compact</option>
			<option value="Leafo\ScssPhp\Formatter\Compressed"  <?php selected($compiling_mode, 'Leafo\ScssPhp\Formatter\Compressed')  ?>>Compressed</option>
			<option value="Leafo\ScssPhp\Formatter\Crunched"    <?php selected($compiling_mode, 'Leafo\ScssPhp\Formatter\Crunched')    ?>>Crunched</option>
			<option value="Leafo\ScssPhp\Formatter\Expanded"    <?php selected($compiling_mode, 'Leafo\ScssPhp\Formatter\Expanded')    ?>>Expanded</option>
			<option value="Leafo\ScssPhp\Formatter\Nested"      <?php selected($compiling_mode, 'Leafo\ScssPhp\Formatter\Nested')      ?>>Nested</option>
		</select>
		<?php
	}

	/**
	 * Render the errors mode field.
	 */
	public function field_errors_mode()
	{
		$errors_mode = enlighten_get_option('sass', 'errors_mode', 'in_header');
		?>
		<select id="errors_mode" name="enlighten-options-sass[errors_mode]">
			<option value="in_header" <?php selected($errors_mode, 'in_header') ?>>Show in Header</option>
			<option value="error_log" <?php selected($errors_mode, 'error_log') ?>>Show in Error Log</option>
		</select>
		<?php
	}
}
