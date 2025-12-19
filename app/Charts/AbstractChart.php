<?php

namespace App\Charts;

use App\Models\Widget;
use Illuminate\Support\Carbon;
use App\Models\ValuationHistory;

abstract class AbstractChart
{
    protected ?Widget $widget;
    protected array $options = [];
    protected string $label = '';
    // protected ?string $description = null;

    abstract protected function compute();
    abstract public static function form(): array;

    public function __construct(?Widget $widget = null)
    {
        $this->widget = $widget;
        $this->label = $widget->title;
        // $this->description = $widget->description;
        $this->compute();
    }

    public function getWidget(): Widget
    {
        return $this->widget;
    }

    public function getLabel(): string
    {
        return $this->label;
    }

    public function getOptions(): array
    {
        return $this->options;
    }

    public function toArray(): array
    {
        return [
            'widget_id' => $this->widget->id,
            'label' => $this->getLabel(),
            'options' => $this->getOptions(),
        ];
    }

    public function timeSeriesSections(): array
    {
        $sections = [];
        $startDate = null;
        $endDate = null;
        switch ($this->widget->parameters['since']) {
            case 'YTD':
                $startDate = now()->startOfYear();
                break;
            case 'MTD':
                $startDate = now()->startOfMonth();
                break;
            case '1W':
                $startDate = now()->subWeek();
            case '6M':
                $startDate = now()->subMonths(6);
            case '1Y':
                $startDate = now()->subYear();
            case 'Beginning':
                $startDate = ValuationHistory::orderBy('date', 'asc')->first()->date;
        }
        $endDate = now();
        $currentDate = $startDate->copy();

        if ($this->widget->parameters['interval'] === 'day') {
            while ($currentDate->isBefore($endDate->copy()->add('1 day'))) {
                $sections[] = [$currentDate->copy(), $currentDate->copy()->add('1 day')];
                $currentDate = $currentDate->add('1 day');
            }
        }
        if ($this->widget->parameters['interval'] === 'week') {
            $startDate = $startDate->startOfWeek();
            $currentDate = $startDate->copy();

            while ($currentDate->isBefore($endDate->copy()->endOfWeek()->add('1 week'))) {
                $sections[] = [$currentDate->copy(), $currentDate->copy()->add('1 week')];
                $currentDate = $currentDate->add('1 week');
            }
        }
        if ($this->widget->parameters['interval'] === 'month') {
            $startDate = $startDate->startOfMonth();
            $currentDate = $startDate->copy();

            while ($currentDate->isBefore($endDate->copy()->endOfMonth()->add('1 month'))) {
                $sections[] = [$currentDate->copy(), $currentDate->copy()->add('1 month')];
                $currentDate = $currentDate->add('1 month');
            }
        }
        if ($this->widget->parameters['interval'] === 'year') {
            $startDate = $startDate->startOfYear();
            $currentDate = $startDate->copy();

            while ($currentDate->isBefore($endDate->copy()->endOfYear()->add('1 year'))) {
                $sections[] = [$currentDate->copy(), $currentDate->copy()->add('1 year')];
                $currentDate = $currentDate->add('1 year');
            }
        }
        return $sections;
    }

    public function timeSerieFormatDateLabel(Carbon $date)
    {
        if ($this->widget->parameters['interval'] === 'day') {
            return $date->format('U') * 1000;
        }
        if ($this->widget->parameters['interval'] === 'week') {
            return $date->format('W y');
        }
        if ($this->widget->parameters['interval'] === 'month') {
            return $date->format('M y');
        }
        if ($this->widget->parameters['interval'] === 'year') {
            return $date->format('Y');
        }
    }
}
