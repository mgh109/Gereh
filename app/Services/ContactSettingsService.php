<?php

namespace App\Services;

use Config\Database;
use RuntimeException;
use Throwable;

final class ContactSettingsService
{
    public const DEFAULTS = [
        'phone' => '01133290207',
        'email' => 'h3ein157@gmail.com',
        'postal_code' => '4819675397',
        'address' => 'مازندران، ساری، محله صادقیه، کوچه اول صادقیه، کوچه ایثار ۱۱، پلاک ۰۰، طبقه همکف',
    ];

    public function read(): array
    {
        try {
            $db = Database::connect();
            if ($db->tableExists('contact_settings')) {
                $row = $db->table('contact_settings')->where('id', 1)->get()->getRowArray();
                if ($row) return array_intersect_key($row, self::DEFAULTS) + self::DEFAULTS;
            }
        } catch (Throwable $exception) {
            log_message('error', 'Contact settings unavailable: {type}', ['type' => get_class($exception)]);
        }
        return self::DEFAULTS;
    }

    public function update(array $data): void
    {
        $db = Database::connect();
        if (!$db->tableExists('contact_settings')) {
            throw new RuntimeException('ابتدا بروزرسانی دیتابیس اطلاعات تماس را اجرا کنید.');
        }
        $data = array_intersect_key($data, self::DEFAULTS);
        $data['updated_at'] = date('Y-m-d H:i:s');
        $query = $db->table('contact_settings');
        $ok = $query->where('id', 1)->countAllResults() > 0
            ? $db->table('contact_settings')->where('id', 1)->update($data)
            : $db->table('contact_settings')->insert(['id' => 1] + $data);
        if (!$ok) throw new RuntimeException('ذخیره اطلاعات تماس انجام نشد.');
    }
}
