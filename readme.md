Проект Веб-приложение для сокращения ссылок

Требования: PHP 5.6+ | MySql | Composer | Веб-сервер

Данные для входа: email - admin@ya.com  Пароль - Adminpass

Инструкция для запуска:

1 Загрузите репозиторий на свой компьютер, поместив его в директорию, на которую настроен Ваш веб-сервер как localhost

2 Загрузите необходимые зависимости с помощью команды composer update, используя терминал

3 Создайте файл .env (используйте .env.example) и внесите в него следующие настройки:
- Данные для подключения к БД 
- Создайте виртуальный хост и укажите url приложения. Точка входа находится в папке public
- Установите ключ приложения APP_KEY=base64:M5vIVhKpO+81ph5nGGAnYlG1FW/K2bMG23OORHxS6ew=

4 Подготовьте БД, таблицы и содержимое:
- Сделайте это с помощью зарание подготовленных миграций: используя терминал, в директории проекта, выполните следующие команды: 
  php artisan migrate - создает необходимые таблицы в указанной в файле .env базе данных
  php artisan db:seed - заполняет таблицы начальными данными. ВНИМАНИЕ - в процессе заполнения в таблицу переходов добавляется 
    10 000 записей, для большей наглядности отчетов. Если для Вас это много, то измените это значение в файле
    database/seeds/FillRefererUrlRelationsTable.php. Для отката миграций используйте команду php artisan migrate:rollback
- Или сделайте это, импортировав файл shorturls.sql:
  В этом случае убедитесь, что в процессе импорта у таблиц не были потеряны первичные ключи - все поля id должны быть первичными ключами в
  режиме auto_increament

5 Запустите приложение

С чего начать?
* Приложение использует HTTP авторизацю, поэтому если зайти под одним пользователем, то выйти сразу не получится, придется использовать     другой браузер.
* Для не аутентифицированных пользователей доступен маршрут /register, туда же вас перенаправит и корневой марщрут /.
* Если вы хотите сразу пройти аутентификацию, то используйте маршрут /start.
* В БД предустановлено 3 пользователя, Admin, Alex, John. Используйте их данные, чтобы войти, например:
* Email - admin@ya.com  --- для входа используется email
* Пароль - Adminpass    --- по этой логике формируются пароли и для остальных пользователей.
* После входа вы будете автоматически перенаправлены на страницу пользователя.

Особенности:
- Приложение настроено таким образом, чтобы отлавливать переходы по несуществующим маршрутам и направлять их на корневой маршрут /.
- Все исключения типа PDOException отлавливаются и возвращают Http статус 500. При правильной настройке БД их не должно возникать.
- Отключить эти особенности можно в файле app/Exceptions/Handler.php
- Все доступные маршруты приложения вы всегда можете увидеть с помощью команды php artisan route:list 
