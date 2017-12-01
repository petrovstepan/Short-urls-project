@extends('head')

@section('title')
    Создать короткую ссылку
@endsection

@section('content')
    @if ($errors->has('url'))
        <span class="help-block">
            <strong>{{ $errors->first('url') }}</strong>
        </span>
    @endif
    <div class="row">
        <form action="{{route('shorten_urls.store')}}" method="POST">
            <input type="text" name="url" placeholder="http://example.com/your/parameters/here" value="" class="col s5">
            <div class="col s4 offset-s1"><a class="btn submit light-green accent-3">Создать ссылку</a></div>
        </form>
    </div>
    <div class="row">
        <div class="col s4"><a class="btn blue" href="{{route('shorten_urls.index')}}">Смотреть ссылки</a> </div>
    </div>
@endsection