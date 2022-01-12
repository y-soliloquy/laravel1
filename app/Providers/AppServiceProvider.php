<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Medels\Memo;

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
        view() -> comporser('*', function($view) {
            // DBからメモの情報を取得する
            $memos = Memo::select('memos.*')
            -> where('user_id', '=', \Auth::id())
            -> whereNull('deleted_at')
            -> orderBy('updated_at', 'DESC')
            -> get();

            // viewに上記を渡す
            $view -> with('memos', $memos);

        });
    }
}
