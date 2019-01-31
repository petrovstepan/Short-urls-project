<?php

namespace App\Http\Controllers;

use App\Referer;
use App\RefererUrlRelation;
use App\ShortenUrl;
use App\User;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Exception;

class ShortenUrlController extends Controller
{
    public $rules = [
        'dates' => ['from_date', 'to_date'],
        'store' => ['url' => 'required|url|unique:shorten_urls'],
        'hash' => ['hash' => 'unique:shorten_urls'],
        'sort' => ['minute' => 60, 'hour' => 3600, 'day' => 3600*24],
        'report' => [
            'sort' => ['required', 'regex:/(minute|hour|day)/'],
            'from_date' => ['required', 'date', 'before:to_date'],
            'to_date' => ['required', 'date', 'after:from_date']
        ]
    ];

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
     * Метод отображает список из всех созданных ссылок залогиненого пользователя
     * GET /api/v1/users/me/shorten_urls
     *
     * @return View (string)
     */
    public function index()
    {
        $user = Auth::user();
        $urls = $user->urls;
        return view('urls.index', ['urls' => $urls]);
    }

    /**
     * Метод отображает форму создания короткой ссылки
     * GET /api/v1/users/me/shorten_urls/create
     *
     * @return View | Response
     */
    public function create()
    {
        $view = view('urls.create');
        return old('status') === null ? $view : new Response($view, old('status'));
    }


    /**
     * Метод сохраняет короткую ссылку пользователя
     * Отправляет на страницу созданной ссылки, или возвращает обратно к форме
     * POST /api/v1/users/me/shorten_urls
     *
     * @HttpStatus:
     * 201 - успех
     * 409 - ресурс с таким именем уже существует
     * 417 - некорректные входящие данные
     * 501 - БД не создала новую запись
     *
     * @param Request $request [string $url]
     *
     * @return Redirect
     */
    public function store(Request $request)
    {
        $data = $request->only('url');

        $validator = Validator::make($data, $this->rules['store']);

        if ($validator->fails() === true)
        {
            $st = $this->checkUnique($validator->errors()->first()) ? 417 : 409;
            return redirect()->back()->withErrors($validator)->withInput($this->statusArray($st));
        }

        do
        {
            $data['hash'] = Str::random(8);
            $validator = Validator::make($data, $this->rules['hash']);

        } while ($validator->fails() === true);


        try {

            DB::transaction(function () use ($data) {

                $url = Auth::user()->urls()->create($data);

                if ($url->exists !== true)
                {
                    throw new Exception($this->getMsg(501));
                }

            });

        } catch (Exception $e)

        {
            return $this->returnError($e->getMessage(), $this->getStatus(501));
        }

        $url = ShortenUrl::findByParam($data);

        return redirect()->route('shorten_urls.show', ['id' => $url->id])->withInput($this->statusArray(201));
    }


    /**
     * Метод отображает информацию о конкретной ссылке пользователя
     * GET /api/v1/users/me/shorten_urls/{id}
     *
     * @HttpStatus:
     * 200 - успех
     * 403 - ресурс не принадлежит пользователю
     * 404 - ресурса не существует
     *
     * @param int $id
     *
     * @return View | Response
     */
    public function show($id)
    {
        if ((($status = $this->usersUrlExist(Auth::user(), $id)) instanceof ShortenUrl) === false)
        {
            return $this->returnError($this->getMsg($status), $this->getStatus($status));
        }

        $id = (int) $id;
        $url = Auth::user()->urls()->whereId($id)->withCount('follows')->first();

        $view = view('urls.show', ['url' => $url]);

        return old('status') === null ? $view : new Response($view, old('status'));
    }

    /**
     * Метод удаляет ссылку пользователя
     * Редирект обратно, если есть http_referer, иначе страница ошибки
     * DELETE /api/v1/users/me/shorten_urls/{id}
     *
     * @HttpStatus:
     * 200 - успех
     * 403 - ресурс не принадлежит пользователю
     * 404 - ресурса не существует
     * 501 - не удалось удалить ресурс
     *
     * @param int $id
     *
     * @return Redirect
     */
    public function destroy($id)
    {
        if ((($urlOrStatus = $this->usersUrlExist(Auth::user(), $id)) instanceof ShortenUrl) === false)
        {
            return redirect()->back()->withErrors($this->msgArray($urlOrStatus))->withInput($this->statusArray($urlOrStatus));
        }

        try {

            DB::transaction(function () use ($urlOrStatus) {

                $urlOrStatus->delete();

                if ($urlOrStatus->exists !== false)
                {
                    throw new Exception($this->getMsg(501));
                }

            });

        } catch (Exception $e)

        {
            return $this->returnError($e->getMessage(), $this->getStatus(501));
        }

        return redirect()->route('shorten_urls.index');
    }

