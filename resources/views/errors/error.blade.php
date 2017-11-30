@extends('head')

@section('content')
    @if (isset($error))
        <span class="help-block">
                <strong>{{$error}}</strong>
        </span>
    @endif

    @if(count($errors) > 0)
        <span class="help-block">
            @foreach($errors as $error)
                <strong>{{$error}}</strong>
            @endforeach
        </span>
    @endif
@endsection