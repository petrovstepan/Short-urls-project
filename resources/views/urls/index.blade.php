@extends('head')
@section('title')
    Ссылки пользователя {{Auth::user()->name}}
@endsection

@section('content')
    <div class="row">
        @if (count($urls) === 0) <span class="help-block">Вы еще не создали ни одной короткой ссылки</span>
        @else
            <table class="responsive-table bordered highlight">
                <thead>
                <tr>
                    <th>Сайт</th>
                    <th>Ссылка</th>
                    <th>Просмотр</th>
                </tr>
                </thead>

                <tbody>
                @foreach ($urls as $url)

                    <tr>
                        <td><a href="{{$url->url}}">{{$url->url}}</a></td>

                        <td><a href="{{route('url.hash', ['hash' => $url->hash])}}">{{route('url.hash', ['hash' => $url->hash])}}</a></td>

                        <td><a href="{{route('shorten_urls.show', ['id' => $url->id])}}">Смотреть подробности</a></td>

                    </tr>

                @endforeach
                </tbody>
            </table>
        @endif
    </div>
    <div class="row"></div>
    <div class="row">
        <div class="col s4 offset-s4">
            <a class="btn light-green accent-3" href="{{route('shorten_urls.create')}}">Создать ссылку</a>
        </div>
    </div>

@endsection