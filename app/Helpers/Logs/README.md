# Log Helpers Usage

This directory contains log helper classes for different system components.

## Available Log Helpers

- `LogCotations` - For cotation update operations
- `LogTransfers` - For transfer operations  
- `LogDashboards` - For dashboard and stat computations

## Usage Examples

### LogCotations
```php
use App\Helpers\Logs\LogCotations;

// Debug level logging
LogCotations::debug('Starting cotation update', ['asset_id' => 123]);

// Info level logging
LogCotations::info('Cotation updated successfully', ['asset_id' => 123, 'new_price' => 150.50]);

// Warning level logging
LogCotations::warning('Failed to fetch cotation from API', ['asset_id' => 123, 'error' => 'API timeout']);

// Error level logging
LogCotations::error('Critical error in cotation update', ['asset_id' => 123, 'exception' => $e->getMessage()]);
```

### LogTransfers
```php
use App\Helpers\Logs\LogTransfers;

LogTransfers::info('Transfer created', ['transfer_id' => 456, 'amount' => 1000.00]);
LogTransfers::debug('Processing transfer validation', ['transfer_id' => 456]);
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
- Cotation Updates Log Level
- Transfers Log Level  
- Dashboards Log Level

## Viewing Logs

Logs can be viewed in the Configuration > View Logs page, which provides:
- Filtering by channel (Cotations, Transfers, Dashboards)
- Filtering by level (Debug, Info, Warning, Error)
- Real-time log entry display
- Context data expansion
