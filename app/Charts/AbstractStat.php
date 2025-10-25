<?php

namespace App\Charts;

use App\Models\Asset;
use App\Models\Widget;
use App\Enums\FilterType;
use App\Enums\Widgets\WidgetType;
use App\Models\ValuationHistory;

abstract class AbstractStat
{
    protected ?Widget $widget;
    protected ?string $label = null;
    protected ?string $value = null;
    protected ?string $description = null;
    protected ?string $descriptionIcon = null;
    protected ?string $color = null;


    abstract protected function compute();
    abstract public static function form(): array;

    public function __construct(?Widget $widget = null)
    {
        $this->widget = $widget;
        $this->label = $widget->title;
        $this->description = $widget->description;
        $this->compute();
    }

    public function getLabel(): ?string
    {
        return $this->label;
    }

    public function getValue(): ?string
    {
        return $this->value;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function getDescriptionIcon(): ?string
    {
        return $this->descriptionIcon;
    }

    public function getColor(): ?string
    {
        return $this->color;
    }

    public function getWidget(): Widget
    {
        return $this->widget;
    }


    protected function sinceToDate()
    {
        switch ($this->widget->parameters['since']) {
            case 'YTD':
                return now()->startOfYear();
            case 'MTD':
                return now()->startOfMonth();
            case '1W':
                return now()->subWeek();
            case '6M':
                return now()->subMonths(6);
            case '1Y':
                return now()->subYear();
            case 'Beginning':
                return ValuationHistory::orderBy('date', 'asc')->first()->date;
        }
    }


    public function toArray(): array
    {
        return [
            'label' => $this->getLabel(),
            'value' => $this->getValue(),
            'description' => $this->getDescription(),
            'descriptionIcon' => $this->getDescriptionIcon(),
            'color' => $this->getColor(),

        ];
    }
}
