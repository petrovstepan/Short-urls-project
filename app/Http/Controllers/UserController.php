<?php

namespace App\Http\Controllers;

use App\User;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class UserController extends Controller
{
    /**
     * Правила для валидации входящих данных
     *
     * @var array
     */
    public $rules = [
        'name' => 'required|string|max:255',
        'email' => 'required|email|unique:users|max:255',
        'password' => 'required|min:6|confirmed'
    ];

    public $regParams = ['name', 'email', 'password', 'password_confirmation'];

    /**
     * Метод отображает форму регистрации пользователя
     * GET /api/v1/users/create
     *
     * @return View (string)
     */
    public function create()
    {
        $view = view('users.create');
        return (old('status') === null) ? $view : new Response($view, old('status'));
    }

    /**
     * Метод регистрирует нового пользователя
     * В случае успеха производится принудительный логин и редирект на страницу пользователя
     * POST /api/v1/users
     *
     * @HttpStatus:
     * 201 - успех
     * 417 - некорректные входящие данные
     *
     * @param Request $request
     * @return Redirect
     */
    public function store(Request $request)
    {
        $data = $request->only($this->regParams);

        $validator = Validator::make($data, $this->rules);

        if ($validator->fails() === true)
        {
            return redirect()->back()->withErrors($validator)->withInput(['status' => '417 Expectation Failed']);
        }

        unset($data['password_confirm']);
        $data['name'] = htmlspecialchars($data['name']);
        $data['password'] = Hash::make($request->password);

        $user = User::create($data);
        Auth::login($user);

        return redirect()->route('users.me')->withInput(['status' => '201 Created']);

    }

    /**
     * Метод отображает страницу пользователя по его $id
     *
     * @HttpStatus:
     * 200 -успех
     * 403 - ресурс не принадлежит пользователю
     * 404 - ресурс не существует
     *
     * @param int $id
     * @return View | Response
     */
    public function show($id)
    {
        $id = (int) $id;
        $user = new User();

        $user = $user->whereId($id)
            ->with([
                'urls' => function ($query) {
                    $query->withCount('follows')
                        ->orderBy('follows_count', 'desc')
                        ->limit(1);
            }])
            ->withCount('urls')
            ->first();

        if ($user === null)
        {
            return new Response(view('errors.error', ['error' => 'Запрашиваемый ресурс не существует']), '404 Not Found');

        } elseif ($user->id !== Auth::id())
        {
            return new Response(view('errors.error', ['error' => 'Запрещен доступ к чужому ресурсу']), '403 Forbidden');
        }

        $view =  view('users.show', ['user' => $user]);

        return (old('status') === null) ? $view : new Response($view, old('status'));
    }


    /**
     * Метод отображает страницу залогиненного пользователя
     * GET /api/v1/users/me
     *
     * @return mixed
     */
    public function showMe()
    {
        return $this->show(Auth::id());
    }
}
