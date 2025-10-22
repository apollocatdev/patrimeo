<?php

namespace Tests\Unit;

use Tests\TestCase;
use App\Enums\CotationUpdateMethod;
use App\Enums\TransferUpdateMethod;
use App\Enums\UpdateCotationPeriodicity;

class EnumsTest extends TestCase
{
    public function test_cotation_update_method_enum(): void
    {
        $this->assertEquals('yahoo', CotationUpdateMethod::YAHOO->value);
        $this->assertEquals('css', CotationUpdateMethod::CSS->value);
        $this->assertEquals('coingecko', CotationUpdateMethod::COINGECKO->value);
        $this->assertEquals('openai', CotationUpdateMethod::OPENAI->value);
        $this->assertEquals('command', CotationUpdateMethod::COMMAND->value);
        $this->assertEquals('fixed', CotationUpdateMethod::FIXED->value);
        $this->assertEquals('manual', CotationUpdateMethod::MANUAL->value);
    }

    public function test_cotation_update_method_labels(): void
    {
        $this->assertEquals('Yahoo Finance', CotationUpdateMethod::YAHOO->getLabel());
        $this->assertEquals('CSS selector', CotationUpdateMethod::CSS->getLabel());
        $this->assertEquals('Coingecko', CotationUpdateMethod::COINGECKO->getLabel());
        $this->assertEquals('OpenAI ChatGPT', CotationUpdateMethod::OPENAI->getLabel());
        $this->assertEquals('Command', CotationUpdateMethod::COMMAND->getLabel());
        $this->assertEquals('Fixed', CotationUpdateMethod::FIXED->getLabel());
        $this->assertEquals('Manual', CotationUpdateMethod::MANUAL->getLabel());
    }

    public function test_cotation_update_method_service_classes(): void
    {
        $this->assertEquals(\App\Services\Cotations\CotationYahoo::class, CotationUpdateMethod::YAHOO->getServiceClass());
        $this->assertEquals(\App\Services\Cotations\CotationCss::class, CotationUpdateMethod::CSS->getServiceClass());
        $this->assertEquals(\App\Services\Cotations\CotationCoingecko::class, CotationUpdateMethod::COINGECKO->getServiceClass());
        $this->assertEquals(\App\Services\Cotations\CotationOpenAI::class, CotationUpdateMethod::OPENAI->getServiceClass());
        $this->assertEquals(\App\Services\Cotations\CotationCommand::class, CotationUpdateMethod::COMMAND->getServiceClass());
        $this->assertNull(CotationUpdateMethod::FIXED->getServiceClass());
        $this->assertNull(CotationUpdateMethod::MANUAL->getServiceClass());
    }

    public function test_cotation_update_method_dropdown(): void
    {
        $dropdown = CotationUpdateMethod::dropdown();

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
        $this->assertEquals('manual', TransferUpdateMethod::MANUAL->value);
        $this->assertEquals('fixed', TransferUpdateMethod::FIXED->value);
        $this->assertEquals('command_json', TransferUpdateMethod::COMMAND_JSON->value);
        $this->assertEquals('command_simple_balance', TransferUpdateMethod::COMMAND_SIMPLE_BALANCE->value);
        $this->assertEquals('woob', TransferUpdateMethod::WOOB->value);
        $this->assertEquals('finary', TransferUpdateMethod::FINARY->value);
    }

    public function test_transfer_update_method_labels(): void
    {
        $this->assertEquals('Manual', TransferUpdateMethod::MANUAL->getLabel());
        $this->assertEquals('Fixed', TransferUpdateMethod::FIXED->getLabel());
        $this->assertEquals('Command JSON', TransferUpdateMethod::COMMAND_JSON->getLabel());
        $this->assertEquals('Command Simple Balance', TransferUpdateMethod::COMMAND_SIMPLE_BALANCE->getLabel());
        $this->assertEquals('Woob (Weboob)', TransferUpdateMethod::WOOB->getLabel());
        $this->assertEquals('Finary', TransferUpdateMethod::FINARY->getLabel());
    }

    public function test_transfer_update_method_service_classes(): void
    {
        $this->assertNull(TransferUpdateMethod::MANUAL->getServiceClass());
        $this->assertNull(TransferUpdateMethod::FIXED->getServiceClass());
        $this->assertEquals(\App\Services\Transfers\TransfersCommandJson::class, TransferUpdateMethod::COMMAND_JSON->getServiceClass());
        $this->assertEquals(\App\Services\Transfers\TransfersCommandSimpleBalance::class, TransferUpdateMethod::COMMAND_SIMPLE_BALANCE->getServiceClass());
        $this->assertEquals(\App\Services\Transfers\TransfersWoob::class, TransferUpdateMethod::WOOB->getServiceClass());
        $this->assertEquals(\App\Services\Transfers\TransfersFinary::class, TransferUpdateMethod::FINARY->getServiceClass());
    }

    public function test_transfer_update_method_dropdown(): void
    {
        $dropdown = TransferUpdateMethod::dropdown();

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
        $this->assertEquals('hour', UpdateCotationPeriodicity::HOUR->value);
        $this->assertEquals('day', UpdateCotationPeriodicity::DAY->value);
        $this->assertEquals('week', UpdateCotationPeriodicity::WEEK->value);
    }

    public function test_update_cotation_periodicity_labels(): void
    {
        $this->assertEquals('Hour(s)', UpdateCotationPeriodicity::HOUR->getLabel());
        $this->assertEquals('Day(s)', UpdateCotationPeriodicity::DAY->getLabel());
        $this->assertEquals('Week(s)', UpdateCotationPeriodicity::WEEK->getLabel());
    }
}
