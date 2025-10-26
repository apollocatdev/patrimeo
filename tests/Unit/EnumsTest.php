<?php

namespace Tests\Unit;

use Tests\TestCase;
use App\Enums\ValuationUpdateMethod;
use App\Enums\TransactionUpdateMethod;
use App\Enums\UpdateValuationPeriodicity;

class EnumsTest extends TestCase
{
    public function test_cotation_update_method_enum(): void
    {
        $this->assertEquals('yahoo', ValuationUpdateMethod::YAHOO->value);
        $this->assertEquals('css', ValuationUpdateMethod::CSS->value);
        $this->assertEquals('coingecko', ValuationUpdateMethod::COINGECKO->value);
        $this->assertEquals('openai', ValuationUpdateMethod::OPENAI->value);
        $this->assertEquals('command', ValuationUpdateMethod::COMMAND->value);
        $this->assertEquals('fixed', ValuationUpdateMethod::FIXED->value);
        $this->assertEquals('manual', ValuationUpdateMethod::MANUAL->value);
    }

    public function test_cotation_update_method_labels(): void
    {
        $this->assertEquals('Yahoo Finance', ValuationUpdateMethod::YAHOO->getLabel());
        $this->assertEquals('CSS selector', ValuationUpdateMethod::CSS->getLabel());
        $this->assertEquals('Coingecko', ValuationUpdateMethod::COINGECKO->getLabel());
        $this->assertEquals('OpenAI ChatGPT', ValuationUpdateMethod::OPENAI->getLabel());
        $this->assertEquals('Command', ValuationUpdateMethod::COMMAND->getLabel());
        $this->assertEquals('Fixed', ValuationUpdateMethod::FIXED->getLabel());
        $this->assertEquals('Manual', ValuationUpdateMethod::MANUAL->getLabel());
    }

    public function test_cotation_update_method_service_classes(): void
    {
        $this->assertEquals(\App\Services\Valuations\ValuationYahoo::class, ValuationUpdateMethod::YAHOO->getServiceClass());
        $this->assertEquals(\App\Services\Valuations\ValuationCss::class, ValuationUpdateMethod::CSS->getServiceClass());
        $this->assertEquals(\App\Services\Valuations\ValuationCoingecko::class, ValuationUpdateMethod::COINGECKO->getServiceClass());
        $this->assertEquals(\App\Services\Valuations\ValuationOpenAI::class, ValuationUpdateMethod::OPENAI->getServiceClass());
        $this->assertEquals(\App\Services\Valuations\ValuationCommand::class, ValuationUpdateMethod::COMMAND->getServiceClass());
        $this->assertNull(ValuationUpdateMethod::FIXED->getServiceClass());
        $this->assertNull(ValuationUpdateMethod::MANUAL->getServiceClass());
    }

    public function test_cotation_update_method_dropdown(): void
    {
        $dropdown = ValuationUpdateMethod::dropdown();

        $this->assertIsArray($dropdown);
        $this->assertGreaterThan(0, count($dropdown));

        foreach ($dropdown as $item) {
            $this->assertArrayHasKey('label', $item);
            $this->assertArrayHasKey('value', $item);
            $this->assertIsString($item['label']);
            $this->assertIsString($item['value']);
        }
    }

    public function test_transfer_update_method_enum(): void
    {
        $this->assertEquals('manual', TransactionUpdateMethod::MANUAL->value);
        $this->assertEquals('fixed', TransactionUpdateMethod::FIXED->value);
        $this->assertEquals('command_json', TransactionUpdateMethod::COMMAND_JSON->value);
        $this->assertEquals('command_simple_balance', TransactionUpdateMethod::COMMAND_SIMPLE_BALANCE->value);
        $this->assertEquals('woob', TransactionUpdateMethod::WOOB->value);
        $this->assertEquals('finary', TransactionUpdateMethod::FINARY->value);
    }

    public function test_transfer_update_method_labels(): void
    {
        $this->assertEquals('Manual', TransactionUpdateMethod::MANUAL->getLabel());
        $this->assertEquals('Fixed', TransactionUpdateMethod::FIXED->getLabel());
        $this->assertEquals('Command JSON', TransactionUpdateMethod::COMMAND_JSON->getLabel());
        $this->assertEquals('Command Simple Balance', TransactionUpdateMethod::COMMAND_SIMPLE_BALANCE->getLabel());
        $this->assertEquals('Woob (Weboob)', TransactionUpdateMethod::WOOB->getLabel());
        $this->assertEquals('Finary', TransactionUpdateMethod::FINARY->getLabel());
    }

    public function test_transfer_update_method_service_classes(): void
    {
        $this->assertNull(TransactionUpdateMethod::MANUAL->getServiceClass());
        $this->assertNull(TransactionUpdateMethod::FIXED->getServiceClass());
        $this->assertEquals(\App\Services\Transactions\TransactionsCommandJson::class, TransactionUpdateMethod::COMMAND_JSON->getServiceClass());
        $this->assertEquals(\App\Services\Transactions\TransactionsCommandSimpleBalance::class, TransactionUpdateMethod::COMMAND_SIMPLE_BALANCE->getServiceClass());
        $this->assertEquals(\App\Services\Transactions\TransactionsWoob::class, TransactionUpdateMethod::WOOB->getServiceClass());
        $this->assertEquals(\App\Services\Transactions\TransactionsFinary::class, TransactionUpdateMethod::FINARY->getServiceClass());
    }

    public function test_transfer_update_method_dropdown(): void
    {
        $dropdown = TransactionUpdateMethod::dropdown();

        $this->assertIsArray($dropdown);
        $this->assertGreaterThan(0, count($dropdown));

        foreach ($dropdown as $item) {
            $this->assertArrayHasKey('label', $item);
            $this->assertArrayHasKey('value', $item);
            $this->assertIsString($item['label']);
            $this->assertIsString($item['value']);
        }
    }

    public function test_update_cotation_periodicity_enum(): void
    {
        $this->assertEquals('hour', UpdateValuationPeriodicity::HOUR->value);
        $this->assertEquals('day', UpdateValuationPeriodicity::DAY->value);
        $this->assertEquals('week', UpdateValuationPeriodicity::WEEK->value);
    }

    public function test_update_cotation_periodicity_labels(): void
    {
        $this->assertEquals('Hour(s)', UpdateValuationPeriodicity::HOUR->getLabel());
        $this->assertEquals('Day(s)', UpdateValuationPeriodicity::DAY->getLabel());
        $this->assertEquals('Week(s)', UpdateValuationPeriodicity::WEEK->getLabel());
    }
}
