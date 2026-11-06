<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateCustomerOrders extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'id' => ['type' => 'BIGINT', 'unsigned' => true, 'auto_increment' => true],
            'mobile' => ['type' => 'VARCHAR', 'constraint' => 16],
            'first_name' => ['type' => 'VARCHAR', 'constraint' => 100, 'null' => true],
            'last_name' => ['type' => 'VARCHAR', 'constraint' => 100, 'null' => true],
            'status' => ['type' => 'VARCHAR', 'constraint' => 20, 'default' => 'active'],
            'created_at' => ['type' => 'DATETIME', 'null' => true],
            'updated_at' => ['type' => 'DATETIME', 'null' => true],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addUniqueKey('mobile');
        $this->forge->createTable('customers', true, ['ENGINE' => 'InnoDB']);

        $this->forge->addField([
            'id' => ['type' => 'BIGINT', 'unsigned' => true, 'auto_increment' => true],
            'customer_id' => ['type' => 'BIGINT', 'unsigned' => true],
            'order_number' => ['type' => 'VARCHAR', 'constraint' => 40],
            'idempotency_key' => ['type' => 'VARCHAR', 'constraint' => 64],
            'receiver_name' => ['type' => 'VARCHAR', 'constraint' => 160],
            'receiver_mobile' => ['type' => 'VARCHAR', 'constraint' => 16],
            'province' => ['type' => 'VARCHAR', 'constraint' => 100],
            'city' => ['type' => 'VARCHAR', 'constraint' => 100],
            'address' => ['type' => 'TEXT'],
            'postal_code' => ['type' => 'VARCHAR', 'constraint' => 10],
            'shipping_method' => ['type' => 'VARCHAR', 'constraint' => 30, 'default' => 'pending'],
            'subtotal_toman' => ['type' => 'BIGINT', 'unsigned' => true],
            'shipping_toman' => ['type' => 'BIGINT', 'unsigned' => true, 'default' => 0],
            'total_toman' => ['type' => 'BIGINT', 'unsigned' => true],
            'payment_status' => ['type' => 'VARCHAR', 'constraint' => 30, 'default' => 'unpaid'],
            'order_status' => ['type' => 'VARCHAR', 'constraint' => 30, 'default' => 'pending_payment'],
            'created_at' => ['type' => 'DATETIME'],
            'updated_at' => ['type' => 'DATETIME', 'null' => true],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addUniqueKey('order_number');
        $this->forge->addUniqueKey(['customer_id', 'idempotency_key']);
        $this->forge->addKey(['customer_id', 'created_at']);
        $this->forge->addForeignKey('customer_id', 'customers', 'id', 'CASCADE', 'RESTRICT');
        $this->forge->createTable('orders', true, ['ENGINE' => 'InnoDB']);

        $this->forge->addField([
            'id' => ['type' => 'BIGINT', 'unsigned' => true, 'auto_increment' => true],
            'order_id' => ['type' => 'BIGINT', 'unsigned' => true],
            'product_id' => ['type' => 'BIGINT', 'unsigned' => true],
            'variant_id' => ['type' => 'BIGINT', 'unsigned' => true],
            'product_name' => ['type' => 'VARCHAR', 'constraint' => 220],
            'sku' => ['type' => 'VARCHAR', 'constraint' => 80],
            'size_label' => ['type' => 'VARCHAR', 'constraint' => 60, 'null' => true],
            'color_name' => ['type' => 'VARCHAR', 'constraint' => 80, 'null' => true],
            'quantity' => ['type' => 'INT', 'unsigned' => true],
            'unit_price_toman' => ['type' => 'BIGINT', 'unsigned' => true],
            'subtotal_toman' => ['type' => 'BIGINT', 'unsigned' => true],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addKey('order_id');
        $this->forge->addForeignKey('order_id', 'orders', 'id', 'CASCADE', 'CASCADE');
        $this->forge->createTable('order_items', true, ['ENGINE' => 'InnoDB']);

        $this->forge->addField([
            'id' => ['type' => 'BIGINT', 'unsigned' => true, 'auto_increment' => true],
            'order_id' => ['type' => 'BIGINT', 'unsigned' => true],
            'provider' => ['type' => 'VARCHAR', 'constraint' => 30],
            'authority' => ['type' => 'VARCHAR', 'constraint' => 120, 'null' => true],
            'ref_id' => ['type' => 'VARCHAR', 'constraint' => 120, 'null' => true],
            'amount_toman' => ['type' => 'BIGINT', 'unsigned' => true],
            'status' => ['type' => 'VARCHAR', 'constraint' => 30, 'default' => 'initiated'],
            'created_at' => ['type' => 'DATETIME'],
            'verified_at' => ['type' => 'DATETIME', 'null' => true],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addKey('order_id');
        $this->forge->addUniqueKey('authority');
        $this->forge->addUniqueKey(['provider', 'ref_id']);
        $this->forge->addForeignKey('order_id', 'orders', 'id', 'CASCADE', 'RESTRICT');
        $this->forge->createTable('payment_transactions', true, ['ENGINE' => 'InnoDB']);
    }

    public function down()
    {
        $this->forge->dropTable('payment_transactions', true);
        $this->forge->dropTable('order_items', true);
        $this->forge->dropTable('orders', true);
        $this->forge->dropTable('customers', true);
    }
}
