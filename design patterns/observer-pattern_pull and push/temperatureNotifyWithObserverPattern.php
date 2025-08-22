<?php

const MODE = 'PUSH'; // 'PULL' or 'PUSH'

//interface
interface Subject {
    function notify ();
}

interface Observer {
    function update (Subject|array $data);
}

//Subject
class WeatherStation implements Subject {
    public array $observersList;
    public float $lastTemp;
    public int $lastTs;
    public function __construct(array $observersList)
    {
        $this->observersList = $observersList;
    }

    public function notify ()
    {
        foreach ($this->observersList as $o) {
            $o->update(MODE === 'PULL' ? $this : ['temp' => $this->lastTemp, 'time' => $this->lastTs]);
        }
    }

    public function updateTemp($temp)
    {
        $this->lastTemp = $temp;
        $this->lastTs = time();
        $this->notify();
    }
}

//Observer
final class ConsoleObserver implements Observer {
    public function update (Subject|array $data) {
        $temp  = MODE === 'PULL' ? $data->lastTemp : $data['temp'];
        $ts  = MODE === 'PULL' ? $data->lastTs : $data['time'];
        echo sprintf("[%s] Console: %.1f °C at %s", MODE, $temp, date('H:i:s', $ts)) . PHP_EOL;
    }
}

final class AlertObserver implements Observer {
    public float $defaultAlertTemp;
    public function __construct(float $defaultAlertTemp = 35.0)
    {
        $this->defaultAlertTemp = $defaultAlertTemp;
    }

    public function update (Subject|array $data)
    {
        $temp  = MODE === 'PULL' ? $data->lastTemp : $data['temp'];
        if ($temp > $this->defaultAlertTemp) {
            echo sprintf("[%s] Alert: %.1f °C", MODE, $temp) . PHP_EOL;
        }
    }
}

final class StatsObserver implements Observer {
    private ?float $average = null;
    private ?float $minTemp = null;
    private ?float $maxTemp = null;
    private float $sum = 0.0;
    private int $count = 0;

    public function update(Subject|array $data)
    {
        $temp  = MODE === 'PULL' ? $data->lastTemp : $data['temp'];
        $this->count++;
        $this->sum+=$temp;
        $this->average = round($this->sum / $this->count, 1);
        $this->minTemp = min(($this->minTemp ?? $temp), $temp);
        $this->maxTemp = max(($this->maxTemp ?? $temp), $temp);

        echo sprintf("[%s] Stats: average -> %.1f °C, min -> %.1f °C, max ->  %.1f °C after %d times", MODE, $this->average, $this->minTemp, $this->maxTemp, $this->count) . PHP_EOL;
    }
}

function main () {
    $subject = new WeatherStation(
        [
            new ConsoleObserver(),
            new AlertObserver(37.0),
            new StatsObserver()
        ]
    );
    for ($i = 0; $i < 5; $i++) {
        $temp = rand(100, 1000) / 10.0;
        $subject->updateTemp($temp);
    }
}

main();