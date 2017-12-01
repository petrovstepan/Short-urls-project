@extends('head')

@section('title')
    Информация о пользователе {{Auth::user()->name}}
@endsection

@section('content')
    <div class="row">
        <table class="responsive-table bordered highlight">
            <tbody>
                <tr>
                    <th>Имя</th> <td>{{$user->name}}</td>
                </tr>
                <tr>
                    <th>Email</th> <td><a href="mailto:{{$user->email}}">{{$user->email}}</a></td>
                </tr>
                <tr>
                    <th>Дата регистрации</th> <td>{{$user->created_at}}</td>
                </tr>
                <tr>
                    <th>Кол-во созданных ссылок</th> <td>{{$user->urls_count}} <a href="{{route('shorten_urls.index')}}">Смотреть</a></td>
                </tr>

                @if (count($user->urls) !== 0)
                <tr>
                    <th></th><th>Самая популярная ссылка</th>
                </tr>

                    @foreach ($user->urls as $url)
                        <tr>
                            <th>Короткий адрес</th> <td><a href="{{route('url.hash', ['hash' => $url->hash])}}">{{route('url.hash', ['hash' => $url->hash])}}</a></td>
                        </tr>
                        <tr>
                            <th>Адрес</th> <td><a href="{{$url->url}}">{{$url->url}}</a></td>
                        </tr>
                        <tr>
                            <th>Кол-во переходов</th> <td>{{$url->follows_count}}</td>
                        </tr>
                        <tr>
                            <th>Узнать подробнее</th> <td><a class="btn yellow" href="{{route('shorten_urls.show', ['id' => $url->id])}}">Смотреть</a> </td>
                        </tr>
                    @endforeach
                @endif
            </tbody>
        </table>
    </div>
    <div class="row"></div>
    <div class="row">
        <div class="col s4 offset-s2"><a class="btn light-green accent-3" href="{{route('shorten_urls.create')}}">Создать ссылку</a></div>
        <div class="col s4"><a class="btn blue" href="{{route('shorten_urls.index')}}">Смотреть ссылки</a> </div>
    </div>

@endsection