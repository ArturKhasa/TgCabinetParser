<?php

namespace App\Services;

class ScheduleService
{
    private array $timeValues;
    private array $schedule;

    public function __construct()
    {
        for ($i = 0; $i <= 23; $i++) {
            $this->timeValues[$i] = pow(2, $i);
        }

        for ($i = 0; $i < 7; $i++)
            for ($j = 0; $j <= 23; $j++)
                $this->schedule[$i][$j] = 0;
    }

    public function getTimeValues(): array
    {
        return $this->timeValues;
    }

    public function getIndexes($sum): array
    {
        $values = $this->getTimeValues();
        arsort($values);
        $result = [];
        foreach ($values as $i => $value) {
            if ($value <= $sum && $sum >= 0) {
                $sum -= $value;
                $result[] = $i;
            }
        }
        return $result;
    }

    public function getSerializedScheduleFrom($scheduleString = null): ?array
    {
        if (!$scheduleString)
            return $this->schedule;

        $scheduleArray = explode(";", $scheduleString);

        foreach ($scheduleArray as $index => $daySum) {
            $indexes = $this->getIndexes($daySum);
            foreach ($indexes as $hourIndex)
                $this->schedule[$index][$hourIndex] = 1;
        }

        return $this->schedule;
    }

    public function getSumStringFromArray($schedule): ?string
    {
        if (!$schedule)
            return "";

        $result = "";

        foreach ($schedule as $dayIndex => $day) {
            $sum = 0;
            foreach ($day as $hourIndex => $hourValue) {
                if ($hourValue)
                    $sum += $this->timeValues[$hourIndex];
            }
            $result .= $sum . ";";
        }
        return trim($result, ";");
    }
}