<?php

namespace Enlighten\Mail;

use Enlighten\Mail\Transport\LogTransport;
use Enlighten\View\View;
use Exception;
use Illuminate\Contracts\Mail\Mailer as MailerContract;
use InvalidArgumentException;
use Swift_Mailer;
use Swift_MailTransport;
use Swift_Message;
use Swift_SmtpTransport;

class Mailer implements MailerContract
{
	/**
	 * Send a new message when only a raw text part.
	 *
	 * @param string
	 * @param \Closure|string
	 * @return int
	 */
	public function raw($text, $callback)
	{
		return $this->send($text, [], $callback);
	}

	/**
	 * Send a message.
	 *
	 * @param mixed
	 * @param array
	 * @param \Closure|null
	 * @return int
	 */
	public function send($html, array $data = array(), $callback = null)
	{
		if (is_callable($data)) {
			$callback = $data;
			$data = array();
		}

		if (is_string($html)) {
			try {
				$html = enlighten('view')->make($html, $data);
			}
			catch (InvalidArgumentException $e) {}
		}

		if ($callback && is_callable($callback) === false) {
			throw new InvalidArgumentException;
		}

		if (is_object($html) && $html instanceof View) {
			$html = $html->render();
		}

		$html = self::renderMergeTags((string)$html, $data);

		// Create message
		$message = Swift_Message::newInstance();
		$message->setBody($html, 'text/html');

		$mailer = Swift_Mailer::newInstance($this->getSwiftTransport());

		if ($callback) {
			$callback($message, $mailer);
		}

		// If in Test Mode the set the recipient to the Test Mode recipients
		if (enlighten_get_option('mailer', 'test_mode') === 'on') {
			$recipients = array_map('trim', explode(',', enlighten_get_option('mailer', 'test_rcpt')));
			$message->setTo($recipients);
		}

		// If no `To` has been added then send to
		// the Wordpress admin as a default
		if (count($message->getTo()) === 0) {
			$message->setTo(get_option('admin_email'));
		}

		// If no `From` has been set then set to the default
		if (count($message->getFrom()) === 0) {
			$message->setFrom(
				enlighten_get_option('mailer', 'from_email') ?: get_option('admin_email'),
				enlighten_get_option('mailer', 'from_name')  ?: null
			);
		}

		return $mailer->send($message);
	}

	/**
	 * Render a merge tags.
	 *
	 * @param string
	 * @param array
	 * @return string
	 */
	public static function renderMergeTags($content, array $mergeTags = array())
	{
		$keys = array_keys($mergeTags);
		foreach ($keys as &$key) {
			$key = '[' . trim($key,'[]') . ']';
		}

		return str_replace($keys, array_values($mergeTags), $content);
	}

	/**
	 * Retrieve SwiftMailer Transport based on settings.
	 *
	 * @return \Swift_Transport
	 */
	protected function getSwiftTransport()
	{
		$transport = enlighten_get_option('mailer', 'transport');

		if ($transport === 'LOG') {
			return LogTransport::newInstance(enlighten_get_option('mailer', 'log_file'));
		}

		if ($transport === 'SMTP') {
			$transport = Swift_SmtpTransport::newInstance(
				enlighten_get_option('mailer', 'smtp_hostname'),
				enlighten_get_option('mailer', 'smtp_port')
			);

			$username = enlighten_get_option('mailer', 'smtp_username');
			if (!empty($username)) {
				$transport->setUsername($username);
			}

			$password = enlighten_get_option('mailer', 'smtp_password');
			if (!empty($password)) {
				$transport->setPassword($password);
			}

			$encryption = enlighten_get_option('mailer', 'smtp_encryption');
			if (!empty($encryption)) {
				$transport->setEncryption($encryption);
			}

			return $transport;
		}

		return Swift_MailTransport::newInstance();
	}

	/**
	 * Get the array of failed recipients.
	 *
	 * @return array
	 */
	public function failures()
	{
		return [];
	}
}
