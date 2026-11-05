<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateCatalogTables extends Migration
{
    private array $tables = [
        'inventory_movements',
        'inventory_items',
        'product_variants',
        'product_media',
        'products',
        'brands',
        'categories',
    ];

    public function up()
    {
        $this->forge->addField([
            'id' => ['type' => 'BIGINT', 'unsigned' => true, 'auto_increment' => true],
            'parent_id' => ['type' => 'BIGINT', 'unsigned' => true, 'null' => true],
            'name' => ['type' => 'VARCHAR', 'constraint' => 160],
            'slug' => ['type' => 'VARCHAR', 'constraint' => 190],
            'description' => ['type' => 'TEXT', 'null' => true],
            'image_path' => ['type' => 'VARCHAR', 'constraint' => 255, 'null' => true],
            'sort_order' => ['type' => 'INT', 'constraint' => 10, 'unsigned' => true, 'default' => 0],
            'status' => ['type' => 'VARCHAR', 'constraint' => 20, 'default' => 'active'],
            'meta_title' => ['type' => 'VARCHAR', 'constraint' => 255, 'null' => true],
            'meta_description' => ['type' => 'VARCHAR', 'constraint' => 320, 'null' => true],
            'created_at' => ['type' => 'DATETIME', 'null' => true],
            'updated_at' => ['type' => 'DATETIME', 'null' => true],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addUniqueKey('slug');
        $this->forge->addKey('parent_id');
        $this->forge->addKey(['status', 'sort_order']);
        $this->forge->addForeignKey('parent_id', 'categories', 'id', 'CASCADE', 'SET NULL', 'fk_categories_parent');
        $this->forge->createTable('categories', true, [
            'ENGINE' => 'InnoDB',
            'CHARSET' => 'utf8mb4',
            'COLLATE' => 'utf8mb4_unicode_ci',
        ]);

        $this->forge->addField([
            'id' => ['type' => 'BIGINT', 'unsigned' => true, 'auto_increment' => true],
            'name' => ['type' => 'VARCHAR', 'constraint' => 160],
            'slug' => ['type' => 'VARCHAR', 'constraint' => 190],
            'description' => ['type' => 'TEXT', 'null' => true],
            'logo_path' => ['type' => 'VARCHAR', 'constraint' => 255, 'null' => true],
            'is_active' => ['type' => 'TINYINT', 'constraint' => 1, 'unsigned' => true, 'default' => 1],
            'created_at' => ['type' => 'DATETIME', 'null' => true],
            'updated_at' => ['type' => 'DATETIME', 'null' => true],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addUniqueKey('slug');
        $this->forge->addKey('is_active');
        $this->forge->createTable('brands', true, [
            'ENGINE' => 'InnoDB',
            'CHARSET' => 'utf8mb4',
            'COLLATE' => 'utf8mb4_unicode_ci',
        ]);

        $this->forge->addField([
            'id' => ['type' => 'BIGINT', 'unsigned' => true, 'auto_increment' => true],
            'category_id' => ['type' => 'BIGINT', 'unsigned' => true, 'null' => true],
            'brand_id' => ['type' => 'BIGINT', 'unsigned' => true, 'null' => true],
            'name' => ['type' => 'VARCHAR', 'constraint' => 220],
            'slug' => ['type' => 'VARCHAR', 'constraint' => 220],
            'short_description' => ['type' => 'VARCHAR', 'constraint' => 500, 'null' => true],
            'description' => ['type' => 'LONGTEXT', 'null' => true],
            'price_toman' => ['type' => 'BIGINT', 'unsigned' => true, 'default' => 0],
            'compare_at_price_toman' => ['type' => 'BIGINT', 'unsigned' => true, 'null' => true],
            'status' => ['type' => 'VARCHAR', 'constraint' => 20, 'default' => 'draft'],
            'is_featured' => ['type' => 'TINYINT', 'constraint' => 1, 'unsigned' => true, 'default' => 0],
            'meta_title' => ['type' => 'VARCHAR', 'constraint' => 255, 'null' => true],
            'meta_description' => ['type' => 'VARCHAR', 'constraint' => 320, 'null' => true],
            'published_at' => ['type' => 'DATETIME', 'null' => true],
            'created_at' => ['type' => 'DATETIME', 'null' => true],
            'updated_at' => ['type' => 'DATETIME', 'null' => true],
            'deleted_at' => ['type' => 'DATETIME', 'null' => true],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addUniqueKey('slug');
        $this->forge->addKey(['category_id', 'status']);
        $this->forge->addKey(['brand_id', 'status']);
        $this->forge->addKey(['status', 'published_at']);
        $this->forge->addKey('is_featured');
        $this->forge->addForeignKey('category_id', 'categories', 'id', 'CASCADE', 'SET NULL', 'fk_products_category');
        $this->forge->addForeignKey('brand_id', 'brands', 'id', 'CASCADE', 'SET NULL', 'fk_products_brand');
        $this->forge->createTable('products', true, [
            'ENGINE' => 'InnoDB',
            'CHARSET' => 'utf8mb4',
            'COLLATE' => 'utf8mb4_unicode_ci',
        ]);

        $this->forge->addField([
            'id' => ['type' => 'BIGINT', 'unsigned' => true, 'auto_increment' => true],
            'product_id' => ['type' => 'BIGINT', 'unsigned' => true],
            'file_path' => ['type' => 'VARCHAR', 'constraint' => 255],
            'alt_text' => ['type' => 'VARCHAR', 'constraint' => 255, 'null' => true],
            'media_type' => ['type' => 'VARCHAR', 'constraint' => 20, 'default' => 'image'],
            'sort_order' => ['type' => 'INT', 'constraint' => 10, 'unsigned' => true, 'default' => 0],
            'is_primary' => ['type' => 'TINYINT', 'constraint' => 1, 'unsigned' => true, 'default' => 0],
            'created_at' => ['type' => 'DATETIME', 'null' => true],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addKey(['product_id', 'sort_order']);
        $this->forge->addKey(['product_id', 'is_primary']);
        $this->forge->addForeignKey('product_id', 'products', 'id', 'CASCADE', 'CASCADE', 'fk_product_media_product');
        $this->forge->createTable('product_media', true, [
            'ENGINE' => 'InnoDB',
            'CHARSET' => 'utf8mb4',
            'COLLATE' => 'utf8mb4_unicode_ci',
        ]);

        $this->forge->addField([
            'id' => ['type' => 'BIGINT', 'unsigned' => true, 'auto_increment' => true],
            'product_id' => ['type' => 'BIGINT', 'unsigned' => true],
            'sku' => ['type' => 'VARCHAR', 'constraint' => 80],
            'barcode' => ['type' => 'VARCHAR', 'constraint' => 80, 'null' => true],
            'size_label' => ['type' => 'VARCHAR', 'constraint' => 60, 'null' => true],
            'color_name' => ['type' => 'VARCHAR', 'constraint' => 80, 'null' => true],
            'color_code' => ['type' => 'VARCHAR', 'constraint' => 16, 'null' => true],
            'price_toman' => ['type' => 'BIGINT', 'unsigned' => true, 'null' => true],
            'compare_at_price_toman' => ['type' => 'BIGINT', 'unsigned' => true, 'null' => true],
            'is_active' => ['type' => 'TINYINT', 'constraint' => 1, 'unsigned' => true, 'default' => 1],
            'sort_order' => ['type' => 'INT', 'constraint' => 10, 'unsigned' => true, 'default' => 0],
            'created_at' => ['type' => 'DATETIME', 'null' => true],
            'updated_at' => ['type' => 'DATETIME', 'null' => true],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addUniqueKey('sku');
        $this->forge->addKey('product_id');
        $this->forge->addKey(['product_id', 'is_active', 'sort_order']);
        $this->forge->addForeignKey('product_id', 'products', 'id', 'CASCADE', 'CASCADE', 'fk_product_variants_product');
        $this->forge->createTable('product_variants', true, [
            'ENGINE' => 'InnoDB',
            'CHARSET' => 'utf8mb4',
            'COLLATE' => 'utf8mb4_unicode_ci',
        ]);

        $this->forge->addField([
            'id' => ['type' => 'BIGINT', 'unsigned' => true, 'auto_increment' => true],
            'variant_id' => ['type' => 'BIGINT', 'unsigned' => true],
            'quantity' => ['type' => 'INT', 'constraint' => 10, 'unsigned' => true, 'default' => 0],
            'low_stock_threshold' => ['type' => 'INT', 'constraint' => 10, 'unsigned' => true, 'default' => 2],
            'updated_at' => ['type' => 'DATETIME', 'null' => true],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addUniqueKey('variant_id');
        $this->forge->addForeignKey('variant_id', 'product_variants', 'id', 'CASCADE', 'CASCADE', 'fk_inventory_items_variant');
        $this->forge->createTable('inventory_items', true, [
            'ENGINE' => 'InnoDB',
            'CHARSET' => 'utf8mb4',
            'COLLATE' => 'utf8mb4_unicode_ci',
        ]);

        $this->forge->addField([
            'id' => ['type' => 'BIGINT', 'unsigned' => true, 'auto_increment' => true],
            'variant_id' => ['type' => 'BIGINT', 'unsigned' => true],
            'quantity_delta' => ['type' => 'INT', 'constraint' => 10],
            'movement_type' => ['type' => 'VARCHAR', 'constraint' => 30],
            'reference_type' => ['type' => 'VARCHAR', 'constraint' => 50, 'null' => true],
            'reference_id' => ['type' => 'BIGINT', 'unsigned' => true, 'null' => true],
            'note' => ['type' => 'VARCHAR', 'constraint' => 500, 'null' => true],
            'created_at' => ['type' => 'DATETIME', 'null' => true],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addKey(['variant_id', 'created_at']);
        $this->forge->addKey(['reference_type', 'reference_id']);
        $this->forge->addForeignKey('variant_id', 'product_variants', 'id', 'CASCADE', 'RESTRICT', 'fk_inventory_movements_variant');
        $this->forge->createTable('inventory_movements', true, [
            'ENGINE' => 'InnoDB',
            'CHARSET' => 'utf8mb4',
            'COLLATE' => 'utf8mb4_unicode_ci',
        ]);
    }

    public function down()
    {
        foreach ($this->tables as $table) {
            $this->forge->dropTable($table, true);
        }
    }
}
