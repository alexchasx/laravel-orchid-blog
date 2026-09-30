<?php

namespace App\Http\Controllers;

use Illuminate\Http\Response;

class RobotsController extends Controller
{
    /**
     * Отдача robots.txt с динамической ссылкой на sitemap.
     */
    public function __invoke(): Response
    {
        $appUrl = config('app.url');

        $content = <<<TXT
User-agent: *
Disallow: /nexus
Disallow: /dashboard
Disallow: /profile
Disallow: /login
Disallow: /register
Disallow: /test-
Disallow: /notpublic
Disallow: /unsubscribe
Disallow: /consent/revoke
Disallow: /*?search=
Disallow: /*?page=
Sitemap: {$appUrl}/sitemap.xml
TXT;

        return response($content, 200, [
            'Content-Type' => 'text/plain',
        ]);
    }
}
