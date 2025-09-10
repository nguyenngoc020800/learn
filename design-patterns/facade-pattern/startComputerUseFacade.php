<?php
class CPU {
    function prepare () {
        echo 'prepare to start' . PHP_EOL;
    }
}

class Ram {
    function read () {
        echo 'read ram'  . PHP_EOL;
    }
}

class Disk {
    function read () {
        echo 'read disk'  . PHP_EOL;
    }
}

class Screen {
    function turnOn () {
        echo 'turn on screen'  . PHP_EOL;
    }
}

class Computer {
    function __construct()
    {
        $this->CPU = new CPU();
        $this->ram = new Ram();
        $this->disk = new Disk();
        $this->screen = new Screen();
    }

    function start () {
        $this->CPU->prepare();
        $this->ram->read();
        $this->disk->read();
        $this->screen->turnOn();

        echo 'Turning on computer completely.';
    }
}

$computer = new Computer();
$computer->start();
