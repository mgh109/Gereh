<?php

namespace App\Controllers;

use Config\Database;
use Throwable;

class SeoController extends BaseController
{
    public function robots()
    {
        $body = "User-agent: *\n"
            . "Disallow: /admin\n"
            . "Disallow: /cart\n"
            . "Disallow: /checkout\n"
            . "Disallow: /api/\n"
            . "Sitemap: " . site_url('sitemap.xml') . "\n";
        return $this->response->setContentType('text/plain', 'utf-8')->setBody($body);
    }

    public function sitemap()
    {
        try {
            $db = Database::connect();
            $urls = [
                ['loc' => site_url('/'), 'lastmod' => null],
                ['loc' => site_url('shop'), 'lastmod' => null],
                ['loc' => site_url('size-guide'), 'lastmod' => null],
                ['loc' => site_url('blog'), 'lastmod' => null],
            ];
            foreach (['products' => 'product/'] as $table => $prefix) {
                // Keep under the sitemap protocol's 50,000 URL limit.
                $rows = $db->table($table)->select('slug, updated_at')
                    ->where('status', 'active')->where('deleted_at', null)
                    ->orderBy('id', 'ASC')->limit(45000)->get()->getResultArray();
                foreach ($rows as $row) {
                    $urls[] = ['loc' => site_url($prefix . rawurlencode($row['slug'])), 'lastmod' => $row['updated_at']];
                }
            }
            $articles = $db->table('articles')->select('slug, updated_at')
                ->where('status', 'published')
                ->where('published_at <=', date('Y-m-d H:i:s'))
                ->orderBy('id', 'ASC')->limit(4000)->get()->getResultArray();
            foreach ($articles as $article) {
                $urls[] = ['loc' => site_url('blog/' . rawurlencode($article['slug'])), 'lastmod' => $article['updated_at']];
            }
            $xml = '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
            $xml .= '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">';
            foreach ($urls as $entry) {
                $xml .= '<url><loc>' . htmlspecialchars($entry['loc'], ENT_XML1 | ENT_QUOTES, 'UTF-8') . '</loc>';
                if (! empty($entry['lastmod'])) {
                    $timestamp = strtotime((string) $entry['lastmod']);
                    if ($timestamp !== false) {
                        $xml .= '<lastmod>' . gmdate('Y-m-d', $timestamp) . '</lastmod>';
                    }
                }
                $xml .= '</url>';
            }
            $xml .= '</urlset>';
            return $this->response->setContentType('application/xml', 'utf-8')->setBody($xml);
        } catch (Throwable $exception) {
            log_message('error', 'Sitemap failure: {message}', ['message' => $exception->getMessage()]);
            return $this->response->setStatusCode(503)->setContentType('text/plain')->setBody('Sitemap temporarily unavailable.');
        }
    }
}
