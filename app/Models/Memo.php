<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Memo extends Model
{
    use HasFactory;

    public function getMyMemo() {
        $query_tag = \Request::query('tag');
        if (!empty($query_tag)) {
            $memos = Memo::select('memos.*')
                -> leftJoin('memo_tags', 'memo_tags.memo_id', '=', 'memos.id')
                -> where('memo_tags.tag_id', '=', $query_tag)
                -> where('user_id', '=', \Auth::id())
                -> whereNull('deleted_at')
                -> orderBy('updated_at', 'DESC')
                -> get();
        } else {
        // DBからメモの情報を取得する
            $memos = Memo::select('memos.*')
                -> where('user_id', '=', \Auth::id())
                -> whereNull('deleted_at')
                -> orderBy('updated_at', 'DESC')
                -> get();
        }

        return $memos;
    }
}
