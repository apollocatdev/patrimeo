<?php

namespace App\Data\Schedules;

class Action
{
    public string $action;
    public string $message;

    public function __construct(string $action, ?string $message = null)
    {
        $this->action = $action;
        $this->message = $message ?? __('It\'s time to update');
    }

    public function toArray(): array
    {
        return [
            'action' => $this->action,
            'message' => $this->message,
        ];
    }

    public static function fromArray(array $data): self
    {
        return new self(
            $data['action'],
            $data['message'] ?? null
        );
    }
}

// Alias to avoid conflicts with Filament's Action class
class_alias(\App\Data\Schedules\Action::class, \App\Data\Schedules\ScheduleAction::class);
