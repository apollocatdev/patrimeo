<?php

namespace App\Data\Schedules;

use App\Enums\ScheduleAction;

class Action
{
    public ScheduleAction $action;
    public string $message;

    public function __construct(ScheduleAction $action, ?string $message = null)
    {
        $this->action = $action;
        $this->message = $message ?? __('It\'s time to update');
    }

    public function toArray(): array
    {
        return [
            'action' => $this->action->value,
            'message' => $this->message,
        ];
    }

    public static function fromArray(array $data): self
    {
        return new self(
            ScheduleAction::from($data['action']),
            $data['message'] ?? null
        );
    }
}

// Alias to avoid conflicts with Filament's Action class
// class_alias(\App\Data\Schedules\Action::class, \App\Data\Schedules\ScheduleAction::class);
