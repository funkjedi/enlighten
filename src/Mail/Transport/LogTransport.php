<?php

namespace Enlighten\Foundation\Mail;

use Swift_Events_EventListener;
use Swift_Events_SendEvent;
use Swift_Mime_Message;
use Swift_Mime_MimeEntity;
use Swift_Transport;

class LogTransport implements Swift_Transport
{
	/**
	 * The plug-ins registered with the transport.
	 *
	 * @var array
	 */
	public $plugins = [];

	/**
	 * The path to the log file.
	 *
	 * @var string
	 */
	protected $logFile;

	/**
	 * Create a new log transport instance.
	 *
	 * @param  string $logFile
	 * @return void
	 */
	public function __construct($logFile)
	{
		$this->logFile = $logFile;
	}

	/**
	 * Create a new MailTransport instance.
	 *
	 * @return \ElectedOfficials\LogTransport
	 */
	public static function newInstance($logFile)
	{
		return new self($logFile);
	}

	/**
	 * {@inheritdoc}
	 */
	public function send(Swift_Mime_Message $message, &$failedRecipients = null)
	{
		$this->beforeSendPerformed($message);

		@file_put_contents($this->logFile, $this->getMimeEntityString($message).PHP_EOL.PHP_EOL, FILE_APPEND);
	}

	/**
	 * Get a loggable string out of a Swiftmailer entity.
	 *
	 * @param  \Swift_Mime_MimeEntity $entity
	 * @return string
	 */
	protected function getMimeEntityString(Swift_Mime_MimeEntity $entity)
	{
		$string = (string) $entity->getHeaders().PHP_EOL.$entity->getBody();

		foreach ($entity->getChildren() as $children) {
			$string .= PHP_EOL.PHP_EOL.$this->getMimeEntityString($children);
		}

		return $string;
	}

	/**
	 * {@inheritdoc}
	 */
	public function isStarted()
	{
		return true;
	}

	/**
	 * {@inheritdoc}
	 */
	public function start()
	{
		return true;
	}

	/**
	 * {@inheritdoc}
	 */
	public function stop()
	{
		return true;
	}

	/**
	 * Register a plug-in with the transport.
	 *
	 * @param  \Swift_Events_EventListener  $plugin
	 * @return void
	 */
	public function registerPlugin(Swift_Events_EventListener $plugin)
	{
		array_push($this->plugins, $plugin);
	}

	/**
	 * Iterate through registered plugins and execute plugins' methods.
	 *
	 * @param  \Swift_Mime_Message  $message
	 * @return void
	 */
	protected function beforeSendPerformed(Swift_Mime_Message $message)
	{
		$event = new Swift_Events_SendEvent($this, $message);
		foreach ($this->plugins as $plugin) {
			if (method_exists($plugin, 'beforeSendPerformed')) {
				$plugin->beforeSendPerformed($event);
			}
		}
	}
}
