<?php

namespace Enlighten\Pages;

class MailerPage extends Page
{
	/**
	 * Create an instance.
	 */
	public function __construct()
	{
		add_action('admin_menu', array($this, 'admin_menu'), 999);
		add_action('admin_init', array($this, 'admin_init'));
	}

	/**
	 * Register the options page with the Wordpress menu.
	 */
	function admin_menu()
	{
		add_options_page('Mailer', 'Mailer', 'manage_options', 'enlighten-mailer', array($this, 'options_page'));
	}

	/**
	 * Register settings and default fields.
	 */
	function admin_init()
	{
		register_setting('mailer', 'enlighten-options-mailer');

		add_settings_section(
			'mailer-section',
			'',
			array($this, 'section_mailer_section'),
			'mailer'
		);
		add_settings_field(
			'Test Mode',
			'Test Mode',
			array($this, 'field_mailer_test_mode'),
			'mailer',
			'mailer-section'
		);
		add_settings_field(
			'From',
			'From',
			array($this, 'field_mailer_from'),
			'mailer',
			'mailer-section'
		);
		add_settings_field(
			'Transport',
			'Transport',
			array($this, 'field_mailer_transport'),
			'mailer',
			'mailer-section'
		);
		add_settings_section(
			'mailer-smtp-section',
			'SMTP Section',
			'__return_true',
			'mailer'
		);
		add_settings_field(
			'Hostname',
			'Hostname',
			array($this, 'field_smtp_hostname'),
			'mailer',
			'mailer-smtp-section'
		);
		add_settings_field(
			'Port',
			'Port',
			array($this, 'field_smtp_port'),
			'mailer',
			'mailer-smtp-section'
		);
		add_settings_field(
			'Username',
			'Username',
			array($this, 'field_smtp_username'),
			'mailer',
			'mailer-smtp-section'
		);
		add_settings_field(
			'Password',
			'Password',
			array($this, 'field_smtp_password'),
			'mailer',
			'mailer-smtp-section'
		);
		add_settings_field(
			'Encryption',
			'Encryption',
			array($this, 'field_smtp_encryption'),
			'mailer',
			'mailer-smtp-section'
		);
		add_settings_section(
			'mailer-log-section',
			'Log Section',
			'__return_true',
			'mailer'
		);
		add_settings_field(
			'Log file location',
			'Log file location',
			array($this, 'field_log_file'),
			'mailer',
			'mailer-log-section'
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
				<h2>Mailer Settings</h2>
				<?php
					settings_fields('mailer');
					do_settings_sections('mailer');
					submit_button();
				?>
			</div>
		</form>
		<style>
		h2 {margin-top: 40px;}
		.form-table th {padding:10px 10px 10px 0;}
		.form-table td {padding:5px 10px;}
		.help-block {font-size:80%;font-style:italic;line-height:1.2;max-width:400px}
		#mailer_test_rcpt {margin-top:4px;}
		</style>
		<?php
	}

	/**
	 * Render the mailing options section.
	 */
	public function section_mailer_section()
	{
	?>
		<p>
			If sending high volumes of messages consider using a ESP like
			<a href="https://sendgrid.com/" target="_blank">Sendgrid</a>,
			<a href="https://www.mailgun.com/" target="_blank">Mailgun</a> or
			<a href="https://www.mandrill.com/" target="_blank">Mandrill</a>.
			Using an ESP to send messages with greatly increase the deliverability and performance of the
			outgoing messages. In addition most ESPs offer bounce management and some level of analytics.
		</p>
		<script type="text/javascript">
		jQuery(function($){
			var log = jQuery('h2:contains(Log Section)').hide();
			var smtp = jQuery('h2:contains(SMTP Section)').hide();
			$('#mailer_options').on('change', function(){
				var value = $(this).val();
				switch(value) {
					case 'SMTP':
						$(this).next().hide();
						log.next('.form-table').hide();
						smtp.next('.form-table').show();
						break;
					case 'LOG':
						$(this).next().hide();
						log.next('.form-table').show();
						smtp.next('.form-table').hide();
						break;
					default:
						$(this).next().show();
						log.next('.form-table').hide();
						smtp.next('.form-table').hide();
				}
			}).trigger('change');
			$('#mailer_test_mode').on('change', function(){
				if ($(this).val() === 'on') {
					$(this).next().show();
				} else {
					$(this).next().hide();
				}
			}).trigger('change');
		});
		</script>
	<?php
	}

	/**
	 * Render the from field.
	 */
	public function field_mailer_from()
	{
		$mailer_from_email = enlighten_get_option('mailer', 'from_email', '');
		$mailer_from_name  = enlighten_get_option('mailer', 'from_name',  '');
		?>
		<input id="mailer_from_name"  type="text" name="enlighten-options-mailer[from_name]"  size="20" placeholder="Name" value="<?= esc_attr($mailer_from_name) ?>"><br>
		<input id="mailer_from_email" type="text" name="enlighten-options-mailer[from_email]" size="40" placeholder="E-mail Address" value="<?= esc_attr($mailer_from_email) ?>">
		<?php
	}

	/**
	 * Render the test mode field.
	 */
	public function field_mailer_test_mode()
	{
		$mailer_test_mode = enlighten_get_option('mailer', 'test_mode', 'off');
		$mailer_test_rcpt = enlighten_get_option('mailer', 'test_rcpt', '');
		?>
		<select id="mailer_test_mode" name="enlighten-options-mailer[test_mode]">
			<option value="off" <?php selected($mailer_test_mode, 'off') ?>>OFF</option>
			<option value="on"  <?php selected($mailer_test_mode, 'on')  ?>>ON</option>
		</select>
		<div style="display:none">
			<textarea id="mailer_test_rcpt" name="enlighten-options-mailer[test_rcpt]" cols="80" rows="5"><?= esc_html($mailer_test_rcpt) ?></textarea>
			<div class="help-block">
			Comma-separated list of email addresses to be used as the recipients for
			all outgoing messages while in Test Mode.
			</div>
		</div>
		<?php
	}

	/**
	 * Render the transport field.
	 */
	public function field_mailer_transport()
	{
		$mailer_transport = enlighten_get_option('mailer', 'transport', 'MAIL');
		?>
		<select id="mailer_options" name="enlighten-options-mailer[transport]">
			<option value="MAIL" <?php selected($mailer_transport, 'MAIL') ?>>PHP mail()</option>
			<option value="SMTP" <?php selected($mailer_transport, 'SMTP') ?>>SMTP</option>
			<option value="LOG"  <?php selected($mailer_transport, 'LOG')  ?>>Log file</option>
		</select>
		<div class="help-block" style="margin-top:8px;">
		The Mail Transport sends messages by delegating to PHP's internal mail() function.
		The mail() function is not particularly predictable, or helpful. You'd be much better
		off using the SMTP Transport.
		</div>
		<?php
	}

	/**
	 * Render the SMTP hostname field.
	 */
	public function field_smtp_hostname()
	{
		$smtp_hostname = enlighten_get_option('mailer', 'smtp_hostname', '');
		?>
		<input id="smtp_hostname" type="text" name="enlighten-options-mailer[smtp_hostname]" value="<?= esc_attr($smtp_hostname) ?>">
		<?php
	}

	/**
	 * Render the SMTP hostname field.
	 */
	public function field_smtp_port()
	{
		$smtp_port = enlighten_get_option('mailer', 'smtp_port', '');
		?>
		<input id="smtp_port" type="number" name="enlighten-options-mailer[smtp_port]" value="<?= esc_attr($smtp_port) ?>">
		<?php
	}

	/**
	 * Render the SMTP username field.
	 */
	public function field_smtp_username()
	{
		$smtp_username = enlighten_get_option('mailer', 'smtp_username', '');
		?>
		<input id="smtp_username" type="text" name="enlighten-options-mailer[smtp_username]" value="<?= esc_attr($smtp_username) ?>">
		<?php
	}

	/**
	 * Render the SMTP password field.
	 */
	public function field_smtp_password()
	{
		$smtp_password = enlighten_get_option('mailer', 'smtp_password', '');
		?>
		<input id="smtp_password" type="password" name="enlighten-options-mailer[smtp_password]" value="<?= esc_attr($smtp_password) ?>">
		<?php
	}

	/**
	 * Render the SMTP encryption field.
	 */
	public function field_smtp_encryption()
	{
		$smtp_encryption = enlighten_get_option('mailer', 'smtp_encryption', '');
		?>
		<select id="mailer_options" name="enlighten-options-mailer[smtp_encryption]">
			<option value="">(none)</option>
			<option value="ssl" <?php selected($smtp_encryption, 'ssl') ?>>SSL</option>
			<option value="tls" <?php selected($smtp_encryption, 'tls') ?>>TLS</option>
		</select>
		<?php
	}

	/**
	 * Render the Log file field.
	 */
	public function field_log_file()
	{
		$writable = false;

		$filename = enlighten_get_option('mailer', 'log_file', get_stylesheet_directory().'/mail_log');

		if (!empty($filename)) {
			if (!file_exists($filename)) {
				@file_put_contents($filename,'');
			}

			$writable = is_writable($filename);
		}

		?>
		<input id="log_file" type="text" name="enlighten-options-mailer[log_file]" size="90" value="<?= esc_attr($filename) ?>">
		<?php if (!empty($filename)): ?>
			<div class="help-block">
				The file path to the log file.
				<?php if ($writable): ?>
					<span style="display:inline-block;line-height:20px;color:#1CB755">
						<span class="dashicons dashicons-yes"></span>
						Log file location writable.
					</span>
				<?php else: ?>
					<span style="display:inline-block;line-height:20px;color:red">
						<span class="dashicons dashicons-no-alt"></span>
						Log file location not writable!
					</span>
				<?php endif ?>
			</div>
		<?php endif ?>
		<?php
	}

}
