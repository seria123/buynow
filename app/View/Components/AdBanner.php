<?php

namespace App\View\Components;

use App\Models\Ad;
use Closure;
use Illuminate\Contracts\View\View;
use Illuminate\View\Component;

class AdBanner extends Component
{
    /**
     * The position where the ad should be displayed.
     */
    public string $position;

    /**
     * The number of ads to display.
     */
    public int $limit;

    /**
     * Whether to shuffle the ads randomly.
     */
    public bool $random;

    /**
     * Create a new component instance.
     */
    public function __construct(
        string $position = 'home',
        int $limit = 1,
        bool $random = false
    ) {
        $this->position = $position;
        $this->limit = $limit;
        $this->random = $random;
    }

    /**
     * Get the ads to display.
     */
    public function ads(): \Illuminate\Database\Eloquent\Collection
    {
        $query = Ad::query()
            ->active()
            ->valid()
            ->byPosition($this->position)
            ->ordered();

        if ($this->random) {
            return $query->inRandomOrder()->take($this->limit)->get();
        }

        return $query->take($this->limit)->get();
    }

    /**
     * Get the view / contents that represent the component.
     */
    public function render(): View|Closure|string
    {
        return view('components.ad-banner');
    }
}
