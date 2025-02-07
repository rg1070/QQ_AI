<?php

namespace App\Library;

use App\Models\History;
use Illuminate\Support\Facades\Http;

use Smalot\PdfParser\Parser;
use Smalot\PdfParser\Config;
use Illuminate\Support\Facades\Storage;

use App\Models\Chunk;
use Illuminate\Support\Str;
use App\Library\PineConeClient;
use App\Models\Setting;
use PHPHtmlParser\Dom;

class Requester
{

    private $charLimit = 1000;
   
    private $id, $host;
  
    private $openaiKey = "";

    public function run()
    {
        info('cron running');

        $this->charLimit = env("CHUNK_CHAR_LIMIT", 1000);
        $settings = Setting::where('id', 1)->select('openaiKey', 'host')->first();
        
        $this->host = $settings->host;

        $this->openaiKey = $settings->openaiKey;

        $history = History::where(['sent' => 0, 'status' => 'pending'])->select('id', 'extension', 'path', 'name')->get();

        foreach ($history as $h) {

            $ext = $h->extension;
            $this->id = $h->id;

            //pdf
            if ($ext === "pdf") {

                try {

                    $config = new Config();
                    $config->setFontSpaceLimit(-60);
                    $config->setHorizontalOffset('');
                    $config->setRetainImageContent(false);


                    $parser = new Parser([], $config);

                    $pdfPath = Storage::path("private/" . $h->path);

                    $pdf = $parser->parseFile($pdfPath);
                } catch (\Exception $err) {

                    //info($err);

                    return response()->json([
                        "msg" => "We are unable to process this pdf file. Please use another.",
                        "status" => "failed"
                    ], 400);
                }

                $text = [];
                $text['text'] = "";
                $text['source'] = [];
                $text['pages'] = [];

                //get file name if its greator than 200 trim it.
                $fileName = $this->trimString($h->name, 200);


                //read pdf pages and get its text
                foreach ($pdf->getPages() as $key => $pg) {

                    $pgNo = $key + 1;
                    $pgText  = $pg->getText();

                    //if pg text is empty or after removing whitespaces its empty
                    if (empty($pgText)) {
                        continue;
                    }

                    $text['text'] .=  $pgText;
                    $text['source'][] = "$fileName | Page # $pgNo";
                    $text['pages'][] = $pgText;
                }

                //make chunks
                $data = $this->makePDFChunks($text);

                $chunks = $data['main'];
                //info($chunks);
                //$chunks = json_encode($chunks);
            } elseif ($ext === "txt") {

                $content = Storage::disk('private')->get($h->path);


                $chunks = $this->makeTxtChunks($content);
                // $chunks = json_encode($chunks);

                //info($chunks);
            } 
            elseif ($ext === "csv") {

                $content = Storage::disk('private')->get($h->path);

                $chunks = $this->makeCSVChunks($content);
                // $chunks = json_encode($chunks);

                //info($chunks);
            } 
            elseif ($ext === "web") {

                $file =  Storage::disk('private')->get($h->path);

                $urls = explode("\n", $file);

                $content = $this->scrapeWebsites($urls);

                $chunks = $this->makeTxtChunks($content);


                //info($chunks);
            }

        
            $req = Http::timeout(300)->withoutVerifying()->post("https://chatbot-trainer-2.yousuf.website/api/request?pro=129", [
                'chunks' => $chunks,
                'extension' => $ext,
                'openaiAPIKey' =>   $this->openaiKey,
                'historyId' => $h->id,
                'domain' => env('CHATBOT_URL'),
                'pineconeHost' =>  $this->host,
                'pineconeAPIKey' => env('PINECONE_API'),
                'pineconeEnv' => env('PINECONE_ENV'),
                'pineconeIndexName' => env('PINECONE_INDEX_NAME'),
            ]);

            if($req->successful()){
                
                History::where("id", $this->id)->update([
                    'sent' => 1
                ]);
          
            }

        }
    }

