<?php

namespace App\Validation;

class ProductRules
{
    public const CREATE = [
        'name' => 'required|min_length[2]|max_length[220]',
        'slug' => 'required|alpha_dash|min_length[2]|max_length[220]',
        'category_id' => 'permit_empty|is_natural_no_zero',
        'brand_id' => 'permit_empty|is_natural_no_zero',
        'short_description' => 'permit_empty|max_length[500]',
        'price_toman' => 'required|is_natural',
        'compare_at_price_toman' => 'permit_empty|is_natural',
        'status' => 'required|in_list[draft,active,archived]',
        'is_featured' => 'permit_empty|in_list[0,1]',
    ];

    public const VARIANT = [
        'sku' => 'required|min_length[2]|max_length[80]',
        'barcode' => 'permit_empty|max_length[80]',
        'size_label' => 'permit_empty|max_length[60]',
        'color_name' => 'permit_empty|max_length[80]',
        'color_code' => 'permit_empty|regex_match[/^#[0-9A-Fa-f]{6}$/]',
        'price_toman' => 'permit_empty|is_natural',
        'compare_at_price_toman' => 'permit_empty|is_natural',
        'is_active' => 'permit_empty|in_list[0,1]',
    ];

    public const CATEGORY = [
        'name' => 'required|min_length[2]|max_length[160]',
        'slug' => 'required|alpha_dash|min_length[2]|max_length[190]',
        'parent_id' => 'permit_empty|is_natural_no_zero',
        'status' => 'required|in_list[active,inactive]',
    ];

    public const BRAND = [
        'name' => 'required|min_length[2]|max_length[160]',
        'slug' => 'required|alpha_dash|min_length[2]|max_length[190]',
        'is_active' => 'permit_empty|in_list[0,1]',
    ];
}