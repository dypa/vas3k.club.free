<?php

use GuzzleHttp\Client;

require_once './vendor/autoload.php';

function getSitemap(): string
{
    $client = new Client();
    $request = $client->get('https://vas3k.club/sitemap.xml');

    return $request->getBody()->getContents();
}


function parseSitemap(string $content): array
{
    $xmlNode = simplexml_load_string($content);
    $urls = [];
    foreach ($xmlNode->children() as $node) {
        $loc = (string)$node->loc;
        $lastmod = (string)$node->lastmod;
        if (!preg_match('/intro|weekly_digest/', $loc)) {
            $urls[$lastmod][] = $loc;
        }
    }

    return $urls;
}


function render(array $urls): string
{
    ksort($urls);
    $urls = array_reverse($urls);

    $body = '';
    foreach ($urls as $key => $array) {
        $body .= "<h3>$key</h3>";
        foreach ($array as $url) {
            $body .= "<a href=\"$url\">$url</a>";
        }
    }

    return $body;
}

$content = getSitemap();
$urls = parseSitemap($content);
$body = render($urls);

file_put_contents('sitemap.html', "
<html>
<head>
<style>
    a { 
        display: block; 
        color: black;
        margin: 5px;
    }
    a:visited { color: white; } 
</style>
</head>
<body>
    $body
</body>
</html>
");