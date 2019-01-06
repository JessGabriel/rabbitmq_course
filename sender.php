<?php
require_once __DIR__ . '/vendor/autoload.php';
use PhpAmqpLib\Connection\AMQPStreamConnection;
use PhpAmqpLib\Message\AMQPMessage;

try {
	$connection = new AMQPStreamConnection('localhost', 5672, 'guest', 'guest');
	$channel = $connection->channel();

	list($queue, $messageCount, $consumerCount) = $channel->queue_declare('hello', true);
	$channel->queue_declare('reponse_queue', false, false, false, false);

	$msg = new AMQPMessage('Hello World!');

	$channel->basic_publish($msg, '', 'hello');

	$callback = function ($msg) {
	    echo ' [x] Message successfully sent and received by consumer';
	};

	$channel->basic_consume('reponse_queue', '', false, true, false, false, $callback);

	echo " [x] Sending message";
	while (count($channel->callbacks)) {
	    $channel->wait();
	}
} catch (Exception $exception) {
	die("Error " . $exception->getMessage());
} finally {
	$channel->close();
	$connection->close();
}
