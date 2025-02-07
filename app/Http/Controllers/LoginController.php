<?php

namespace App\Http\Controllers;

use App\Models\Setting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class LoginController extends Controller
{
    public function index(Request $req){

        if($req->isMethod('get')){
            return view('login');
        }

        if(!$req->filled('password')){
            return back();
        }

        $pass = Setting::where('id' , 1)->select('password')->first();

        if(Hash::check($req->password, $pass->password)){
            session([
                'loggedIn' => true
            ]);
        }
        else{

            return back()->with("status", ["error",  'Wrong Credentials']);
        }

        return redirect()->route('home');
    }
}
