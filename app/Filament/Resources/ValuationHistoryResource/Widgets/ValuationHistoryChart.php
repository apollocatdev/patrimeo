<?php

namespace App\Filament\Resources\ValuationHistoryResource\Widgets;

use Carbon\Carbon;
use App\Models\Valuation;
use Flowframe\Trend\Trend;
use App\Models\ValuationHistory;
use Flowframe\Trend\TrendValue;
use Filament\Widgets\ChartWidget;

class ValuationHistoryChart extends ChartWidget
{
    // protected ?string $heading = __'Valuation History Chart';
    public ?int $valuationId = null;
    public ?string $filter = 'ytd';
    protected int|string|array $columnSpan = 'full';
    protected ?string $maxHeight = '300px';

    protected $listeners = ['refreshWidget' => '$refresh'];

    public function getHeading(): string
    {
        if ($this->valuationId === null) {
            return __('Valuation History Chart');
        }

        $valuation = Valuation::find($this->valuationId);
        return __('Valuation History Chart') . ' - ' . $valuation->name;
    }
    protected function getData(): array
    {
        if ($this->valuationId === null) {
            return [
                'datasets' => [],
                'labels' => [],
            ];
        }

        $valuation = Valuation::find($this->valuationId);

        $data = Trend::query(ValuationHistory::where('valuation_id', $this->valuationId))
            ->dateColumn('date')
            ->between(
                start: $this->mapFilterToDate($this->filter),
                end: now()
            )
            ->perDay()
            ->average('value_main_currency');
        return [
            //
            'datasets' => [
                [
                    'label' => $valuation->name,
                    'data' => $data->map(fn(TrendValue $value) => $value->aggregate),
                ]
            ],
            'labels' => $data->map(fn(TrendValue $value) => $value->date),
        ];
    }

    public function mapFilterToDate(string $filter): Carbon
    {
        return match ($filter) {
            'ytd' => now()->startOfYear(),
            'all' => $this->valuationId ?
                ValuationHistory::where('valuation_id', $this->valuationId)->orderBy('date', 'desc')->first()?->date ?? now()->subYear() :
                now()->subYear(),
            '1_year' => now()->subYear(),
            '1_month' => now()->subMonth(),
            '1_week' => now()->subWeek(),
        };
    }

    public function getFilters(): ?array
    {
        return [
            'all' => __('All'),
            'ytd' => __('Year to date'),
            '1_year' => __('1 year'),
            '1_month' => __('1 month'),
            '1_week' => __('1 week'),
        ];
    }

    protected function getType(): string
    {
        return 'line';
    }
}
