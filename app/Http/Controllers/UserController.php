<?php

namespace App\Http\Controllers;

use App\User;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Exception;

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

    /**
     * Информация о кодах http состояний
     *
     * @var array
     */
    public $status = [
        201 => ['status' => '201 Created', 'msg' => ''],
        403 => ['status' => '403 Forbidden', 'msg' => 'Запрещен доступ к чужому ресурсу'],
        404 => ['status' => '404 Not Found', 'msg' => 'Запрашиваемый ресурс не существует'],
        409 => ['status' => '409 Conflict', 'msg' => ''],
        417 => ['status' => '417 Excpectation Failed', 'msg' => ''],
        500 => ['status' => '500 Internal Server Error', 'msg' => 'Сервер временно недоступен'],
        501 => ['status' => '501 Not Implemented', 'msg' => 'Сервер не смог выполнить действие']
    ];

    /**
     * Поля с входящими данными, ожидаемые при регистрации пользователя
     *
     * @var array
     */
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
        return old('status') === null ? $view : new Response($view, old('status'));
    }

    /**
     * Метод регистрирует нового пользователя
     * В случае успеха возвращает на страницу регистрации со статусом 201
     * POST /api/v1/users
     *
     * @HttpStatus:
     * 201 - успех
     * 417 - некорректные входящие данные
     * 501 - БД не выполнила запрос
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
            return redirect()->back()->withErrors($validator)->withInput($this->statusArray(417));
        }

        unset($data['password_confirm']);
        $data['name'] = htmlspecialchars($data['name']);
        $data['password'] = Hash::make($request->password);

        try {

            DB::transaction(function () use ($data) {

                $user = User::create($data);

                if ($user->exists === true)
                {
                    throw new Exception();
                }

            });

        } catch (Exception $e)

        {
            $this->returnError($e->getMessage(), $this->getStatus(501));
        }

        return redirect()->back()->withInput($this->statusArray(201));

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
            return $this->returnError($this->getMsg(404), $this->getStatus(404));

        } elseif ($user->id !== Auth::id())
        {
            return $this->returnError($this->getMsg(403), $this->getStatus(403));
        }

        $view =  view('users.show', ['user' => $user]);

        return old('status') === null ? $view : new Response($view, old('status'));
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

    /**
     * Метод возвращает страницу ошибки с сообщением и статусом
     *
     * @param string $msg
     * @param string $status
     * @return Response
     */
    private function returnError($msg, $status)
    {
        return new Response(view('errors.error', ['error' => $msg]), $status);
    }

    /**
     * Метод возвращает полное название статуса из массива статусов
     *
     * @param int $status
     * @return string
     */
    private function getStatus($status)
    {
        return $this->status[$status]['status'];
    }

    /**
     * Метод возвращает статус в виде именованного массива
     *
     * @param int $status
     * @return array
     */
    private function statusArray($status)
    {
        return ['status' => $this->status[$status]['status']];
    }

    /**
     * Метод возвращает сообщение, связанное со статусом
     *
     * @param int $status
     * @return sting
     */
    private function getMsg($status)
    {
        return $this->status[$status]['msg'];
    }
}
