<?php

namespace App\Services\Transactions;

use App\Models\Asset;
use App\Models\Transaction;
use App\Enums\TransactionType;
use App\Services\TransactionsInterface;
use App\Exceptions\TransactionsException;
use Filament\Forms\Components\TextInput;
use JsonSchema\Validator;
use JsonSchema\Constraints\Constraint;
use Illuminate\Support\Facades\Log;

class TransactionsCommandJson implements TransactionsInterface
{
    protected Asset $asset;
    protected string $jsonSchema;

    public function __construct(Asset $asset)
    {
        $this->asset = $asset;

        // Get enum values dynamically
        $transferTypes = array_map(fn($case) => $case->value, TransactionType::cases());

        $this->jsonSchema = json_encode([
            '$schema' => 'http://json-schema.org/draft-07/schema#',
            'type' => 'array',
            'items' => [
                'type' => 'object',
                'required' => ['type', 'date'],
                'properties' => [
                    'type' => [
                        'type' => 'string',
                        'enum' => $transferTypes
                    ],
                    'source' => ['type' => 'string'],
                    'source_quantity' => ['type' => 'number'],
                    'destination' => ['type' => 'string'],
                    'destination_quantity' => ['type' => 'number'],
                    'date' => ['type' => 'string', 'format' => 'date'],
                    'comment' => ['type' => 'string']
                ],
                'anyOf' => [
                    [
                        'required' => ['source', 'source_quantity']
                    ],
                    [
                        'required' => ['destination', 'destination_quantity']
                    ]
                ]
            ]
        ]);
    }



    public function saveTransactions(): void
    {
        $command = $this->asset->update_data['command'] ?? '';

        if (empty($command)) {
            throw new TransactionsException(
                $this->asset,
                'Some parameters are missing required for command-based transactions',
                null,
            );
        }

        $output = [];
        $returnCode = 0;
        exec($command, $output, $returnCode);

        if ($returnCode !== 0) {
            throw new TransactionsException(
                $this->asset,
                'Command failed with return code ' . $returnCode,
                null,
                'Return code: ' . $returnCode . ' | Output: ' . implode("\n", $output)
            );
        }

        try {
            $json = json_decode(implode("\n", $output));
        } catch (\Exception $e) {
            throw new TransactionsException(
                $this->asset,
                'Invalid JSON format',
                null,
                $e->getMessage()
            );
        }

        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new TransactionsException(
                $this->asset,
                'JSON decode error: ' . json_last_error_msg(),
                null,
                'Output: ' . implode("\n", $output)
            );
        }

        // Convert to JSON-decoded object for validation
        // $jsonObject = json_decode($jsonString);

        // Validate JSON against schema
        $validator = new Validator();
        $validator->validate($json, json_decode($this->jsonSchema));

        if (!$validator->isValid()) {
            $errors = [];
            foreach ($validator->getErrors() as $error) {
                $errors[] = $error['property'] . ': ' . $error['message'];
            }

            throw new TransactionsException(
                $this->asset,
                'JSON validation failed',
                null,
                'Validation errors: ' . implode('; ', $errors)
            );
        }

        // Get duplicate checking configuration once
        $checkDuplicates = $this->asset->update_data['check_duplicates'] ?? true;
        $duplicateBehavior = $this->asset->update_data['duplicate_behavior'] ?? 'error';

        // Process and create transfers
        $transfers = [];
        $skippedDuplicates = [];

        foreach ($json as $transferData) {
            $transferData = (array) $transferData;
            try {
                $transfer = $this->createTransaction($transferData);

                // Check for duplicates if enabled
                if ($checkDuplicates && $transfer->checkDuplicate()) {
                    if ($duplicateBehavior === 'error') {
                        throw new TransactionsException(
                            $this->asset,
                            'Duplicate transaction detected',
                            null,
                            'A transaction with the same source, destination, quantities, and date already exists'
                        );
                    } else {
                        // Skip this transfer and continue with others
                        $skippedDuplicates[] = $transferData;
                        continue;
                    }
                }

                $transfer->save();
                $transfers[] = $transfer;
            } catch (\Exception $e) {
                throw new TransactionsException(
                    $this->asset,
                    'Failed to create transaction: ' . $e->getMessage(),
                    null,
                    'Transaction data: ' . json_encode($transferData)
                );
            }
        }

        // Recompute the asset quantity based on the new transactions
        if (!empty($transfers)) {
            $this->asset->computeQuantity();
        }
    }

    protected function createTransaction(array $data): Transaction
    {
        // Validate TransferType
        try {
            $type = TransactionType::from($data['type']);
        } catch (\ValueError $e) {
            throw new TransactionsException(
                $this->asset,
                'Invalid transaction type: ' . $data['type'],
                null,
                'Available types: ' . implode(', ', array_map(fn($case) => $case->value, TransactionType::cases()))
            );
        }

        // Resolve source asset by name if provided
        $sourceId = null;
        if (!empty($data['source'])) {
            $sourceAsset = Asset::where('name', $data['source'])
                ->where('user_id', $this->asset->user_id)
                ->first();

            if (!$sourceAsset) {
                throw new TransactionsException(
                    $this->asset,
                    'Source asset not found: ' . $data['source'],
                    null,
                    'Make sure the asset name exists and belongs to your account'
                );
            }
            $sourceId = $sourceAsset->id;
        }

        // Resolve destination asset by name if provided
        $destinationId = null;
        if (!empty($data['destination'])) {
            $destinationAsset = Asset::where('name', $data['destination'])
                ->where('user_id', $this->asset->user_id)
                ->first();

            if (!$destinationAsset) {
                throw new TransactionsException(
                    $this->asset,
                    'Destination asset not found: ' . $data['destination'],
                    null,
                    'Make sure the asset name exists and belongs to your account'
                );
            }
            $destinationId = $destinationAsset->id;
        }

        // Create transaction record
        $transfer = new Transaction();
        $transfer->type = $type;
        $transfer->source_id = $sourceId;
        $transfer->source_quantity = $data['source_quantity'] ?? null;
        $transfer->destination_id = $destinationId;
        $transfer->destination_quantity = $data['destination_quantity'] ?? null;
        $transfer->date = $data['date'];
        $transfer->comment = $data['comment'] ?? null;
        $transfer->user_id = $this->asset->user_id;

        return $transfer;
    }

    public static function getFields(): array
    {
        return [
            'command' => TextInput::make('command')
                ->label(__('Command')),
            'check_duplicates' => \Filament\Forms\Components\Toggle::make('check_duplicates')
                ->label(__('Check for duplicate transactions'))
                ->helperText(__('Enable to prevent duplicate transactions from being created'))
                ->default(true),
            'duplicate_behavior' => \Filament\Forms\Components\Select::make('duplicate_behavior')
                ->label(__('Duplicate handling behavior'))
                ->options([
                    'error' => __('Raise error and stop all transactions'),
                    'skip' => __('Skip duplicate transactions and continue with others'),
                ])
                ->default('error')
                ->visible(fn($get) => $get('check_duplicates'))
                ->helperText(__('Choose how to handle duplicate transactions')),
        ];
    }
}
