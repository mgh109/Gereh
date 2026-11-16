<?php

namespace App\Services;

use Config\Database;
use RuntimeException;
use Throwable;

final class ContactSettingsService
{
    public const DEFAULTS = [
        'enamad_link' => '',
        'enamad_image_url' => '',
        'phone' => '01133290207',
        'email' => 'h3ein157@gmail.com',
        'postal_code' => '4819675397',
        'address' => 'مازندران، ساری، محله صادقیه، کوچه اول صادقیه، کوچه ایثار ۱۱، پلاک ۰۰، طبقه همکف',
    ];

    public static function parseEnamadCode(string $code): array
    {
        if (trim($code) === '') return ['enamad_link'=>'', 'enamad_image_url'=>''];
        $urls = [];
        foreach (['enamad_link'=>['a','href'], 'enamad_image_url'=>['img','src']] as $key=>$attributes) {
            [$tag,$attribute] = $attributes;
            $pattern = '~<'.$tag.'\\b[^>]*\\b'.$attribute.'\\s*=\\s*([\'\"]) (.*?)\\1~isx';
            if (!preg_match($pattern, $code, $matches)) {
                throw new \InvalidArgumentException('کد کامل لینک و تصویر نماد را از پنل اینماد وارد کنید.');
            }
            $url = html_entity_decode(trim($matches[2]), ENT_QUOTES | ENT_HTML5, 'UTF-8');
            $parts = parse_url($url);
            if (strlen($url)>2000 || !filter_var($url, FILTER_VALIDATE_URL) || !$parts
                || strtolower($parts['scheme']??'')!=='https'
                || strtolower($parts['host']??'')!=='trustseal.enamad.ir'
                || isset($parts['user']) || isset($parts['pass']) || isset($parts['port'])) {
                throw new \InvalidArgumentException('لینک و تصویر نماد باید از آدرس امن trustseal.enamad.ir باشند.');
            }
            $urls[$key] = $url;
        }
        return $urls;
    }

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
