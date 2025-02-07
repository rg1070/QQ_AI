<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Models\Setting;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Http;

class SettingsController extends Controller
{
    public function index(Request $req)
    {

        if ($req->isMethod('get')) {

            $data = Setting::where('id', 1)->first();
            return view('settings', ['data' => $data]);
        }

        if ($req->isMethod('post')) {

            $validator = Validator::make($req->all(), [
                'openaiKey' => 'required',
                'model' => 'required',
                'displayName' => 'required',
                'systemMsg' => 'required',
                'initMsg' => 'required',
                'aiMsgColor' => 'required',
                'userMsgColor' => 'required',
                'chatBubbleColor' => 'required',
                'temperature' => 'required|numeric',
            ]);

            if ($validator->fails()) {
                return back()->with("status", ["error", 'Something is empty, please check']);
            }


            $models = [
                'gpt-3.5-turbo',
                'gpt-3.5-turbo-16k',
                'gpt-4',
                'gpt-4-32k',
                'gpt-4-1106-preview',
                'gpt-4-turbo-preview',
                'gpt-4o'
            ];

            if (!in_array($req->model, $models)) {
                return back()->with("status", ["error", 'Invalid model']);
            }


            //getting exact domain name
            if ($req->filled('allowedDomains')) {

                $domains = explode("\n", $req->allowedDomains);
                $domains = array_filter($domains);

                foreach ($domains as &$d) {
                    // Parse the URL
                    $parsedUrl = parse_url(trim($d));

                    // Get the host from the parsed URL
                    $d = $parsedUrl['host'];


                    // Remove 'www.' if present
                    $d = str_replace('www.', '', $d);
                }

                $allowedDomains = implode("\n", $domains);
            } else {
                $allowedDomains = null;
            }



            $fields = [
                'openaiKey' => $req->openaiKey,
                'temperature' => $req->temperature,
                'model' => $req->model,
                'aiMsgColor' =>  $req->aiMsgColor,
                'userMsgColor'  => $req->userMsgColor,
                'chatBubbleColor' => $req->chatBubbleColor,
                'displayName' => $req->displayName,
                'systemMsg' => $req->systemMsg,
                'initMsg' => substr($req->initMsg, 0, 255),
                'allowedDomains' => $allowedDomains,
                'tgBotId' => $req->filled('tgBotId') ? $req->tgBotId : null
            ];

            //setting tg webhook
            if ($req->filled('tgBotId')) {

                $webhookUrl = env('CHATBOT_URL') . '/api/tg-chatbot/chat';
                $resp = Http::timeout(20)->get("https://api.telegram.org/bot$req->tgBotId/setWebhook?url=$webhookUrl");

                info($resp->json());
            }


            if ($req->hasFile('chatIcon')) {

                $file = $req->file('chatIcon');
                $directory = 'images';

                // Generate a unique filename for the uploaded file
                $filename = 'chaticon.' . $file->getClientOriginalExtension();

                // Store the file in the public disk with the specified directory and filename
                $path = $file->storeAs($directory, $filename, 'public');

                $fields['chatIcon'] = $path;
            }

            if ($req->filled('password')) {
                $fields['password'] = Hash::make($req->password);
            }

            Setting::where('id', 1)->update($fields);

            return back()->with("status", ["success",  'Settings updated']);
        }
    }
}
