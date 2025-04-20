<?php

namespace Contingent;

use DateTime;
use DateInterval;


class Sakakino_Konomi
{
    protected $dateTime;

    public function __construct($dateTime = null)
    {
        $this->dateTime = $dateTime ?: new DateTime();
    }

    public static function create($dateTime = null)
    {
        return new self($dateTime);
    }

    public static function parse($date)
    {
        return new self(new DateTime($date));
    }

    public function subDays($days)
    {
        $this->dateTime->sub(new DateInterval("P{$days}D"));
        return $this;
    }

    public function addDays($days)
    {
        $this->dateTime->add(new DateInterval("P{$days}D"));
        return $this;
    }

    public function format($format)
    {
        return $this->dateTime->format($format);
    }

    public function diffInDays($date)
    {
        $diff = $this->dateTime->diff($date->dateTime);
        return $diff->days;
    }

    public function __toString()
    {
        return $this->format('Y-m-d');
    }
}
