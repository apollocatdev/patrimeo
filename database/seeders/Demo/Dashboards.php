<?php

namespace Database\Seeders\Demo;

use App\Models\User;
use App\Models\Filter;
use App\Models\Widget;
use App\Models\Taxonomy;
use App\Models\Dashboard;
use App\Data\Filters\Filters;
use Illuminate\Database\Seeder;
use App\Enums\Widgets\WidgetType;
use App\Enums\Filters\FilterEntity;
use App\Data\Filters\FilterRuleAsset;
use App\Enums\Filters\FilterRuleAssetType;

class Dashboards extends Seeder
{
    public function run(): void
    {
        $user = User::first();
        $dashboard1 = Dashboard::create([
            'navigation_title' => 'Portfolio dashboard',
            'navigation_icon' => 'hugeicons-money-bag-02',
            'navigation_sort_order' => 1,
            'n_columns' => 2,
            'default' => true,
            'user_id' => $user->id,
        ]);

        $dashboard2 = Dashboard::create([
            'navigation_title' => 'Stock shares dashboard',
            'navigation_icon' => 'hugeicons-money-bag-02',
            'navigation_sort_order' => 2,
            'n_columns' => 2,
            'default' => false,
            'user_id' => $user->id,
        ]);

        $dashboard3 = Dashboard::create([
            'navigation_title' => 'Better classes dashboard',
            'navigation_icon' => 'hugeicons-money-bag-02',
            'navigation_sort_order' => 3,
            'n_columns' => 2,
            'default' => false,
            'user_id' => $user->id,
        ]);

        $this->createWidgets($user, $dashboard1);
        $this->createFilteredWidgets($user, $dashboard2);
        $this->createBetterClassesWidgets($user, $dashboard3);
        // $dashboard2->widgets()->attach($widgets[1]);
    }

    public function createWidgets(User $user, Dashboard $dashboard)
    {
        $widget = Widget::create([
            'title' => 'Number of assets',
            'description' => 'Number of assets',
            'user_id' => $user->id,
            'type' => WidgetType::STAT_NUMBER_OF_ENTITIES,
            'parameters' => [
                'entity' => 'assets',
            ],
            'sort' => 1,
        ]);
        $dashboard->widgets()->attach($widget->id);

        $widget = Widget::create([
            'title' => 'Portfolio value',
            'description' => 'Total value of all assets',
            'user_id' => $user->id,
            'type' => WidgetType::STAT_PORTFOLIO_VALUE,
            'sort' => 0,
        ]);
        $dashboard->widgets()->attach($widget);

        $widget = Widget::create([
            'title' => 'Portfolio gain YTD',
            'description' => 'Total gain of all assets since the beginning of the year',
            'user_id' => $user->id,
            'type' => WidgetType::STAT_PORTFOLIO_GAIN,
            'parameters' => [
                'since' => 'YTD'
            ],
            'sort' => 2,
        ]);
        $dashboard->widgets()->attach($widget->id);

        $widget = Widget::create([
            'title' => 'Portfolio performance',
            'description' => 'Total gain of all assets since the beginning of the year',
            'user_id' => $user->id,
            'type' => WidgetType::STAT_PORTFOLIO_PERFORMANCE,
            'parameters' => [
                'since' => 'YTD',
                'algorithm' => 'TWR'
            ],
            'sort' => 2,
        ]);

        $dashboard->widgets()->attach($widget->id);
        $widget = Widget::create([
            'title' => 'Assets distribution',
            'description' => 'Distribution of each class per total value',
            'user_id' => $user->id,
            'type' => WidgetType::CHART_DONUT_ASSETS_DISTRIBUTION,
            'parameters' => [
                'dimension' => 'envelop_type',
            ],
            'sort' => 3,
        ]);
        $dashboard->widgets()->attach($widget->id);
        $widget = Widget::create([
            'title' => 'Assets distribution',
            'description' => 'Distribution of each class per total value',
            'user_id' => $user->id,
            'type' => WidgetType::CHART_TREEMAP_ASSETS_DISTRIBUTION,
            'parameters' => [
                'dimension' => 'class',
            ],
            'sort' => 3,
        ]);
        $dashboard->widgets()->attach($widget->id);
        $widget = Widget::create([
            'title' => 'Portfolio value evolution',
            'description' => 'Evolution of your portfolio',
            'user_id' => $user->id,
            'type' => WidgetType::CHART_LINE_VALUE_EVOLUTION,
            'parameters' => [
                'since' => 'YTD',
                'interval' => 'day',
                // 'dimension' => 'class',
            ],
            'sort' => 3,
        ]);
        $dashboard->widgets()->attach($widget->id);
        $widget = Widget::create([
            'title' => 'Performance evolution (Time-Weighted Return)',
            'description' => 'Performance of your portfolio',
            'user_id' => $user->id,
            'type' => WidgetType::CHART_BAR_PERFORMANCE_EVOLUTION,
            'parameters' => [
                'since' => 'YTD',
                'interval' => 'month',
                'algorithm' => 'TWR',
                // 'dimension' => 'class',
            ],
            'sort' => 3,
        ]);
        $dashboard->widgets()->attach($widget->id);

        $widget = Widget::create([
            'title' => 'Performance evolution (Money-Weighted Return)',
            'description' => 'Performance of your portfolio',
            'user_id' => $user->id,
            'type' => WidgetType::CHART_BAR_PERFORMANCE_EVOLUTION,
            'parameters' => [
                'since' => 'YTD',
                'interval' => 'month',
                'algorithm' => 'MWR',
                // 'dimension' => 'class',
            ],
            'sort' => 3,
        ]);
        $dashboard->widgets()->attach($widget->id);
    }

