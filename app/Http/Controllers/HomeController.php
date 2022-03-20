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

        // DBからユーザーに紐づいたタグを取得する
        $tags = Tag::where('user_id', '=', \Auth::id())
            -> whereNull('deleted_at')
            -> orderBy('id', 'DESC')
            -> get();

        return view('create', compact('tags'));
    }

    public function store(Request $request)
    {
        $posts = $request->all();
        $request -> validate(['content' => 'required']);

        DB::transaction(function() use($posts) {
            $memo_id = Memo::insertGetId(['content' => $posts['content'], 'user_id' => \Auth::id() ]);

            // ユーザーのメモの中に追加する予定のタグがすでに存在しているか
            $isTagNameExist = Tag::where('user_id', '=', \Auth::id()) -> where('name', '=', $posts['new_tag']) -> exists();

            if((!empty($posts['new_tag']) || $posts['new_tag'] === "0")&& !$isTagNameExist) {
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

        $edit_memo = Memo::select('memos.*', 'tags.id AS tag_id')
            -> leftJoin('memo_tags', 'memo_tags.memo_id', '=', 'memos.id')
            -> leftJoin('tags', 'memo_tags.tag_id', '=', 'tags.id')
            -> where('memos.user_id', '=', \Auth::id())
            -> where('memos.id', '=', $id)
            -> whereNull('memos.deleted_at')
            -> get();

        $include_tags = [];
        foreach($edit_memo as $memo) {
            array_push($include_tags, $memo['tag_id']);
        }

        $tags = Tag::where('user_id', '=', \Auth::id())
        -> whereNull('deleted_at')
        -> orderBy('id', 'DESC')
        -> get();

        return view('edit', compact('edit_memo', 'include_tags', 'tags'));
    }

    public function update(Request $request)
    {
        $posts = $request->all();
        $request -> validate(['content' => 'required']);

        DB::transaction(function() use($posts) {
            Memo::where('id', $posts['memo_id']) -> update(['content' => $posts['content']]);
            // 対象のメモとタグのidに関する情報をDBから物理削除する
            MemoTag::where('memo_id', '=', $posts['memo_id'])
                -> delete();

            // 対象のメモとタグのidに関する情報をDBに入れなおす
            foreach($posts['tags'] as $tag) {
                MemoTag::insert(['memo_id' => $posts['memo_id'], 'tag_id' => $tag]);
            };

            // 新しいタグが存在すればそれを追加
            $isTagNameExist = Tag::where('user_id', '=', \Auth::id()) -> where('name', '=', $posts['new_tag']) -> exists();

            if((!empty($posts['new_tag']) || $posts['new_tag'] === "0")&& !$isTagNameExist) {
                $tag_id = Tag::insertGetId(['user_id' => \Auth::id(), 'name' => $posts['new_tag']]);
                MemoTag::insert(['memo_id' => $posts['memo_id'], 'tag_id' => $tag_id]);
            }

        });


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