    private function makePDFChunks(array $list): array
    {
        $sources = [];
        $chunks = [];
        $textLength = 0;
        $snapshot = "";

        foreach ($list['pages'] as $key => $pg) {


            $text = $pg;

            $text = preg_replace('/\n+/', ' ', $text);
            $text = preg_replace('/\t+/', ' ', $text);

            #remove special chars except space, comma, quote, minus, dot
            $text = preg_replace('/[^\w.,\'\"\-\s]|_/', '', $text);

            //if text empty continue looping
            if (empty($text)) continue;

            //snapshot
            if (strlen($snapshot) < 1000) {
                $snapshot .= $this->trimString($text, (1000 - strlen($snapshot)));
            }

            //chunk_split($text, $this->charLimit,  "<||>#<>#<>#{$list['source'][$key]}");

            //making chunks
            $chunkArr = chunk_split($text, $this->charLimit,  "<||>");

            //making chunks array and removing last empty item
            $tempChunk = array_filter(explode('<||>', $chunkArr));

            //merging old chunk array with new
            $chunks = array_merge($chunks, $tempChunk);

            //making same size source array
            $tempSource = array_fill(0, count($tempChunk), $list['source'][$key]);

            //merging old and new source
            $sources = array_merge($sources, $tempSource);

            // $chunks .= chunk_split($text, $this->charLimit,  "<||>#<>#<>#{$list['source'][$key]}");

            //counting total str len
            $textLength += strlen($text);
        }

        return [

            'main' => [
                'text' => $chunks,
                'source' => $sources,
            ],

            'textLength' => $textLength,
            'snapshot' => $snapshot
        ];
    }

    private function makeCSVChunks(string $content): array{

        $csvArray = [];
        
        // Convert the CSV content to an array of lines
        $csvLines = explode(PHP_EOL, $content);
    
        // Iterate through each line and process the CSV data
        foreach ($csvLines as $line) {
            // Skip empty lines
            if (!empty($line)) {
                // Parse the CSV line using str_getcsv
                $data = str_getcsv($line);

                if(empty($data)){
                    continue;
                }

                // Use array_filter with the custom callback function
                $data = array_filter($data);
    
                // Combine the values of each row using the specified format
                $formattedRow = implode(' == ', $data);
    
                // Add the formatted row to the array
                $csvArray[] = $formattedRow;
            }
        }

        $chunks = array_filter($csvArray, function ($item) {
            return !empty($item);
        });

        return [
            'text' => $chunks,
            'source' => array_fill(0, count($chunks), '')
        ];
    

    }

    private function makeTxtChunks(string $text): array
    {

        $newTextArray = [];

        $text = preg_replace('/\n+/', ' ', $text);
        $text = preg_replace('/\t+/', ' ', $text);

        #remove special chars except space, comma, quote, minus, dot
        $text = preg_replace('/[^\w.,\'\"\-\s]|_/', '', $text);

        $chunks = chunk_split($text, $this->charLimit, "<||>");
        $chunks = explode('<||>', $chunks);

        $chunks = array_filter($chunks, function ($item) {
            return !empty($item);
        });

        return [
            'text' => $chunks,
            'source' => array_fill(0, count($chunks), '')
        ];
    }

    private function trimString($string, $maxLength = 2000)
    {
        if (strlen($string) > $maxLength) {
            $trimmedString = substr($string, 0, $maxLength);
            return $trimmedString . "...";
        } else {
            return $string;
        }
    }

    private function scrapeWebsites($urls)
    {

        $result = "";

        foreach ($urls as $url) {

            $dom = new Dom;
            try {
                // Make an HTTP request with a timeout of 30 seconds
                $response =  Http::withoutVerifying()->withHeaders([
                    'User-Agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/91.0.4472.124 Safari/537.36',
                ])->timeout(30)->get(trim($url));

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
                    
                    //info($bodyInnerText);

                    // Store the result
                    $result .= $bodyInnerText . "\n";
                } else {
                    //info($url . ' FAILED 1');
                    $status = $response->status();
                    //info($url . " : " . $status);
                    continue;
                }
            } catch (\Exception $e) {
                //info($url . ' FAILED 2');
                $status = $response->status();
                //info($url . " : " . $status);
                continue;
            }
        }

        return $result;
    }
}
