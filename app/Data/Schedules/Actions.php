<?php

namespace App\Data\Schedules;

use Illuminate\Support\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Contracts\Database\Eloquent\Castable;
use Illuminate\Contracts\Database\Eloquent\CastsAttributes;

class Actions implements Castable
{
    public function __construct(public Collection $actions) {}

    public static function castUsing(array $arguments): CastsAttributes
    {
        return new class implements CastsAttributes
        {
            public function get(Model $model, string $key, $value, array $attributes): Actions
            {
                $result = collect();

                // Handle both JSON strings and arrays
                if (is_string($value)) {
                    $actions = json_decode($value, true) ?? [];
                } elseif (is_array($value)) {
                    $actions = $value;
                } else {
                    $actions = [];
                }

                foreach ($actions as $action) {
                    $result->push(Action::fromArray($action));
                }

                return new Actions($result);
            }

            public function set(Model $model, string $key, $value, array $attributes): array
            {
                if ($value instanceof Actions) {
                    $actions = $value->actions->map(function ($action) {
                        return $action->toArray();
                    })->toArray();

                    return [$key => json_encode($actions)];
                } elseif (is_array($value)) {
                    // Handle direct array input (from forms)
                    return [$key => json_encode($value)];
                }

                return [$key => $value];
            }
        };
    }

    public function toArray(): array
    {
        return $this->actions->map(function ($action) {
            return $action->toArray();
        })->toArray();
    }

    public static function fromFormArray(array $data): self
    {
        $actions = collect($data)->map(function ($item) {
            return new Action(
                $item['action'],
                $item['message'] ?? null
            );
        })->filter();

        return new self($actions);
    }

    public function count(): int
    {
        return $this->actions->count();
    }
}
