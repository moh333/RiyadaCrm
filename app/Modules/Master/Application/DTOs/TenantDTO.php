<?php

namespace App\Modules\Master\Application\DTOs;

final class TenantDTO
{
    public string $id;
    public string $name; // Assuming 'id' is domain or we have a name field
    public array $domains;
    public ?string $created_at;

    public function __construct(string $id, array $domains, ?string $created_at)
    {
        $this->id = $id;
        $this->name = ucfirst($id); // Placeholder for name logic
        $this->domains = $domains;
        $this->created_at = $created_at;
    }

    public static function fromModel($model): self
    {
        return new self(
            $model->id,
            $model->domains->pluck('domain')->toArray(),
            $model->created_at?->toFormattedDateString()
        );
    }

    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'domains' => $this->domains,
            'created_at' => $this->created_at,
        ];
    }
}
