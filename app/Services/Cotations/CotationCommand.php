<?php

namespace App\Services\Cotations;

use App\Models\Cotation;
use App\Exceptions\CotationException;
use App\Helpers\Currency;
use Illuminate\Support\Facades\Http;
use Filament\Forms\Components\TextInput;
use Symfony\Component\DomCrawler\Crawler;
use App\Services\CotationInterface;

class CotationCommand implements CotationInterface
{
    protected Cotation $cotation;

    public function __construct(Cotation $cotation)
    {
        $this->cotation = $cotation;
    }

    public function getQuote(): float
    {
        try {
            $command = $this->cotation->update_data['command'] ?? '';

            if (empty($command)) {
                throw new CotationException(
                    $this->cotation,
                    'Command is required for command-based cotation',
                    null,
                    'Missing field: command'
                );
            }

            $output = [];
            $returnCode = 0;
            exec($command, $output, $returnCode);

            if ($returnCode !== 0) {
                throw new CotationException(
                    $this->cotation,
                    'Command failed with return code ' . $returnCode,
                    null,
                    'Return code: ' . $returnCode . ' | Output: ' . implode("\n", $output)
                );
            }

            $result = implode("\n", $output);
            return Currency::sanitizeToFloat($result);
        } catch (CotationException $e) {
            throw $e;
        } catch (\Exception $e) {
            throw new CotationException(
                $this->cotation,
                'Unexpected error in command-based cotation: ' . $e->getMessage(),
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
