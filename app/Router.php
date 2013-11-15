<?php

class Router {

	private static $instance = null;
	private $routes = array();
	private $notFound = null;

	private function __construct() {}
	private function __clone() {}

	public static function instance() {
		if (!self::$instance) {
			self::$instance = new Router();
		}

		return self::$instance;
	}

	public function add($pattern, $callback) {
		$pattern = '/^' . str_replace('/', '\/', $pattern) . '$/';
		$this->routes[$pattern] = $callback;
	}

	public function notFound($callable = null) {
		if (is_callable($callable)) {
			$this->notFound = $callable;
		} elseif (is_callable($this->notFound)) {
			call_user_func($this->notFound);
		}
	}

	public function run() {
		static $running = false;
		if ($running) {
			return;
		}

		$running = true;

		$url = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
		$base = str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME']));
		if (strpos($url, $base) === 0) {
			$url = substr($url, strlen($base));
		}
		foreach ($this->routes as $pattern => $callback) {
			if (preg_match($pattern, $url, $params)) {
				array_shift($params);
				return call_user_func_array($callback, array_values($params));
			}
		}

		return $this->notFound();
	}
}
