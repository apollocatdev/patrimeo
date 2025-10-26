@component('mail::message')
# Schedule Notification

{{ $message }}

## Schedule Details
- **Name:** {{ $schedule->name }}
- **Cron Expression:** {{ $schedule->cron }}

@if($cotations->count() > 0)
## Valuations
@foreach($cotations as $cotation)
- {{ $cotation->name }} ({{ $cotation->value }} {{ $cotation->currency->symbol ?? '' }})
@endforeach
@endif

@if($assets->count() > 0)
## Assets
@foreach($assets as $asset)
- {{ $asset->name }} ({{ $asset->quantity }} units)
@endforeach
@endif

Thanks,<br>
{{ config('app.name') }}
@endcomponent