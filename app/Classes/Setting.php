<?php

namespace App\Classes;

class Setting
{
    public $html = [];
    public $settings;
    public $user;

    public function __construct($user,$settings)
    {
        $this->user = $user;
        $this->settings = $settings;
    }

    public function add(array $array): void {
        // Module system removed - only check permissions
        if (empty($array['permission']) || $this->user->isAbleTo($array['permission'])) {
            $this->html[] = $array;
        }
    }

    public function getSettings(){
        return $this->settings;
    }
}
