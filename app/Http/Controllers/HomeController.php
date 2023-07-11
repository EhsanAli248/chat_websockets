<?php

namespace App\Http\Controllers;

use App\Events\sendMessage;
use App\Models\Message;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;


class HomeController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Show the application dashboard.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function index()
    {
        return view('home');
    }
    public function chat()
    {

        return view('chat');

    }
    public function messages()
    {
        return Message::with('user')->get();
        // $messages=Message::with('user')->get();
        // return response()->json([
        //     'status'=>'ok',
        //     'message'=>'your complete chat with this person is here',
        //     'data'=>$messages,
        // ],200);
    }
    public function messageStore(Request $request)
    {
        $user = Auth::user();
        $messages = $user->messages()->create([
            'message' => $request->message,
        ]);
        broadcast(new sendMessage($user, $messages))->toOthers();
        return 'message sent';
    }
}
