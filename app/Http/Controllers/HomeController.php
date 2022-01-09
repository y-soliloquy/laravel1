<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Memo;
use App\Models\Tag;
use App\Models\MemoTag;
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

        // DBからユーザーに紐づいたタグを取得する
        $tags = Tag::where('user_id', '=', \Auth::id())
            -> whereNull('deleted_at')
            -> orderBy('id', 'DESC')
            -> get();

        return view('create', compact('memos', 'tags'));
    }

    public function store(Request $request)
    {
        $posts = $request->all();



        DB::transaction(function() use($posts) {
            $memo_id = Memo::insertGetId(['content' => $posts['content'], 'user_id' => \Auth::id() ]);

            // ユーザーのメモの中に追加する予定のタグがすでに存在しているか
            $isTagName = Tag::where('user_id', '=', \Auth::id()) -> where('name', '=', $posts['new_tag']) -> exists();

            if((!empty($posts['new_tag']) || $posts['new_tag'] === "0")&& !$isTagName) {
                $tag_id = Tag::insertGetId(['user_id' => \Auth::id(), 'name' => $posts['new_tag']]);
                MemoTag::insert(['memo_id' => $memo_id, 'tag_id' => $tag_id]);
            }

            // 既存タグが紐づけられたらmemo_tagsテーブルに追加する
            if (!empty($posts['tags'][0])) {
                foreach($posts['tags'] as $tag) {
                    MemoTag::insert(['memo_id' => $memo_id, 'tag_id' => $tag]);
                }
            }
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
