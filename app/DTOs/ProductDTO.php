<?php
namespace App\DTOs;

class ProductDTO
{
    public function __construct(
        public int $vendor_id,
        public ?int $category_id,
        public string $name,
        public string $slug,
        public ?string $description,
        public float $price,
        public ?float $discount_price,
        public ?string $image,
        public ?array $gallery,
        public bool $is_available,
        public ?int $daily_quantity,
        public int $quantity_sold_today,
        public ?int $preparation_time,
        public bool $is_featured,
        public int $sort_order,
        public string $status,
    ) {}
}
