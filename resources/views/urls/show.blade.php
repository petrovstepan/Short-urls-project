@extends('head')

@section('title')
    Короткая ссылка {{$url->hash}}
@endsection

@section('content')
    <div class="row">
        <table class="responsive-table bordered highlight">
            <tbody>
                <tr>
                    <th>ID</th> <td>{{$url->id}}</td>
                </tr>
                <tr>
                    <th>Сайт</th> <td><a href="{{$url->url}}">{{$url->url}}</a></td>
                </tr>
                <tr>
                    <th>Ссылка</th> <td><a href="{{route('url.hash', ['hash' => $url->hash])}}">{{route('url.hash', ['hash' => $url->hash])}}</a></td>
                </tr>
                <tr>
                    <th>Общее количество переходов</th> <td>{{$url->follows_count}}</td>
                </tr>
                <tr>
                    <th>Источники переходов</th> <td><a href="{{route('url.referers', ['id' => $url->id])}}">Подробно</a></td>
                </tr>
                <tr>
                    <th>Удаление</th>
                    <td>
                        <form action="{{route('shorten_urls.destroy', ['id' => $url->id])}}" method="POST">
                            <input type="hidden" name="_method" value="DELETE">
                            <a class="btn red submit">Удалить</a>
                        </form>
                    </td>
                </tr>
            </tbody>
        </table>
    </div>

    <div class="row"></div>


    <div class="row"></div>

    <div class="row">
        <strong>Смотреть временной график переходов</strong>
    </div>

    @if (count($errors) > 0)
        <span class="help-block">
            <strong> @foreach ($errors->all() as $error){{ $error }} <br> @endforeach</strong>
        </span>
        <div class="row"></div>
        <div class="row"><div class="divider"></div></div>
    @endif

    <div class="row">
        <form id="stats" action="{{route('url.stats', ['id' => $url->id, 'sort' => 'sort'])}}" method="GET">
            <div class="row">
                <input type="date" name="from_date" class="col s5" placeholder="Начало периода в формате 2017-11-20">
                <select name="sort" class="col s4 offset-s1">
                    <option value="minute" selected>Поминутно</option>
                    <option value="hour">По часам</option>
                    <option value="day">По дням</option>
                </select>
            </div>
            <div class="row">
                <input type="date" name="to_date" class="col s5" placeholder="Конец периода в формате 2017-11-30">
                <div class="col s4 offset-s1"><a class="btn submit yellow">Получить отчет</a></div>
            </div>
        </form>
    </div>
    <div class="row">
        <div class="col s6 offset-s3"><a href="{{route('shorten_urls.index')}}" class="btn blue">Смотреть все ссылки</a></div>
    </div>


@endsection