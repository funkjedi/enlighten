<?php

namespace Enlighten;

use Enlighten\Foundation\View;
use Enlighten\Foundation\Mail\LogTransport;
use Exception;
use InvalidArgumentException;
use Swift_Mailer;
use Swift_MailTransport;
use Swift_Message;
use Swift_SmtpTransport;

class Mailer
{
	/**
	 * Send a message.
	 *
	 * @param mixed
	 * @param array
	 * @param Closure|null
	 * @return integer
	 */
	public function send($html, $data = null, $callable = null)
	{
		if (is_callable($data)) {
			$callable = $data;
			$data = array();
		}

		if (is_string($html)) {
			try {
				$html = new View($html, $data);
			}
			catch (InvalidArgumentException $e) {}
		}

		if ($callable && is_callable($callable) === false) {
			throw new InvalidArgumentException;
		}

		if (is_a($html, 'Enlighten\Foundation\View')) {
			$html = $html->render();
		}

		$html = self::renderMergeTags((string)$html, $data);

		// Create message
		$message = Swift_Message::newInstance();
		$message->setBody($html, 'text/html');

		if ($callable) {
			$callable($message);
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
				enlighten_get_option('mailer', 'from_email') ?: get_option('admin_email')
				enlighten_get_option('mailer', 'from_name')  ?: null,
			);
		}

		$mailer = Swift_Mailer::newInstance($this->getSwiftTransport());
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
}
