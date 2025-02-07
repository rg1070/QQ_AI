<?php

namespace App\Http\Controllers;

use App\Models\Setting;
use Illuminate\Http\Request;

use App\Library\OpenAI;
use App\Library\PineConeClient;
use App\Models\Chunk;
use Illuminate\Support\Facades\Http;

class ChatBotController extends Controller
{
    public function index(Request $req)
    {

        $data = Setting::select('userMsgColor', 'chatBubbleColor', 'displayName', 'initMsg', 'aiMsgColor', 'allowedDomains', 'chatIcon')->where('id', 1)->first();


        if (!empty($data->allowedDomains)) {
            $allowedDomains = explode("\n", $data->allowedDomains);

            $origin = str_replace('www.', '', $req->getSchemeAndHttpHost());

            if (!in_array($origin, $allowedDomains)) {
                return  response()->json([
                    "msg" => "Failed",
                    "status" => "failed"
                ], 401);
            }
        }

        $data->chatIcon =  env('CHATBOT_URL') . '/storage/' . $data->chatIcon;


        return  response()->json([
            "data" => $data,
            "msg" => "Success",
            "status" => "success"
        ], 200);
    }

    public function chat(Request $req)
    {


        if (!$req->filled('message')) {
            return  response()->json([
                "msg" => "Bad request",
                "status" => "failed"
            ], 400);
        }


        //2000 max
        $message = substr($req->message, 0, 2000);

        $settings = Setting::where('id', 1)->select('openaiKey', 'temperature', 'model', 'systemMsg', 'allowedDomains')->first();
        $contextSize = 20;

        //allowed domain checker

        if (!empty($settings->allowedDomains)) {
            $allowedDomains = explode("\n", $settings->allowedDomains);

            $origin = str_replace('www.', '', $req->getSchemeAndHttpHost());

            if (!in_array($origin, $allowedDomains)) {
                return  response()->json([
                    "msg" => "Your request has been rejected due to security reasons.",
                    "status" => "failed"
                ], 401);
            }
        }

        $emb = OpenAI::getEmbeddings($message, $settings->openaiKey);

        if ($emb->success === false) {
            return response()->json([
                "msg" => "Something went wrong 1",
                "status" => "failed"
            ], 400);
        }

        $embeddings = $emb->embeddings;

        $pinecone = new PineConeClient();

        $vector = $pinecone->queryVectors($embeddings, $contextSize);

        //$vector = VectorDB::get($chatbot->_id, $contextSize, $embeddings);

        if ($vector === false) {
            return response()->json([
                "msg" => "Something went wrong.",
                "status" => "failed"
            ], 400);
        }

        $result = $vector['matches'];

        $ids = [];

        foreach ($result as $r) {
            $ids[] = $r['id'];
        }

        $context = "";
        $systemMsg = $settings->systemMsg;
        $chunks = Chunk::whereIn('id', $ids)->select('chunk')->get();

        foreach ($chunks as $chunk) {

            $context .= "$chunk->chunk\n";
            info($chunk->chunk);
        }

       # info($context);



        $temperature =  number_format($settings->temperature / 10, 2);

        return OpenAI::streamAnswer($settings->model, $settings->openaiKey, $systemMsg, $temperature, $context, $message);
    }

    public function tgChat(Request $req)
    {

        // Record the start time
        $startTime = microtime(true);

        $telegram = $req->all();

        if (empty($telegram['message']['text'])) {
            return  response()->json([
                "msg" => "Bad request",
                "status" => "failed"
            ], 400);
        }

        //2000 max
        $message = substr($telegram['message']['text'], 0, 2000);

        $settings = Setting::where('id', 1)->select('openaiKey', 'temperature', 'model', 'systemMsg', 'allowedDomains')->first();
        $contextSize = 7;

        //allowed domain checker

        if (!empty($settings->allowedDomains)) {
            $allowedDomains = explode("\n", $settings->allowedDomains);

            $origin = str_replace('www.', '', $req->getSchemeAndHttpHost());

            if (!in_array($origin, $allowedDomains)) {
                return  response()->json([
                    "msg" => "Your request has been rejected due to security reasons.",
                    "status" => "failed"
                ], 401);
            }
        }

        $emb = OpenAI::getEmbeddings($message, $settings->openaiKey);

        if ($emb->success === false) {
            return response()->json([
                "msg" => "Something went wrong 1",
                "status" => "failed"
            ], 400);
        }

        $embeddings = $emb->embeddings;

        $pinecone = new PineConeClient();

        $vector = $pinecone->queryVectors($embeddings, $contextSize);

        //$vector = VectorDB::get($chatbot->_id, $contextSize, $embeddings);

        if ($vector === false) {
            return response()->json([
                "msg" => "Something went wrong.",
                "status" => "failed"
            ], 400);
        }

        $result = $vector['matches'];

        $ids = [];

        foreach ($result as $r) {
            $ids[] = $r['id'];
        }

        $context = "";
        $systemMsg = $settings->systemMsg;
        $chunks = Chunk::whereIn('id', $ids)->select('chunk')->get();

        foreach ($chunks as $chunk) {

            $context .= "$chunk->chunk\n";
            // info($chunk->chunk);
        }

        $temperature =  number_format($settings->temperature / 10, 2);

        $openai =  OpenAI::getAnswer($settings->model, $settings->openaiKey, $systemMsg, $temperature, $context, $message);


        $ans = $openai->answer;

        $settings = Setting::where('id', 1)->select('tgBotId')->first();
        $tgBotId = $settings->tgBotId;
        $chatId = $telegram['message']['chat']['id'];

        $resp = Http::timeout(20)->get("https://api.telegram.org/bot$tgBotId/sendmessage?chat_id=$chatId&text=$ans");

        // Record the end time
        $endTime = microtime(true);

        $totalSeconds = $endTime - $startTime;

        info($totalSeconds);

        return '200';
    }
}
