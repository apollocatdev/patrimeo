<?php

namespace App\Console\Commands;

use App\Models\Cotation;
use App\Models\Currency;
use App\Models\User;
use App\Services\Cotations\CotationOpenAI;
use App\Enums\CotationUpdateMethod;
use App\Exceptions\CotationException;
use Illuminate\Console\Command;
use Illuminate\Support\Str;

class TestCotationOpenAI extends Command
{
    protected $signature = 'test:openai {name : The name of the cotation to test}';
    protected $description = 'Test the CotationOpenAI service with a temporary cotation';

    public function handle()
    {
        $cotationName = $this->argument('name');
        $this->info("Testing CotationOpenAI service with cotation: {$cotationName}");

        // Get the first user
        $user = User::first();
        $this->line("Using user: {$user->name}");

        // Create or get a test currency
        $currency = Currency::firstOrCreate(['symbol' => 'EUR'], [
            'main' => false,
            'user_id' => $user->id,
        ]);

        // Create a test cotation
        $cotation = Cotation::create([
            'name' => $cotationName,
            'isin' => 'TEST' . Str::random(8),
            'currency_id' => $currency->id,
            'value' => 0,
            'value_main_currency' => 0,
            'update_method' => CotationUpdateMethod::OPENAI,
            'update_data' => [
                'prompt' => 'Get the latest price of {cotation_name}'
            ],
            'user_id' => $user->id,
        ]);


        $this->info("\nTesting OpenAI service...");

        try {
            $openaiService = new CotationOpenAI($cotation);
            $quote = $openaiService->getQuote();
            $this->info("✓ Quote retrieved successfully: {$quote}");

            // Display debug information
            $debugInfo = $openaiService->getDebugInformation();
            if (!empty($debugInfo)) {
                $this->line("\nDebug Information:");
                $this->line("  Price: {$debugInfo['price']}");
                $this->line("  Currency: {$debugInfo['currency']}");
                $this->line("  Date: {$debugInfo['date']}");
                $this->line("  Raw Response: {$debugInfo['raw_response']}");
            }
        } catch (CotationException $e) {
            $this->error("✗ CotationException: " . $e->getFullMessage());
        } catch (\Exception $e) {
            $this->error("✗ Unexpected error: " . $e->getMessage());
        }

        // Clean up - remove the test cotation
        $this->info("\nCleaning up...");
        $cotation->delete();
        $this->info("✓ Test cotation removed");

        $this->info("\nTest completed successfully!");

        return 0;
    }
}
