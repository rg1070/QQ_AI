<?php

namespace App\Library;

use App\Models\Setting;
use Illuminate\Support\Facades\Http;

use \Probots\Pinecone\Client as Pinecone;

class PineConeClient
{

    private $pinecone;
    private $host, $region, $cloud;
    private $indexName;
    private $apiKey;
    private $environment;

    function __construct()
    {

        $this->apiKey = env('PINECONE_API');
        $this->environment = env('PINECONE_ENV');
        $this->indexName = env('PINECONE_INDEX_NAME');
        
        $this->host = Setting::where('id', 1)->value('host');
        
        $this->region = env('PINECONE_REGION');
        $this->cloud = env('PINECONE_CLOUD');

        // Initialize Pinecone
        $this->pinecone = new Pinecone($this->apiKey, $this->environment);
    }


    public function queryVectors($vectors, $top){

        $response = Http::withHeaders([
            'Api-Key' => $this->apiKey,
            'Content-Type' => 'application/json',
        ])->post($this->host . "/query", [
            'vector' => $vectors,
            'topK' => $top,
        ]);

        if ($response->successful()) {
            return $response->json();
        } else {
            return false;
        }
    }

     public function insertVectors($vectors)
    {

        $response = Http::withHeaders([
            'Api-Key' => $this->apiKey,
            'content-type' => 'application/json',
        ])->post($this->host. "/vectors/upsert", [
            'vectors' => $vectors
        ]);
        
        info($response->json());

        return $response->successful();
    }

   

    public function deleteVectors(){

        
        $response = Http::withHeaders([
            'Api-Key' => $this->apiKey,
        ])->delete("https://api.pinecone.io/indexes/{$this->indexName}");


        return $response->successful();
        
    }


    public function createIndex()
    {

        $response = Http::withHeaders([
            'Accept' => 'application/json',
            'Content-Type' => 'application/json',
            'Api-Key' => $this->apiKey,
        ])->post("https://api.pinecone.io/indexes", [
            'name' => $this->indexName,
            'dimension' => 1536,
            'metric' => 'cosine',
            'spec' => [
                'serverless' => [
                    'cloud' => $this->cloud , //aws,
                    'region' => $this->region //'us-east-1',
                ],
            ],
        ]);

        sleep(2);

        //get host & update
        $host = $this->getHost();
        Setting::where('id', 1)->update([
            'host' => $host,
        ]);

        return $response->successful();

        // Process the response as needed

    }

    //for new api, now host is required
    public function getHost(){

        $response = Http::withHeaders([
            'Api-Key' => $this->apiKey,
        ])->get("https://api.pinecone.io/indexes/{$this->indexName}");

        if($response->successful()){
            return "https://" . $response->json()['host'];
        }
        else{
            return false;
        }

    }

    public function listIndexes(){

        $response = Http::withHeaders([
            'Api-Key' => $this->apiKey,
        ])->get('https://api.pinecone.io/indexes');

        return $response->json();
    }

   
    function getIndexDetails(){

        $response = Http::withHeaders([
            'Api-Key' => $this->apiKey,
        ])->get("https://api.pinecone.io/indexes/{$this->indexName}");

       return $response->successful();
    }
}
