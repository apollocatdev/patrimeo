<?php

namespace App\Helpers;

use App\Models\Asset;
use App\Models\Transfer;
use App\Models\Filter;
use App\Enums\TransferType;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Database\Eloquent\Builder;
use App\Helpers\PortfolioCompute\Transfers;
use App\Helpers\PortfolioCompute\AssetValues;
use App\Helpers\PortfolioCompute\AssetFilters;
use App\Helpers\PortfolioCompute\PortfolioState;

class PortfolioCompute
{
    /**
     * @var Collection<Filter>
     */
    protected Collection $filters;


    public function __construct(?Collection $filters = null)
    {
        $this->filters = $filters ?? collect();
    }

    public function portfolioValue(?Carbon $date): float
    {
        return (new PortfolioState($date, $this->filters))->value();
    }

    public function portfolioCountAssets(?Carbon $date): int
    {
        return (new PortfolioState($date, $this->filters))->count();
    }

    public function getTWRPerformance(Carbon $date1, Carbon $date2): float
    {
        $state1 = new PortfolioState($date1, $this->filters);
        $value1 = $state1->value();
        $value2 = (new PortfolioState($date2, $this->filters))->value();

        $transfers = (new Transfers())->getTransfersBetweenDates($state1->assets(), $date1, $date2);

        $currentStartDate = $date1;
        $amountToAdd = 0;
        $performances = [];

        foreach ($transfers as $transfer) {
            $value1 = (new PortfolioState($currentStartDate, $this->filters))->value();
            $value2 = (new PortfolioState($transfer->date->subDay(), $this->filters))->value();
            $performances[] = ($value2 - $value1) / $value1;

            $currentStartDate = $transfer->date;
            $amountToAdd = $transfer->type === TransferType::Income ? $transfer->destination_quantity : -$transfer->source_quantity;
        }
        $value1 = (new PortfolioState($currentStartDate, $this->filters))->value() + $amountToAdd;
        $value2 = (new PortfolioState($date2, $this->filters))->value();
        $performances[] = ($value2 - $value1) / $value1;

        $totalPerf = 1;
        foreach ($performances as $performance) {
            $totalPerf *= (1 + $performance);
        }
        return round(($totalPerf - 1) * 100, 2);
    }

    public function getMWRPerformance(Carbon $date1, Carbon $date2)
    {
        $transfers = Transfer::where('date', '>=', $date1)->where('date', '<=', $date2)->where('type', TransferType::Income)->orWhere('type', TransferType::Expense)->orderBy('date', 'asc')->get();

        $cf = [- (new PortfolioState($date1, $this->filters))->value()];
        $cfDates = [$date1];

        foreach ($transfers as $transfer) {
            if ($transfer->type === TransferType::Income) {
                $cf[] = (new Transfers())->getTransferValue($transfer);
                $cfDates[] = $transfer->date;
            }
            if ($transfer->type === TransferType::Expense) {
                $cf[] = - (new Transfers())->getTransferValue($transfer);
                $cfDates[] = $transfer->date;
            }
        }
        $cf[] = (new PortfolioState($date2, $this->filters))->value();
        $cfDates[] = $date2;


        $van = function (float $rate) use ($cf, $cfDates, $date1): float {
            $total = 0.0;
            foreach ($cf as $i => $amount) {
                $delta = $date1->floatDiffInRealDays($cfDates[$i]) / 365.0;
                $total += $amount / pow(1 + $rate, $delta);
            }
            return $total;
        };

        // recherche du taux par dichotomie
        $low = -0.99;
        $high = 10.0;
        $mid = 0.0;
        $precision = 1e-6;

        while ($high - $low > $precision) {
            $mid = ($low + $high) / 2;
            $v = $van($mid);
            if ($v > 0) {
                $low = $mid;
            } else {
                $high = $mid;
            }
        }
        $result = ($low + $high) / 2;
        return round($result * 100, 2);
    }
}