    public function createFilteredWidgets(User $user, Dashboard $dashboard)
    {
        $assetFilter = Filter::create([
            'name' => 'Actions',
            'entity' => FilterEntity::ASSETS,
            'filters' => new Filters(collect([
                new FilterRuleAsset(FilterRuleAssetType::ASSET_CLASS, ['Actions'])
            ])),
            'user_id' => $user->id,
        ]);
        $widget = Widget::create([
            'title' => 'Number of assets of type Actions',
            'description' => 'Number of assets (class = Actions)',
            'user_id' => $user->id,
            'type' => WidgetType::STAT_NUMBER_OF_ENTITIES,
            'parameters' => [
                'entity' => 'assets',
            ],
            'sort' => 1,
        ]);
        $widget->filters()->attach($assetFilter->id);
        $dashboard->widgets()->attach($widget->id);

        $widget = Widget::create([
            'title' => 'Portfolio value',
            'description' => 'Total value of all assets',
            'user_id' => $user->id,
            'type' => WidgetType::STAT_PORTFOLIO_VALUE,
            'sort' => 0,
        ]);
        $widget->filters()->attach($assetFilter->id);
        $dashboard->widgets()->attach($widget->id);

        $widget = Widget::create([
            'title' => 'Stock shares gain YTD',
            'description' => 'Total gain of all stock shares since the beginning of the year',
            'user_id' => $user->id,
            'type' => WidgetType::STAT_PORTFOLIO_GAIN,
            'parameters' => [
                'since' => 'YTD'
            ],
            'sort' => 2,
        ]);
        $widget->filters()->attach($assetFilter->id);
        $dashboard->widgets()->attach($widget->id);

        $widget = Widget::create([
            'title' => 'Portfolio performance (Stock shares)',
            'description' => 'Total gain of all stock shares since the beginning of the year',
            'user_id' => $user->id,
            'type' => WidgetType::STAT_PORTFOLIO_PERFORMANCE,
            'parameters' => [
                'since' => 'YTD',
                'algorithm' => 'TWR'
            ],
            'sort' => 2,
        ]);
        $widget->filters()->attach($assetFilter->id);
        $dashboard->widgets()->attach($widget->id);

        $widget = Widget::create([
            'title' => 'Stock shares by envelop type',
            'description' => 'Distribution of each envelop type per total value',
            'user_id' => $user->id,
            'type' => WidgetType::CHART_DONUT_ASSETS_DISTRIBUTION,
            'parameters' => [
                'dimension' => 'envelop_type',
            ],
            'sort' => 3,
        ]);
        $widget->filters()->attach($assetFilter->id);
        $dashboard->widgets()->attach($widget->id);

        $widget = Widget::create([
            'title' => 'Portfolio value evolution',
            'description' => 'Evolution of your portfolio',
            'user_id' => $user->id,
            'type' => WidgetType::CHART_LINE_VALUE_EVOLUTION,
            'parameters' => [
                'since' => 'YTD',
                'interval' => 'day',
                // 'dimension' => 'class',
            ],
            'sort' => 3,
        ]);
        $widget->filters()->attach($assetFilter->id);
        $dashboard->widgets()->attach($widget->id);

        $widget = Widget::create([
            'title' => 'Performance evolution (Time-Weighted Return)',
            'description' => 'Performance of your portfolio',
            'user_id' => $user->id,
            'type' => WidgetType::CHART_BAR_PERFORMANCE_EVOLUTION,
            'parameters' => [
                'since' => 'YTD',
                'interval' => 'month',
                'algorithm' => 'TWR',
                // 'dimension' => 'class',
            ],
            'sort' => 3,
        ]);
        $widget->filters()->attach($assetFilter->id);
        $dashboard->widgets()->attach($widget->id);
    }

    public function createBetterClassesWidgets(User $user, Dashboard $dashboard)
    {
        $taxonomy = Taxonomy::where('name', 'Better asset classes')->first();
        $widget = Widget::create([
            'title' => 'Assets distribution (custom asset classes)',
            'description' => 'Distribution of each class per total value',
            'user_id' => $user->id,
            'type' => WidgetType::CHART_TREEMAP_ASSETS_DISTRIBUTION,
            'parameters' => [
                'dimension' => 'taxonomy',
                'taxonomy' => $taxonomy->id,
            ],
            'sort' => 3,
        ]);
        $dashboard->widgets()->attach($widget->id);
    }
}
