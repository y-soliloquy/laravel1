<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Memo;
use DB;

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
        // DBからメモの情報を取得する
        $memos = Memo::select('memos.*')
            -> where('user_id', '=', \Auth::id())
            -> whereNull('deleted_at')
            -> orderBy('updated_at', 'DESC')
            -> get();

        return view('create', compact('memos'));
    }

    public function store(Request $request)
    {
        $posts = $request->all();


        DB::transaction(function() use($posts) {
            $memo_id = Memo::insertGetId(['content' => $posts['content'], 'user_id' => \Auth::id() ]);
            if(!empty($posts['new_tag'])) {
                dd('新規タグがあります');
            };
        });


        return redirect(route('home'));
    }

    public function edit($id)
    {
        // DBからメモの情報を取得する
        $memos = Memo::select('memos.*')
            -> where('user_id', '=', \Auth::id())
            -> whereNull('deleted_at')
            -> orderBy('updated_at', 'DESC')
            -> get();

        $edit_memo = Memo::find($id);

        return view('edit', compact('memos', 'edit_memo'));
    }

    public function update(Request $request)
    {
        $posts = $request->all();

        Memo::where('id', $posts['memo_id']) -> update(['content' => $posts['content']]);

        return redirect(route('home'));
    }

    public function destroy(Request $request)
    {
        $posts = $request->all();

        // Memo::where('id', $posts['memo_id']) -> delete(); これだと物理削除になり対象がDBから消滅するため採用しない
        Memo::where('id', $posts['memo_id']) -> update(['deleted_at' => date("Y-m-d H:i:s", time())]); // この方法で削除日時を追加し一覧に載せない仕方で論理削除する

        return redirect(route('home'));
    }
}
