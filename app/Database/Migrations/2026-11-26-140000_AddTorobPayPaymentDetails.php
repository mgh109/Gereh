<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

final class AddTorobPayPaymentDetails extends Migration
{
    public function up()
    {
        $this->forge->addColumn('payment_transactions', [
            'gateway_token' => ['type' => 'TEXT', 'null' => true],
            'gateway_redirect_url' => ['type' => 'TEXT', 'null' => true],
        ]);
    }

    public function down()
    {
        $this->forge->dropColumn('payment_transactions', ['gateway_token', 'gateway_redirect_url']);
    }
}
