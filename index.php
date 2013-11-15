<?php

set_error_handler(E_ALL);
ini_set('display_errors', true);

require_once 'app/Router.php';

$router = Router::instance();

$router->add('/', function() {
	echo 'Home page';
});

$router->add('/hello/?', function() {
	echo 'Hello world!';
});

$router->add('/blog/([0-9a-z\-]+)/?', function($slug) use ($router) {
	$posts = array(
		'first-post',
		'hello-world',
	);

	$found = false;
	foreach ($posts as $url) {
		if ($slug == $url) {
			echo $slug;
			$found = true;
			break;
		}
	}

	if (!$found) {
		$router->notFound();
	}

});

$router->notFound(function(){
	echo 'Page not found!';
});

$router->run();
