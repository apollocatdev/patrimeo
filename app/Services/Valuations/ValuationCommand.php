<?php

namespace App\Services\Valuations;

use App\Models\Valuation;
use App\Exceptions\ValuationException;
use App\Helpers\Currency;
use Illuminate\Support\Facades\Http;
use Filament\Forms\Components\TextInput;
use Symfony\Component\DomCrawler\Crawler;
use App\Services\ValuationInterface;

class ValuationCommand implements ValuationInterface
{
    protected Valuation $valuation;

    public function __construct(Valuation $valuation)
    {
        $this->valuation = $valuation;
    }

    public function getQuote(): float
    {
        try {
            $command = $this->valuation->update_data['command'] ?? '';

            if (empty($command)) {
                throw new ValuationException(
                    $this->valuation,
                    'Command is required for command-based valuation',
                    null,
                    'Missing field: command'
                );
            }

            $output = [];
            $returnCode = 0;
            \exec($command, $output, $returnCode);

            if ($returnCode !== 0) {
                throw new ValuationException(
                    $this->valuation,
                    'Command failed with return code ' . $returnCode,
                    null,
                    'Return code: ' . $returnCode . ' | Output: ' . implode("\n", $output)
                );
            }

            $result = implode("\n", $output);
            return Currency::sanitizeToFloat($result);
        } catch (ValuationException $e) {
            throw $e;
        } catch (\Exception $e) {
            throw new ValuationException(
                $this->valuation,
                'Unexpected error in command-based valuation: ' . $e->getMessage(),
                null,
                $e->getMessage()
            );
        }
    }

    public static function getFields(): array
    {
        return [
            'command' => TextInput::make('command')
                ->label(__('Command')),
        ];
    }
}
