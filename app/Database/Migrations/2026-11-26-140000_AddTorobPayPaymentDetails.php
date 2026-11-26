<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

final class AddTorobPayPaymentDetails extends Migration
{
    public function up()
    {
        foreach (['gateway_token', 'gateway_redirect_url'] as $column) {
            if (! $this->db->fieldExists($column, 'payment_transactions')) {
                $this->forge->addColumn('payment_transactions', [$column => ['type' => 'TEXT', 'null' => true]]);
            }
        }
    }

    public function down()
    {
        $this->forge->dropColumn('payment_transactions', ['gateway_token', 'gateway_redirect_url']);
    }
}
