@extends('head')

@section('title')
{{$url->hash}}
@endsection

@section('content')
    <div class="row">
        Временной график количества переходов для <strong>{{$url->hash}}</strong> ---> <strong>{{$url->url}}</strong>
    </div>
    <div class="divider"></div>
    <div class="row"></div>
    <div class="row">
        @if(count($redirects) === 0)
            <strong>Переходы на заданный временной промежуток отсутствуют</strong>
        @else
        <table class="responsive-table bordered highlight">
            <thead>
                <tr>
                    <th>Временной промежуток</th> <th>Количество переходов</th>
                </tr>
            </thead>
            <tbody>
                @if($params['sort'] === 3600*24)
                    @foreach($redirects as $red)
                        @if($red->time < $params['from'])
                        @else
                        <tr>
                            <td><strong>C {{date('j M', $red->time)}}</strong> по <strong>{{date('j M', $red->time + $params['sort'])}}</strong></td> <td>{{$red->num}}</td>
                        </tr>
                        @endif
                    @endforeach
                @else
                    @foreach($redirects as $red)
                        @if($loop->first)
                            <tr>
                                <th>{{date('D, d M', $red->time)}}</th> <td></td>
                            </tr>
                            <?php $currentday = $red->time; ?>
                        @endif
                        @if( date('m d', $red->time) === date('m d', $currentday) )
                        <tr>
                            <td>C {{date('G:i', $red->time)}} до {{date('G:i', $red->time + $params['sort'])}}</td> <td>{{$red->num}}</td>
                        </tr>
                        @else
                            <tr>
                                <th>{{date('D, d M', $red->time)}}</th> <td></td>
                            </tr>
                            <tr>
                                <td>C {{date('G:i', $red->time)}} до {{date('G:i', $red->time + $params['sort'])}}</td> <td>{{$red->num}}</td>
                            </tr>
                            <?php $currentday = $red->time; ?>
                        @endif
                    @endforeach
                @endif
            </tbody>
        </table>
        @endif
    </div>
    <div class="row"></div>

    <div class="row">
        <div class="col s3 ">
            <a class="btn deep-purple lighten-2" href="{{route('shorten_urls.show', ['id' => $url->id])}}">Вернуться</a>
        </div>
        <div class="col s5">
            <a class="btn orange lighten-1" href="{{route('url.referers', ['id' => $url->id])}}">Смотреть топ источников</a>
        </div>
        <div class="col s4 ">
            <a class="btn blue" href="{{route('shorten_urls.index')}}">Смотреть ссылки</a>
        </div>
    </div>
@endsection