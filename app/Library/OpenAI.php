<?php

namespace App\Library;

use Illuminate\Support\Facades\Http;
// use OpenAI\Laravel\Facades\OpenAI as OpenAILib;

use OpenAI as OpenAILib;


class OpenAI
{

    public static function getAnswer(string $model, string $apiKey, string $systemMsg, float $temperature, string $context,  string $query): object
    {

        $url = "https://api.openai.com/v1/chat/completions";
        //$model = 'gpt-3.5-turbo';

        $messages = [];

        $messages[] = [
            'role' => 'system', 'content' => $systemMsg
        ];

        #"You are a helpful chatbot that answer based on provided information. Answer the question in small paras for better readability and in as much details as possible using the provided document and also mention the Source and Page Number in square brackets [] if its mentioned in the context. If there are more than one sources, answer in paras with proper line breaks. And if the answer is not contained within the context try to to answer with your information.",

        // $prompt = "Answer the question truthfully and in a friendly tone using the provided information, and if the answer is not contained within the infromation, say exacly : ['Sorry, this information is not in your data.'].\n\nContext:\n$context\n\nQ:$query\nA:";

        $prompt = "Answer the question in friendly tone using the provided information.\n\nInformation: $context\n\nQ:$query\nA:";

        #$prompt = "Context:\n$context\n\nQ:$query\nA:";

        $messages[] = [
            'role' => 'user', 'content' => $prompt,
        ];

        $resp = Http::withHeaders([
            'Content-Type' => 'application/json',
            'Authorization' => "Bearer $apiKey"
        ])->post($url, [
            'messages' => $messages,
            'model' => $model,
            "temperature" => $temperature,
            "top_p" => 1,
        ]);

        info($resp);

        $obj = new \stdClass();

        //error
        if (!$resp->successful()) {
            $obj->success = false;
            $obj->response = $resp->body();
            return $obj;
        } else {

            $obj->success = true;
            $obj->response = $resp->json();

            $obj->answer = !empty($resp['choices'][0]['message']['content'])
                ?
                $resp['choices'][0]['message']['content']
                :
                env("OPENAI_ERROR_MSG", "something went wrong, please try again later.");


            return $obj;
        }
    }

    public static function streamAnswer(string $model, string $apiKey, string $systemMsg, float $temperature, string $context,  string $query): object
    {

        $messages[] = [
            'role' => 'system', 'content' => $systemMsg
        ];

        $prompt = "Answer the question in friendly tone using the provided information.\n\nInformation: $context\n\nQ:$query\nA:";

        $messages[] = [
            'role' => 'user', 'content' => $prompt,
        ];

        $client = OpenAILib::factory()
            ->withHttpClient(new \GuzzleHttp\Client(['timeout'  => 30]))
            ->withApiKey($apiKey)
            ->make();


        try {
            $stream = $client->chat()->createStreamed([
                'model' => $model,
                'messages' => $messages,
                'temperature' => $temperature,
                "top_p" => 1,
            ]);
        } catch (\Exception $e) {

            info($e);

            return response()->json([
                "msg" => "Something went wrong.",
                "status" => "failed"
            ], 500);
        }

        // $client = OpenAILib::client($apiKey);

        return response()->stream(function () use ($stream) {

            foreach ($stream as $response) {

                if (isset($stream->error)) {

                    echo "$stream->error->message";
                    ob_flush();
                    flush();
                    return;
                }

                $text = $response->choices[0]->delta->content;

                if (connection_aborted()) {
                    break;
                }

                echo $text;
                ob_flush();
                flush();
            }
        });
    }

    public static function getEmbeddings(string $query, string $apiKey): object
    {

        $resp = Http::withHeaders([
            'Content-Type' => 'application/json',
            'Authorization' => "Bearer $apiKey"
        ])->post('https://api.openai.com/v1/embeddings', [
            'model' => env('EMB_MODEL', 'text-embedding-ada-002'),
            'input' => $query,
        ]);

        $obj = new \stdClass();

        //error
        if (!$resp->successful()) {
            $obj->success = false;
            $obj->response = $resp->body();
            info($obj->response);
            return $obj;
        } else {
            $obj->success = true;
            $obj->response = $resp->json();
            $obj->embeddings = $resp['data'][0]['embedding'];
            return $obj;
        }
    }
}
