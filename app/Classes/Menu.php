<?php

namespace App\Classes;

class Menu
{
    public $menu = [];
    public $user;

    public function __construct($user)
    {
        $this->user = $user;
    }

    public function add(array $array): void {
        // Module system removed - only check permissions
        if (empty($array['permission']) || $this->user->isAbleTo($array['permission'])) {
            $this->menu[] = $array;
        }
    }
}
