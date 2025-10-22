<?php

namespace App\Filament\Resources\CotationHistoryResource\Widgets;

use Carbon\Carbon;
use App\Models\Cotation;
use Flowframe\Trend\Trend;
use App\Models\CotationHistory;
use Flowframe\Trend\TrendValue;
use Filament\Widgets\ChartWidget;

class CotationHistoryChart extends ChartWidget
{
    // protected ?string $heading = __'Cotation History Chart';
    public ?int $cotationId = null;
    public ?string $filter = 'ytd';
    protected int|string|array $columnSpan = 'full';
    protected ?string $maxHeight = '300px';

    protected $listeners = ['refreshWidget' => '$refresh'];

    public function getHeading(): string
    {
        if ($this->cotationId === null) {
            return __('Cotation History Chart');
        }

        $cotation = Cotation::find($this->cotationId);
        return __('Cotation History Chart') . ' - ' . $cotation->name;
    }
    protected function getData(): array
    {
        if ($this->cotationId === null) {
            return [
                'datasets' => [],
                'labels' => [],
            ];
        }

        $cotation = Cotation::find($this->cotationId);

        $data = Trend::query(CotationHistory::where('cotation_id', $this->cotationId))
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
                    'label' => $cotation->name,
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
            'all' => $this->cotationId ?
                CotationHistory::where('cotation_id', $this->cotationId)->orderBy('date', 'desc')->first()?->date ?? now()->subYear() :
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
