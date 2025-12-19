<?php

namespace App\Enums\Widgets;

use Filament\Support\Contracts\HasLabel;

enum WidgetType: string implements HasLabel
{
    case STAT_NUMBER_OF_ENTITIES = 'stat_number_of_entities';
    case STAT_PORTFOLIO_VALUE = 'stat_portfolio_value';
    case STAT_PORTFOLIO_GAIN = 'stat_portfolio_gain';
    case STAT_PORTFOLIO_PERFORMANCE = 'stat_portfolio_performance';

    case CHART_LINE_VALUE_EVOLUTION = 'chart_line_value_evolution';
    case CHART_DONUT_ASSETS_DISTRIBUTION = 'chart_donut_assets_distribution';
    case CHART_TREEMAP_ASSETS_DISTRIBUTION = 'chart_treemap_assets_distribution';
    case CHART_BAR_PERFORMANCE_EVOLUTION = 'chart_bar_performance_evolution';

    public function getLabel(): string
    {
        return match ($this) {
            self::STAT_NUMBER_OF_ENTITIES => __('Number of entities'),
            self::STAT_PORTFOLIO_VALUE => __('Portfolio value'),
            self::STAT_PORTFOLIO_GAIN => __('Portfolio gain'),
            self::STAT_PORTFOLIO_PERFORMANCE => __('Portfolio performance'),

            self::CHART_LINE_VALUE_EVOLUTION => __('Line chart value evolution'),
            self::CHART_DONUT_ASSETS_DISTRIBUTION => __('Donut chart assets distribution'),
            self::CHART_TREEMAP_ASSETS_DISTRIBUTION => __('Treemap chart assets distribution'),
            self::CHART_BAR_PERFORMANCE_EVOLUTION => __('Bar chart performance evolution'),
        };
    }

    public function getClass(): string
    {
        return match ($this) {
            // Stats
            self::STAT_NUMBER_OF_ENTITIES => \App\Charts\Stats\NumberOfEntities::class,
            self::STAT_PORTFOLIO_VALUE => \App\Charts\Stats\PortfolioValue::class,
            self::STAT_PORTFOLIO_GAIN => \App\Charts\Stats\PortfolioGain::class,
            self::STAT_PORTFOLIO_PERFORMANCE => \App\Charts\Stats\PortfolioPerformance::class,

            // Charts
            self::CHART_LINE_VALUE_EVOLUTION => \App\Charts\Charts\LineValueEvolution::class,
            self::CHART_DONUT_ASSETS_DISTRIBUTION => \App\Charts\Charts\DonutAssetsDistribution::class,
            self::CHART_TREEMAP_ASSETS_DISTRIBUTION => \App\Charts\Charts\TreemapAssetsDistribution::class,
            self::CHART_BAR_PERFORMANCE_EVOLUTION => \App\Charts\Charts\BarPerformanceEvolution::class,
        };
    }
}
