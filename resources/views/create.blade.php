@extends('layouts.app')

@section('content')
<div class="card">
    <div class="card-header">新規メモ作成</div>
    <form class="card-body" action="/store" method="POST">
        <div class="form-group">
            <textarea class="form-control" name="content" rows="3" placeholder="メモを入力"></textarea>
        </div>
    </form>
</div>
@endsection