    /**
     * Метод формирует отчет для конкретной ссылки пользователя с временным графиком переходов
     * GET /api/v1/users/me/shorten_urls/{id}/{sort}?from_date=0000-00-00&to_date=0000-00-00
     *
     * @HttpStatus:
     * 200 - успех
     * 403 - ресурс не принадлежит пользователю
     * 404 - ресурса не существует
     * 417 - некорректные входящие данные
     *
     * @param Request $request [string(date: 0000-00-00) $from_date, string(date: 0000-00-00) $to_date]
     * @param int $id
     * @param string $sort [minute | hour | day]
     *
     * @return View | Redirect
     */
    public function urlReport(Request $request, $id, $sort)
    {
        if ((($status = $this->usersUrlExist(Auth::user(), $id)) instanceof ShortenUrl) === false)
        {
            return $request->headers->get('referer') === null ?
                        $this->returnError($this->getMsg($status), $this->getStatus($status)) :
                        redirect()->back()->withErrors($this->msgArray($status))->withInput($this->statusArray($status));
        }

        $data = $request->only($this->rules['dates']);
        $data['sort'] = $sort;

        $validator = Validator::make($data, $this->rules['report']);

        if ($validator->fails() === true)
        {
            $status = 417;

            return $request->headers->get('referer') === null ?
                $this->returnError($validator->errors()->first(), $this->getStatus($status)) :
                redirect()->back()->withErrors($validator)->withInput($this->statusArray($status));
        }

        foreach ($data as &$value)
        {
            $value = strtotime($value);
        }
        unset($value, $data['sort']);

        $id = (int) $id;
        $redirects = RefererUrlRelation::getGroupedRedirects($id, $this->rules['sort'][$sort], $data['from_date'], $data['to_date']);

        return view('urls.reports.timecounts', [
            'redirects' => $redirects,
                'params' => [
                    'from' => $data['from_date'],
                    'to' => $data['to_date'],
                    'sort' => $this->rules['sort'][$sort],
                ],
            'url' => Auth::user()->urls()->whereId($id)->first()
        ]);
    }

    /**
     * Метод формирует отчет, содержащий топ-20 источников переходов для конркретной ссылки пользователя
     * GET /api/v1/users/me/shorten_urls/{id}/referers
     *
     * @HttpStatus:
     * 200 - успех
     * 403 - ресурс не принадлежит пользователю
     * 404 - ресурса не существует
     *
     * @param int $id
     * @return View
     */
    public function urlReferers($id)
    {
        if ((($status = $this->usersUrlExist(Auth::user(), $id)) instanceof ShortenUrl) === false)
        {
            return $this->returnError($this->getMsg($status), $this->getStatus($status));
        }

        $id = (int) $id;

        $referers = RefererUrlRelation::getTopRedirects($id, 20);

        return view('urls.reports.referers', ['referers' => $referers, 'url' => Auth::user()->urls()->whereId($id)->first()]);
    }

    /**
     * Метод производит редирект по короткой ссылке, добавляет информацию о переходе в БД
     * GET /api/v1/shorten_urls/{hash}
     *
     * @HttpStatus:
     * 302 - успех
     * 404 - ресурса не существует
     * 500 - ошибка сервера
     *
     * @param Request $request
     * @param string $hash
     * @return Redirect
     */
    public function redirectHash(Request $request, $hash)
    {
        $url = ShortenUrl::findByParam(['hash' => $hash]);

        if ($url === null)
        {
            return $this->returnError($this->getMsg(404), $this->getStatus(404));
        }

        $refererUrl = $request->headers->get('referer') === null ? 'direct_transition' : $request->headers->get('referer');

        try {

            DB::transaction(function () use ($refererUrl, $url) {

                $referer = Referer::firstOrCreate(['referer' => $refererUrl]);

                $relation = RefererUrlRelation::create([
                    'referer_id' => $referer->id,
                    'url_id' => $url->id
                ]);

                if ($referer->exists !== true || $relation->exists !== true)
                {
                    throw new Exception($this->getMsg(500));
                }

            });

        } catch (Exception $e)

        {
            return $this->returnError($e->getMessage(), $this->getStatus(500));
        }

        return redirect($url->url, 302);
    }

    /**
     * Метод ищет конкретный url по $id среди всех url конкретного пользователя $user
     * Формирует массив с ошибкой и статусом:
     * 403 - ресурс не принадлежит пользователю
     * 404 - ресурс не существует
     *
     * @param int $id
     * @return ShortenUrl | int (status)
     */
    private function usersUrlExist(User $user, $id)
    {
        $id = (int) $id;
        $userUrl = $user->urls()->whereId($id)->first();

        if ($userUrl === null) {

            $url = ShortenUrl::find($id);
            $status = $url === null ? 404 : 403;
            return $status;
        }

        return $userUrl;
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

    /**
     * Метод возвращает сообщение в виде именованного массива
     *
     * @param int $status
     * @return array
     */
    private function msgArray($status)
    {
        return ['error' => $this->getMsg($status)];
    }

    /**
     * Метод проверяет, нашел ли валидатор ошибку уникальности добавляемого url адреса
     *
     * @param string $errorString
     * @return bool
     */
    private function checkUnique($errorString)
    {
        return strpos($errorString, 'taken') === false;
    }
}
