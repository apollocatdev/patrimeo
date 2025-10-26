# Log Helpers Usage

This directory contains log helper classes for different system components.

## Available Log Helpers

- `LogValuations` - For valuation update operations
- `LogTransactions` - For transaction operations  
- `LogDashboards` - For dashboard and stat computations

## Usage Examples

### LogValuations
```php
use App\Helpers\Logs\LogValuations;

// Debug level logging
LogValuations::debug('Starting valuation update', ['asset_id' => 123]);

// Info level logging
LogValuations::info('Valuation updated successfully', ['asset_id' => 123, 'new_price' => 150.50]);

// Warning level logging
LogValuations::warning('Failed to fetch valuation from API', ['asset_id' => 123, 'error' => 'API timeout']);

// Error level logging
LogValuations::error('Critical error in valuation update', ['asset_id' => 123, 'exception' => $e->getMessage()]);
```

### LogTransactions
```php
use App\Helpers\Logs\LogTransactions;

LogTransactions::info('Transaction created', ['transaction_id' => 456, 'amount' => 1000.00]);
LogTransactions::debug('Processing transaction validation', ['transaction_id' => 456]);
```

### LogDashboards
```php
use App\Helpers\Logs\LogDashboards;

LogDashboards::debug('Computing dashboard stats', ['dashboard_id' => 789]);
LogDashboards::info('Dashboard cache refreshed', ['dashboard_id' => 789]);
```

## Log Levels

The log levels are configured in the Various settings:

- **None**: No logs are written
- **Info**: Only info, warning, and error logs are written
- **Debug**: All log levels (debug, info, warning, error) are written

## Configuration

Log levels can be configured in the Settings > Various tab:
- Valuation Updates Log Level
- Transactions Log Level  
- Dashboards Log Level

## Viewing Logs

Logs can be viewed in the Configuration > View Logs page, which provides:
- Filtering by channel (Valuations, Transactions, Dashboards)
- Filtering by level (Debug, Info, Warning, Error)
- Real-time log entry display
- Context data expansion
