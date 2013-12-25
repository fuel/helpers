<?php
/**
 * DateTimeInterface interface for PHP < 5.5
 *
 * @codeCoverageIgnore
 */
interface DateTimeInterface
{
	public function diff($datetime2, $absolute = false);
	public function format($format);
	public function getOffset();
	public function getTimestamp();
	public function getTimezone();
	public function __wakeup();
}
