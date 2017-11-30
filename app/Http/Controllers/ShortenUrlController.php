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
use Mockery\Exception;

class ShortenUrlController extends Controller
{
    public $contrName;

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

    public function __construct()
    {
        $this->contrName = $this->makeControllerName();
    }

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
     * @return View (string)
     */
    public function create()
    {
        $view = view('urls.create');
        return (old('status') === null) ? $view : new Response($view, old('status'));
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
            $unique = (strpos($validator->errors()->all()[0], 'taken') === false);
            $st = $unique ? '417 Expectation Failed' : '409 Conflict';
            return redirect()->back()->withErrors($validator)->withInput(['status' => $st]);
        }

        do
        {
            $data['hash'] = Str::random(8);
            $validator = Validator::make($data, $this->rules['hash']);

        } while ($validator->fails() === true);

        $url = Auth::user()->urls()->create($data);

        return redirect()->action("{$this->contrName}@show", ['id' => $url->id])->withInput(['status' => '201 Created']);
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
        $id = (int) $id;
        $url = Auth::user()->urls()->whereId($id)->withCount('follows')->first();

        $expect = $url !== null;

        if ($expect === false)
        {
            $belongsToOtherUser = ShortenUrl::find($id) !== null;
            $st = $belongsToOtherUser ? '403 Forbidden' : '404 Not Found';
            $err = $belongsToOtherUser ? 'Запрещен доступ к чужому ресурсу' : 'Запрашиваемый ресурс не существует';
            return new Response(view('errors.error', ['error' => $err]), $st);
        }

        $view = view('urls.show', ['url' => $url]);

        return (old('status') === null) ? $view : new Response($view, old('status'));
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
     * @param Request $request [http_referer]
     * @param int $id
     *
     * @return Redirect | Response
     */
    public function destroy(Request $request, $id)
    {
        if ( (($userUrl = $this->usersUrlExist(Auth::user(), $id)) instanceof ShortenUrl) === false)
        {
            return ($request->headers->get('referer') === null) ?
                    new Response(view('errors.error', ['error' => $err[0]])) :
                    redirect()->back()->withErrors($userUrl['er'])->withInput($userUrl['st']);
        }


        try {

            DB::transaction(function () use ($userUrl) {
                $affected = $userUrl->delete();

                $expect1 = $affected === true;
                $expect2 = $userUrl->exists === false;

                if (($expect1 && $expect2) !== true)
                {
                    throw new Exception('Сервер не смог выполнить действие');
                }

            });

        } catch (Exception $e)

        {
            $err = $e->getMessage();
            return new Response(view('errors.error', ['error' => $err]), '501 Not Implemented');
        }

        return redirect()->action("{$this->contrName}@index");
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
     * @param Request $request
     * @param int $id
     * @param string $sort [minute | hour | day]
     * @param string(date: 0000-00-00) $from_date
     * @param string(date: 0000-00-00) $to_date
     *
     * @return View | Redirect | Response
     */
    public function urlReport(Request $request, $id, $sort)
    {
        $data = $request->only($this->rules['dates']);
        $data['sort'] = $sort;

        $id = (int) $id;
        $userUrl = Auth::user()->urls()->whereId($id)->first();

        if ($userUrl === null)
        {
            $url = ShortenUrl::find($id);

            $st = ($url === null) ? '404 Not Found' : '403 Forbidden';
            $err['error'] = ($url === null) ? 'Запрашиваемый ресурс не существует' : 'Запрещен доступ к чужому ресурсу';

            return ($request->headers->get('referer') === null) ?
                    new Response(view('errors.error', $err), $st) :
                    redirect()->back()->withErrors($err)->withInput(['status' => $st]);
        }

        $validator = Validator::make($data, $this->rules['report']);

        if ($validator->fails() === true)
        {
            $st = '417 Expectation Failed';

            return ($request->headers->get('referer') === null) ?
                new Response(view('errors.error', ['error' => $validator->errors()->first()]), $st) :
                redirect()->back()->withErrors($validator)->withInput(['status' => $st]);
        }

        foreach ($data as &$value)
        {
            $value = strtotime($value);
        }
        unset($value, $data['sort']);


        $redirects = RefererUrlRelation::getGroupedRedirects($id, $this->rules['sort'][$sort], $data['from_date'], $data['to_date']);

        return view('urls.reports.timecounts', [
            'redirects' => $redirects,
                'params' => [
                    'from' => $data['from_date'],
                    'to' => $data['to_date'],
                    'sort' => $this->rules['sort'][$sort],
                ],
            'url' => $userUrl
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
     * @return View | Response
     */
    public function urlReferers($id)
    {
        $id = (int) $id;
        $userUrl = Auth::user()->urls()->whereId($id)->first();

        if ($userUrl === null)
        {
            $url = ShortenUrl::find($id);

            $st = ($url === null) ? '404 Not Found' : '403 Forbidden';
            $err = ($url === null) ? 'Запрашиваемый ресурс не существует' : 'Запрещен доступ к чужому ресурсу';

            return new Response(view('errors.error', ['error' => $err]), $st);
        }

        $referers = RefererUrlRelation::getTopRedirects($id, 20);

        return view('urls.reports.referers', ['referers' => $referers, 'url' => $userUrl]);

    }

    /**
     * Метод производит редирект по короткой ссылке, добавляет информацию о переходе в БД
     * GET /api/v1/shorten_urls/{hash}
     *
     * @HttpStatus:
     * 302 - успех
     * 404 - ресурса не существует
     *
     * @param Request $request
     * @param string $hash [a-zA=Z0-9]{8}
     * @return Redirect | Response
     */
    public function redirectHash(Request $request, $hash)
    {
        $url = ShortenUrl::findByParam(['hash' => $hash]);

        if ($url === null)
        {
            return new Response(view('errors.error', ['error' => 'Адреса не существует']), '404 Not Found');
        }

        $refererUrl = ($request->headers->get('referer') === null) ? 'direct_transition' : $request->headers->get('referer');

        $referer = Referer::firstOrCreate(['referer' => $refererUrl]);

        RefererUrlRelation::create([
            'referer_id' => $referer->id,
            'url_id' => $url->id
            ]);

        return redirect($url->url);
    }

    /**
     * Метод ищет конкретный url по $id среди всех url конкретного пользователя $user
     * Формирует массив с ошибкой и статусом:
     * 403 - ресурс не принадлежит пользователю
     * 404 - ресурс не существует
     *
     * @param int $id
     * @return ShortenUrl | array
     */
    private function usersUrlExist(User $user, $id)
    {
        $id = (int)$id;

        $userUrl = $user->urls()->whereId($id)->first();

        if ($userUrl === null) {
            $url = ShortenUrl::find($id);

            $arr['st']['status'] = ($url === null) ? '404 Not Found' : '403 Forbidden';
            $arr['er']['error'] = ($url === null) ? 'Запрашиваемый ресурс не существует' : 'Запрещен доступ к чужому ресурсу';

            return $arr;
        }

        return $userUrl;
    }

    /**
     * Метод возвращает название класса контроллера без полного namespace
     *
     * @return string
     */
    private function makeControllerName()
    {
        $array = explode('\\', __CLASS__);
        return array_pop($array);
    }
}
