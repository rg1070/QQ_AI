<?php

namespace App\Http\Controllers;

use App\Models\History;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Storage;
use App\Library\PineConeClient;
use App\Models\Chunk;

class UploadTrainingData extends Controller
{
    public function index(Request $req)
    {

        $data = History::select('status', 'name', 'created_at', 'extension')->orderByDesc('id')->get();

        if ($req->isMethod('get')) {

            if (isset($_GET['reset'])) {

                //delete all pinecone vectors
                $pinecone = new PineConeClient();

                try {
                    $dlt = $pinecone->deleteVectors();
                    sleep(5);
                } catch (\Exception $err) {
                    return back()->with("status", ["error",  $err->getMessage()]);
                }

                if ($dlt === false) {
                    return back()->with("status", ["error",  'Something went wrong, when deleting training data.']);
                }

                //delte chunks
                Chunk::truncate();

                //delte history
                History::whereNotNull('id')->delete();

                sleep(9);

                //create new index
                $d = $pinecone->createIndex();

                info($d);

                return back()->with("status", ["success",  'Deleted all training data successfully.']);
            }

            return view('training', ['data' => $data]);
        }

        if ($req->isMethod('post')) {
            return $this->handlePost($req);
        }
    }

    private function handlePost($req)
    {

        if (!$req->filled('webLinks')) {

            $validator = Validator::make($req->all(), [
                'file' => 'required|mimes:pdf,txt,csv|max:10240',
            ], [
                'file.required' => 'Please choose a file to upload.',
                'file.mimes' => 'Only PDF and TXT files are allowed.',
                'file.max' => 'File size must be less than 10MB.',
            ]);


            //if validator failed
            if ($validator->fails()) {
                return back()->with("status", ["error", $validator->errors()->first()]);
            }

            // Continue with your logic here.
            $file = $req->file('file');
            $extension = $file->getClientOriginalExtension();
            $fileName = time() . '.' . $file->getClientOriginalName();

            if ($extension == 'pdf') {
                $path = $file->storeAs('pdf', $fileName, 'private');
            } elseif ($extension == 'txt') {
                $path = $file->storeAs('txt', $fileName, 'private');
            } elseif ($extension == 'csv') {
                $path = $file->storeAs('csv', $fileName, 'private');
            } 
            else {
                return back()->with("status", ["error",  'Invalid file type. Only PDF and TXT files are allowed.']);
            }

            History::create([
                'name' =>  $file->getClientOriginalName(),
                'status' => 'pending',
                'path' => $path,
                'extension' => $extension
            ]);
        } else {


            $folder = 'web';
            $fileName = uniqid() . '.txt';
            $disk = 'private';
            $visibility = 'private';

            // The text content you want to write to the file
            $links = $req->webLinks;

            // Specify the file path within the storage disk
            $filePath = $folder . '/' . $fileName;

            // Use the Storage facade to store the file with specific disk and visibility
            Storage::disk($disk)->put($filePath, $links, $visibility);


            History::create([
                'name' =>  $req->filled('mainWeb') ? $req->mainWeb : 'Website Links',
                'status' => 'pending',
                'path' => $filePath,
                'extension' => 'web'
            ]);
        }

        return back()->with("status", ["success",  'The training is in queue.']);
    }


    public function historyStatusUpdater(Request $req)
    {


        if (!$req->filled('status') || !$req->filled('id')) {
            return response()->json([
                "msg" => "Not a valid data",
                "status" => "failed"
            ], 400);
        }

        if (!$req->filled('turbo') || $req->turbo !== env('TURBO')) {
            return response()->json([
                "msg" => "Not a valid data",
                "status" => "failed"
            ], 400);
        }

        History::where("id", $req->id)->update([
            'status' => $req->status //'success' or 'failed'
        ]);
    }

    public function chunkStorer(Request $req)
    {

        $validator = Validator::make($req->all(), [
            'store' => 'required|array',
            'status' => 'required',
            'id' => 'required',
        ]);

        //if validator failed
        if ($validator->fails()) {
            return response()->json([
                "msg" => "Not a valid data",
                "status" => "failed"
            ], 400);
        }

        if (!$req->filled('turbo') || $req->turbo !== env('TURBO')) {
            return response()->json([
                "msg" => "Not a valid data",
                "status" => "failed"
            ], 400);
        }

        $store = $req->store;
        $status = $req->status;

        //if status false then no need
        if ($status === false) {

            History::where("id", $req->id)->update([
                'status' => 'failed'
            ]);

            return response()->json([
                "msg" => "Done",
                "status" => "succes"
            ], 200);
        }

        //$vectors = [];

        $chunksToInsert = [];

        foreach ($store as $items) {
            foreach ($items['chunks'] as $key => $i) {
                $chunksToInsert[] = [
                    'id' => $items['chunkIds'][$key],
                    'chunk' => $i,
                    'source' => $items['sources'][$key],
                ];
            }
        }

        // Bulk insert all chunks
        Chunk::insert($chunksToInsert);

        // foreach ($store as $items) {

        //     foreach ($items['chunks'] as $key => $i) {

        //         //insert chunk in db
        //         $create = Chunk::create([
        //             'id' => $items['chunkIds'][$key],
        //             'chunk' => $i,
        //             'source' => $items['sources'][$key]
        //         ]);

        //         // $vectors[] = [
        //         //     'id' => (string)$create->id,
        //         //     'values' => $items['embs'][$key],
        //         // ];
        //     }
        // }

        // //upload to pine cone
        // $pinecone = new PineConeClient();

        // //inset vectors

        // try {
        //     //inset vectors
        //     $status = $pinecone->insertVectors($vectors);
        // } catch (\Exception $err) {
        //     info($err->getMessage());
        //     $status = false;
        // }


        //update status
        // History::where("id", $req->id)->update([
        //     'status' => $status === true ? 'success' : 'failed'
        // ]);

        return response()->json([
            "msg" => "Done",
            "status" => "succes"
        ], 200);
    }
}
