# Notification System

This document describes the notification system implemented in Finary2 using Filament v4.

## Overview

The notification system provides a comprehensive way to display notifications to users in the admin panel. It includes:

- A notification bell icon in the user menu
- A badge showing the number of unread notifications
- A dropdown panel displaying notifications
- Automatic marking of notifications as read
- Click outside to close functionality
- Support for different notification types (info, success, warning, error)

## Features

### Notification Bell Icon
- Located to the left of the user menu
- Shows a red badge with the count of unread notifications
- Badge displays "99+" for counts over 99

### Notification Dropdown
- Opens when clicking the bell icon
- Shows up to 10 most recent notifications
- Automatically marks all notifications as read when opened
- Individual notifications can be marked as read
- "Mark all as read" button for bulk operations

### Notification Types
- **Info**: Blue information icon
- **Success**: Green checkmark icon  
- **Warning**: Yellow warning triangle icon
- **Error**: Red error icon

### Responsive Design
- Works with both light and dark themes
- Proper hover states and transitions
- Mobile-friendly design

## Database Structure

The system uses a `notifications` table with the following fields:

- `id`: Primary key
- `title`: Notification title
- `message`: Notification content
- `type`: Notification type (info, success, warning, error)
- `data`: JSON field for additional data
- `read`: Boolean indicating if notification has been read
- `read_at`: Timestamp when notification was marked as read
- `user_id`: Foreign key to users table
- `created_at` / `updated_at`: Timestamps

## Usage

### Creating Notifications

Use the `NotificationService` to create notifications:

```php
use App\Services\NotificationService;

// Create a simple info notification
NotificationService::createInfo(
    $user,
    'Welcome',
    'Your account has been created successfully.'
);

// Create a success notification with data
NotificationService::createSuccess(
    $user,
    'Portfolio Updated',
    'Your portfolio values have been updated.',
    ['updated_at' => now()->toISOString()]
);

// Create a warning notification
NotificationService::createWarning(
    $user,
    'Market Alert',
    'High volatility detected in your assets.'
);

// Create an error notification
NotificationService::createError(
    $user,
    'Sync Failed',
    'Failed to sync market data.'
);
```

### Creating Notifications for All Users

```php
NotificationService::createForAllUsers(
    'System Maintenance',
    'Scheduled maintenance tonight at 2 AM.',
    'info'
);
```

### Direct Model Usage

You can also create notifications directly:

```php
use App\Models\Notification;

Notification::create([
    'user_id' => $user->id,
    'title' => 'Custom Title',
    'message' => 'Custom message content',
    'type' => 'info',
    'data' => ['key' => 'value']
]);
```

## Console Commands

### Test Notifications
```bash
php artisan notifications:test [user_id]
```
Creates 4 basic test notifications for a user.

### Demo Notifications
```bash
php artisan notifications:demo [user_id]
```
Creates 7 realistic demo notifications showcasing different types and scenarios.

## Admin Panel Management

Notifications can be managed in the admin panel under:
**Configuration > Notifications**

Features include:
- View all notifications
- Create new notifications
- Edit existing notifications
- Delete notifications
- Filter by type, read status, and date
- Bulk operations

## Livewire Component

The notification dropdown is implemented as a Livewire component (`NotificationDropdown`) that:

- Fetches notifications from the database
- Handles opening/closing the dropdown
- Manages marking notifications as read
- Provides real-time updates

## Styling

The notification system uses:
- Tailwind CSS for styling
- Alpine.js for interactivity
- Heroicons for icons
- Filament's design system for consistency

## Customization

### Adding New Notification Types
1. Add the new type to the `NotificationResource` form options
2. Update the view to handle the new type with appropriate styling
3. Consider adding a new service method if needed

### Modifying the Dropdown
The dropdown view is located at `resources/views/livewire/notification-dropdown.blade.php`

### Changing Notification Limits
Modify the `recent(10)` call in the `NotificationDropdown` component to change how many notifications are displayed.

## Integration Examples

### Portfolio Updates
```php
// When portfolio values are updated
NotificationService::createSuccess(
    $user,
    'Portfolio Updated',
    "Your portfolio value is now {$formattedValue}",
    ['new_value' => $value, 'change_percentage' => $percentage]
);
```

### Market Alerts
```php
// When significant market movements are detected
if ($priceChange > $threshold) {
    NotificationService::createWarning(
        $user,
        'Market Movement',
        "{$asset} has moved {$priceChange}% in the last hour."
    );
}
```

### System Notifications
```php
// For maintenance or system events
NotificationService::createInfo(
    $user,
    'System Update',
    'New features have been added to your dashboard.'
);
```

## Troubleshooting

### Notifications Not Appearing
1. Check if the user has notifications in the database
2. Verify the Livewire component is properly registered
3. Check browser console for JavaScript errors

### Badge Not Updating
1. Ensure the `unreadNotificationsCount()` method is working
2. Check if notifications are being marked as read properly
3. Verify the Livewire component is refreshing

### Styling Issues
1. Ensure Tailwind CSS is properly loaded
2. Check if Alpine.js is available
3. Verify Heroicon components are accessible

## Future Enhancements

Potential improvements could include:
- Real-time notifications using WebSockets
- Email notifications
- Push notifications for mobile
- Notification preferences per user
- Notification templates
- Scheduled notifications
- Notification categories and filtering 