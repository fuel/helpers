<?php

namespace Fuel\Common;

class CookieStub
{
	public function setcookie($name, $value, $expire = 0, $path = null, $domain = null, $secure = false, $httponly = false)
	{
		return true;
	}
}
