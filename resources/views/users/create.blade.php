@extends('head')
@section('title')
    Регистрация
@endsection

@section('content')
    <div class="row">
       <form action="{{route('users.store')}}" method="POST">
           <div class="row">

               <div class="col s4">
                   @if ($errors->has('name'))
                       <span class="help-block">
                        <strong>{{ $errors->first('name') }}</strong>
                    </span>
                   @endif
                   <input type="text" name="name" value="{{ old('name') }}" required autofocus placeholder="Name">
               </div>

               <div class="col s4">
                   @if ($errors->has('email'))
                       <span class="help-block">
                            <strong>{{ $errors->first('email') }}</strong>
                        </span>
                   @endif
                   <input type="email" name="email" value="{{ old('email') }}" required placeholder="email@example.com">
               </div>
           </div>

           <div class="row">
               <div class="col s4">
                   @if ($errors->has('password'))
                   <span class="help-block">
                        <strong>{{ $errors->first('password') }}</strong>
                    </span>
                   @endif
                   <input type="password" name="password" required placeholder="password">
               </div>

               <div class="col s4">
                   <input type="password" name="password_confirmation" required placeholder="confirm password">
               </div>
           </div>

           <div class="row">
               <a class="btn submit blue">Зарегистрироваться</a>
           </div>

       </form>
    </div>

@endsection