<?php

require_once __DIR__ . '/../vendor/autoload.php';

class User
{
    public $name;
    public $age;
    private $email;

    public function __construct($name, $age, $email)
    {
        $this->name = $name;
        $this->age = $age;
        $this->email = $email;
    }

    public function getAge()
    {
        return $this->age;
    }

    public function getEmail()
    {
        return $this->email;
    }

    public function isAdult()
    {
        return $this->age >= 18;
    }
}
