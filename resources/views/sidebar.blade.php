
<ul id="slide-out" class="side-nav">
    @if (Auth::check())
        <li><a href="{{route('shorten_urls.index')}}">Мои ссылки</a></li>
        <li><a href="{{route('users.me')}}">Обо мне</a></li>
        <li><a href="{{route('shorten_urls.create')}}">Создать</a></li>
    @else
        <li><a href="{{route('users.create')}}">Регистрация</a></li>
    @endif
</ul>
