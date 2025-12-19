<?php

namespace App\Helpers;

use App\Models\Asset;
use App\Models\Transaction;
use App\Models\Filter;
use App\Enums\TransactionType;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Database\Eloquent\Builder;
use App\Helpers\PortfolioCompute\Transactions;
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

        $transactions = (new Transactions())->getTransactionsBetweenDates($state1->assets(), $date1, $date2);

        $currentStartDate = $date1;
        $amountToAdd = 0;
        $performances = [];

        foreach ($transactions as $transaction) {
            $value1 = (new PortfolioState($currentStartDate, $this->filters))->value();
            $value2 = (new PortfolioState($transaction->date->subDay(), $this->filters))->value();
            $performances[] = ($value2 - $value1) / $value1;

            $currentStartDate = $transaction->date;
            $amountToAdd = $transaction->type === TransactionType::Income ? $transaction->destination_quantity : -$transaction->source_quantity;
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
        $transactions = Transaction::where('date', '>=', $date1)->where('date', '<=', $date2)->where('type', TransactionType::Income)->orWhere('type', TransactionType::Expense)->orderBy('date', 'asc')->get();

        $cf = [- (new PortfolioState($date1, $this->filters))->value()];
        $cfDates = [$date1];

        foreach ($transactions as $transaction) {
            if ($transaction->type === TransactionType::Income) {
                $cf[] = (new Transactions())->getTransactionValue($transaction);
                $cfDates[] = $transaction->date;
            }
            if ($transaction->type === TransactionType::Expense) {
                $cf[] = - (new Transactions())->getTransactionValue($transaction);
                $cfDates[] = $transaction->date;
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
