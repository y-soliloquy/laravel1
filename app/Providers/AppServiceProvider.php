<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Models\Memo;
use App\Models\Tag;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        //
    }

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        // 全てのメソッドが呼ばれる前に走る処理
        view() -> composer('*', function($view) {
            $query_tag = \Request::query('tag');

            // DBからメモの情報を取得する
            $memos = Memo::select('memos.*')
                -> where('user_id', '=', \Auth::id())
                -> whereNull('deleted_at')
                -> orderBy('updated_at', 'DESC')
                -> get();

            // タグ一覧取得
            $tags = Tag::where('user_id', '=', \Auth::id())
                -> whereNull('deleted_at')
                -> orderBy('id', 'DESC')
                -> get();

            // viewに上記を渡す
            $view -> with('memos', $memos) -> with('tags', $tags);

        });
    }
}
