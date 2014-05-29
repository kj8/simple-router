<?php

class Router {

	private static $instance = null;
	private $routes = array();
	private $notFound = null;

	private function __construct() {}
	private function __clone() {}

	/**
	 * 
	 * @return Router
	 */
	public static function instance() {
		if (!self::$instance) {
			self::$instance = new Router();
		}

		return self::$instance;
	}

	/**
	 * Add regex pattern and callable|string.
	 * If $callback is not callable but is string with one colon like "Controller:action",
	 * then controller class is created lazy.
	 * 
	 * @param string $pattern
	 * @param callable|string $callback
	 */
	public function add($pattern, $callback) {
		$pattern = '/^' . str_replace('/', '\/', $pattern) . '$/';
		$this->routes[$pattern] = $callback;
	}

	/**
	 * $callable have to be callable or string with one colon like "Controller404:action".
	 * 
	 * @param callable|string $callable
	 */
	public function notFound($callable = null) {
		if (is_callable($callable)) {
			$this->notFound = $callable;
		} elseif (count(explode(':', $callable)) == 2) {
			$this->notFound = $callable;
		} elseif (is_callable($this->notFound)) {
			call_user_func($this->notFound);
		} elseif (count(explode(':', $this->notFound)) == 2) {
			$callback = explode(':', $this->notFound);
			$controller = $callback[0];
			$action = $callback[1];
			$c = new $controller();
			$c->$action();
		}
	}

	public static function base($addSlash = true) {
		return rtrim(str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME'])), '/') . ($addSlash ? '/' : '');
	}

	public static function url($url) {
		return str_replace('//', '/', self::base(true) . $url);
	}

	public static function redirect($url, $http_response_code = 301) {
		$location = str_replace('//', '/', self::base(true) . $url);
		header('Location: ' . $location, true, $http_response_code);
		exit;
	}

	public function run() {
		static $running = false;
		if ($running) {
			return;
		}

		$running = true;

		$url = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
		$base = self::base(false);

		if (strlen($base) && strpos($url, $base) === 0) {
			$url = substr($url, strlen($base));
		}
		foreach ($this->routes as $pattern => $callback) {
			if (preg_match($pattern, $url, $params)) {
				array_shift($params);
				if (!is_callable($callback)) {
					$callback = explode(':', $callback);
					if (count($callback) == 2) {
						$controller = $callback[0];
						$action = $callback[1];
						$c = new $controller();
						return $c->$action();
					}
				} else {
					return call_user_func_array($callback, array_values($params));
				}
			}
		}

		return $this->notFound();
	}

}
