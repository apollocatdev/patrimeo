<?php

namespace App\Console\Commands;

use App\Models\Valuation;
use App\Models\Currency;
use App\Models\User;
use App\Services\Valuations\ValuationOpenAI;
use App\Enums\ValuationUpdateMethod;
use App\Exceptions\ValuationException;
use Illuminate\Console\Command;
use Illuminate\Support\Str;

class TestValuationOpenAI extends Command
{
    protected $signature = 'test:openai {name : The name of the valuation to test}';
    protected $description = 'Test the ValuationOpenAI service with a temporary valuation';

    public function handle()
    {
        $valuationName = $this->argument('name');
        $this->info("Testing ValuationOpenAI service with valuation: {$valuationName}");

        // Get the first user
        $user = User::first();
        $this->line("Using user: {$user->name}");

        // Create or get a test currency
        $currency = Currency::firstOrCreate(['symbol' => 'EUR'], [
            'main' => false,
            'user_id' => $user->id,
        ]);

        // Create a test valuation
        $valuation = Valuation::create([
            'name' => $valuationName,
            'isin' => 'TEST' . Str::random(8),
            'currency_id' => $currency->id,
            'value' => 0,
            'value_main_currency' => 0,
            'update_method' => ValuationUpdateMethod::OPENAI,
            'update_data' => [
                'prompt' => 'Get the latest price of {valuation_name}'
            ],
            'user_id' => $user->id,
        ]);


        $this->info("\nTesting OpenAI service...");

        try {
            $openaiService = new ValuationOpenAI($valuation);
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
        } catch (ValuationException $e) {
            $this->error("✗ ValuationException: " . $e->getFullMessage());
        } catch (\Exception $e) {
            $this->error("✗ Unexpected error: " . $e->getMessage());
        }

        // Clean up - remove the test valuation
        $this->info("\nCleaning up...");
        $valuation->delete();
        $this->info("✓ Test valuation removed");

        $this->info("\nTest completed successfully!");

        return 0;
    }
}
