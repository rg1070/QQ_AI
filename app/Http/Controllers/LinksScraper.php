<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use PHPHtmlParser\Dom;
use Illuminate\Support\Facades\Http;

class LinksScraper extends Controller
{
    public function index()
    {

        if (empty($_GET['url'])) {
            return  response()->json([
                "links" => [],
                "msg" => "Failed",
                "status" => "failed"
            ], 200);
        }

        $url = $_GET['url'];

        $response = Http::withHeaders([
            'User-Agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/91.0.4472.124 Safari/537.36',
        ])->get($url);

        if ($response->failed()) {

            return  response()->json([
                "links" => [],
                "msg" => "Error in scraping web page. Error Code : " . $response->status(),
                "status" => "failed"
            ], 500);
        }

        $html = $response->body();

        $dom = new Dom;
        $dom->loadStr($html);
        $allAs = $dom->find('a');

        $links = [];
        $baseUrl = $this->getBaseUrl($url);

        if($baseUrl === false){
            return  response()->json([
                "links" => [],
                "msg" => "Invalid URL",
                "status" => "failed"
            ], 400);
        }

        foreach ($allAs as $a) {


            $href = $a->getAttribute('href');


            if (!empty($href) && !str_contains($href, '#') && !str_contains($href, 'mailto:')) {
                // Resolve relative URLs to absolute URLs
                $absoluteUrl = $this->urljoin($baseUrl, $href);

                //if url is already ignore.
                if (in_array($absoluteUrl, $links)) {
                    continue;
                }

                if (strlen($absoluteUrl) > 90) {
                    continue;
                }

                $links[] = $absoluteUrl;
            }

        }


        // $dash = $this->scrapeWebsites($links);

        return  response()->json([
            "links" => $links,
            "msg" => "Success",
            "status" => "success"
        ], 200);
    }

    // Function to get the base URL
    private function getBaseUrl($url)
    {
        $urlParts = parse_url($url);
    
        // Check if 'scheme' and 'host' are set in the array
        if (isset($urlParts['scheme'], $urlParts['host'])) {
            return $urlParts['scheme'] . '://' . $urlParts['host'];
        } else {
            // Handle the case where 'scheme' or 'host' is not present
            return false; // or any other appropriate response
        }
    }

    function urljoin($base, $url)
    {
        // Parse the base URL
        $parsedBase = parse_url($base);

        // Parse the relative URL
        $parsedUrl = parse_url($url);

        // If the URL is absolute, return it directly
        if (isset($parsedUrl['scheme'])) {
            return $url;
        }

        // Combine the base path with the relative path
        $path = rtrim(isset($parsedBase['path']) ? $parsedBase['path'] : '', '/') . '/';
        $path .= ltrim(isset($parsedUrl['path']) ? $parsedUrl['path'] : '', '/');

        // Build the result URL
        $result = '';
        if (isset($parsedBase['scheme'])) {
            $result .= $parsedBase['scheme'] . '://';
        }
        if (isset($parsedBase['host'])) {
            $result .= $parsedBase['host'];
        }
        $result .= $path;

        // Add query string and fragment if present
        if (isset($parsedUrl['query'])) {
            $result .= '?' . $parsedUrl['query'];
        }
        if (isset($parsedUrl['fragment'])) {
            $result .= '#' . $parsedUrl['fragment'];
        }

        return $result;
    }

    private function scrapeWebsites($urls)
    {

        $result = "";

        foreach ($urls as $url) {
            $dom = new Dom;
            try {
                // Make an HTTP request with a timeout of 30 seconds
                $response = Http::withoutVerifying()->withHeaders([
                    'User-Agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/91.0.4472.124 Safari/537.36',
                ])->timeout(30)->get($url);

                $status = $response->status();
                info($url . " : " . $status);

                // Check if the request was successful (status code 2xx)
                if ($response->successful()) {
                    // Get the HTML content from the response
                    $html = $response->body();

                    // Use PHPHtmlParser\Dom to parse the HTML and get the body inner text
                    $dom = new Dom;
                    $dom->loadStr($html);

                    // Remove script tags
                    foreach ($dom->find('script') as $script) {
                        $script->delete();
                    }

                    // Remove style tags
                    foreach ($dom->find('style') as $style) {
                        $style->delete();
                    }

                    // Get the body inner HTML
                    $bodyInnerHtml = $dom->find('body', 0)->innerHtml();

                    // Strip HTML tags to get only text content
                    $bodyInnerText = strip_tags($bodyInnerHtml);

                    info($bodyInnerText);
                    // Store the result
                    $result .= $bodyInnerText . "\n";
                } else {
                    $status = $response->status();
                    info($url . " : " . $status);
                    continue;
                }
            } catch (\Exception $e) {
                $status = $e->getMessage();
                info($url . " : " . $status);
                continue;
            }
        }

        return $result;
    }
}
