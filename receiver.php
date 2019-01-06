<?php
require_once __DIR__ . '/vendor/autoload.php';
use PhpAmqpLib\Connection\AMQPStreamConnection;
use PhpAmqpLib\Message\AMQPMessage;

class ResponseSender 
{
	private $connection;
	private $channel;

	public function __construct()
	{
		$this->connection = new AMQPStreamConnection('localhost', 5672, 'guest', 'guest');
		$this->channel = $this->connection->channel();

		$this->channel->queue_declare('reponse_queue', false, false, false, false);
	}

	public function sendResponse()
	{
		$msg = new AMQPMessage('Response!');
		$this->channel->basic_publish($msg, '', 'reponse_queue');
		echo " Response sent \n";
	}
}

$connection = new AMQPStreamConnection('localhost', 5672, 'guest', 'guest');
$channel = $connection->channel();
try {

	$channel->queue_declare('hello', false, false, false, false);
	echo " [*] En attente de nouveau message. To exit press CTRL+C\n";
	$callback = function ($msg){
	    echo ' [x] Received ', $msg->body, "\n";
	    echo 'Executing task ... \n';
	    sleep(rand(2,5));
	    $responseSender = new ResponseSender();
	    $responseSender->sendResponse();
	    $msg->delivery_info['channel']->basic_ack($msg->delivery_info['delivery_tag']
    );
	};

	$channel->basic_consume('hello', '', false, false, false, false, $callback);
	while (count($channel->callbacks)) {
	    $channel->wait();
	}
} catch (Exception $exception) {
	die ("Error " . $exception->getMessage());
} finally {
	$channel->close();
	$connection->close();
}


function repondre() {
	$channel->basic_publish("recu", '', 'reponse_queue');
}
