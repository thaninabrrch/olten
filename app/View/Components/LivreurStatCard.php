<?php

namespace App\View\Components;

use Illuminate\View\Component;

class LivreurStatCard extends Component
{
    public $title;
    public $value;
    public $icon;
    public $color;
    public $extra;

    public function __construct($title, $value, $icon, $color = 'blue', $extra = null)
    {
        $this->title = $title;
        $this->value = $value;
        $this->icon = $icon;
        $this->color = $color;
        $this->extra = $extra;
    }

    public function render()
    {
        return view('components.livreur-stat-card');
    }
}
