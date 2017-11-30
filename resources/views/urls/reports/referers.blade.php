@extends('head')

@section('title')
    Статистика переходов
@endsection

@section('content')
    <div class="row">
        Редиректы: <strong>{{route('url.hash', ['hash' => $url->hash])}}</strong> ---> <strong>{{$url->url}}</strong>
    </div>
    <div class="divider"></div>
    <div class="row"></div>

    <div class="row">
        <table class="responsive-table bordered highlight">
            <thead>
                <tr> <th>Источник перехода</th> <th>Количество переходов</th>
                </tr>
            </thead>

            <tbody>
                @foreach($referers as $ref)
                    <tr>
                        <td>{{$ref->referer}}</td> <td>{{$ref->num}}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    <div class="row">
        <div class="col s4 offset-s2">
            <a class="btn" href="{{route('shorten_urls.show', ['id' => $url->id])}}">Вернуться</a>
        </div>

        <div class="col s4 ">
            <a class="btn" href="{{route('shorten_urls.index')}}">Смотреть ссылки</a>
        </div>
@endsection