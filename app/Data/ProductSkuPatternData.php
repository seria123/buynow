<?php

namespace App\Data;

class ProductSkuPatternData
{
    public function __construct(
        public string $category_id,
        public string $prefix,
        public ?string $separator = '-'
    ) {}

    public static function from(array $data): self
    {
        return new self(
            $data['category_id'],
            $data['prefix'],
            $data['separator'] ?? '-'
        );
    }

    public function toArray(): array
    {
        return [
            'category_id' => $this->category_id,
            'prefix' => $this->prefix,
            'separator' => $this->separator ?? '-',
        ];
    }
}
