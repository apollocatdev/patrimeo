@php
    $colors = [
        'error' => 'red',
        'success' => 'green',
        'info' => 'blue'
    ];
    $color = $colors[$type] ?? 'blue';
@endphp

<div class="p-4 bg-{{ $color }}-50 border border-{{ $color }}-200 rounded-md">
    <div class="flex">
        <div class="flex-shrink-0">
            @if($type === 'error')
                <svg class="h-5 w-5 text-{{ $color }}-400" viewBox="0 0 20 20" fill="currentColor">
                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd" />
                </svg>
            @elseif($type === 'success')
                <svg class="h-5 w-5 text-{{ $color }}-400" viewBox="0 0 20 20" fill="currentColor">
                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd" />
                </svg>
            @else
                <svg class="h-5 w-5 text-{{ $color }}-400" viewBox="0 0 20 20" fill="currentColor">
                    <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd" />
                </svg>
            @endif
        </div>
        <div class="ml-3 flex-1">
            <h3 class="text-sm font-medium text-{{ $color }}-800">{{ $title }}</h3>
            <div class="mt-2 text-sm text-{{ $color }}-700">
                <p>{{ $message }}</p>
                
                @if(isset($details) && $type === 'error')
                    <div class="mt-3">
<div class="p-4">
    @if($hasTestResults)
    @if($testError)
    <div class="p-4 bg-red-50 border border-red-200 rounded-md">
        <div class="flex">
            <div class="flex-shrink-0">
                <svg class="h-5 w-5 text-red-400" viewBox="0 0 20 20" fill="currentColor">
                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd" />
                </svg>
            </div>
            <div class="ml-3">
                <h3 class="text-sm font-medium text-red-800">Test Failed</h3>
                <div class="mt-2 text-sm text-red-700">
                    <p>{{ $testError }}</p>
                </div>
            </div>
        </div>
    </div>
    @elseif($testResult !== null)
    <div class="p-4 bg-green-50 border border-green-200 rounded-md">
        <div class="flex">
            <div class="flex-shrink-0">
                <svg class="h-5 w-5 text-green-400" viewBox="0 0 20 20" fill="currentColor">
                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                </svg>
            </div>
            <div class="ml-3">
                <h3 class="text-sm font-medium text-green-800">Test Successful!</h3>
                <div class="mt-2 text-sm text-green-700">
                    <p>Price extracted: <strong>{{ number_format($testResult, 6) }}</strong></p>
                </div>
            </div>
        </div>
    </div>
    @endif
    @else
    <div class="p-4 bg-blue-50 border border-blue-200 rounded-md">
        <div class="flex">
            <div class="flex-shrink-0">
                <svg class="h-5 w-5 text-blue-400" viewBox="0 0 20 20" fill="currentColor">
                    <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd" />
                </svg>
            </div>
            <div class="ml-3">
                <h3 class="text-sm font-medium text-blue-800">Ready to Test</h3>
                <div class="mt-2 text-sm text-blue-700">
                    <p>Click the "Test Price Extraction" button to test your configuration.</p>
                </div>
            </div>
        </div>
    </div>
    @endif
</div>