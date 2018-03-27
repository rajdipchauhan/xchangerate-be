<?php


namespace App\Http\Controllers;


class SocketController extends Controller
{
    public function onMessage(ConnectionInterface $conn, $input)
    {
        parent::onMessage($conn, $input);

        if (!$this->throttled) {
            $this->send($conn, 'Hello you.');

            $this->sendAll('Hello everyone.');

            $this->send($conn, 'Wait, I don\'t know you! Bye bye!');

            $this->abort($conn);
        }
    }
}