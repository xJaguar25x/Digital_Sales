<?php
/**
 * Created by PhpStorm.
 * User: Jaguar25
 * Date: 09.03.2018
 * Time: 19:04
 */
#Массив с параметрами, которые нужно передать методом POST к API системы
$user=array(
    'USER_LOGIN'=>'amocrmsystem@gmail.com', #Ваш логин (электронная почта)
    'USER_HASH'=>'a6515465dd6e044348a5684bf2933677', #Хэш для доступа к API (смотрите в профиле пользователя)
);
$subdomain='testirovaniyakk'; #Наш аккаунт - поддомен
#Формируем ссылку для запроса
$link='https://'.$subdomain.'.amocrm.ru/private/api/auth.php?type=json';
$curl=curl_init(); #Сохраняем дескриптор сеанса cURL

#Устанавливаем необходимые опции для сеанса cURL

//TRUE для возврата результата передачи в качестве строки из curl_exec() вместо прямого вывода в браузер.
curl_setopt($curl,CURLOPT_RETURNTRANSFER,true);

//Содержимое заголовка "User-Agent: ", посылаемого в HTTP-запросе.
curl_setopt($curl,CURLOPT_USERAGENT,'amoCRM-API-client/1.0');

//Загружаемый URL. Данный параметр может быть также установлен при инициализации сеанса с помощью curl_init().
curl_setopt($curl,CURLOPT_URL,$link);

//Собственный метод запроса, используемый вместо "GET" или "HEAD" при выполнении HTTP-запроса. Это полезно при запросах "DELETE" или других, более редких HTTP-запросах.
curl_setopt($curl,CURLOPT_CUSTOMREQUEST,'POST');

//Все данные, передаваемые в HTTP POST-запросе.
curl_setopt($curl,CURLOPT_POSTFIELDS,json_encode($user));

//Массив устанавливаемых HTTP-заголовков, в формате array('Content-type: text/plain', 'Content-length: 100')
curl_setopt($curl,CURLOPT_HTTPHEADER,array('Content-Type: application/json'));

//TRUE для включения заголовков в вывод.
curl_setopt($curl,CURLOPT_HEADER,false);

//Имя файла, содержащего cookies. Данный файл должен быть в формате Netscape или просто заголовками HTTP, записанными в файл. Если в качестве имени файла передана пустая строка, то cookies сохраняться не будут, но их обработка все еще будет включена.
curl_setopt($curl,CURLOPT_COOKIEFILE,dirname(__FILE__).'/cookie.txt'); #PHP>5.3.6 dirname(__FILE__) -> __DIR__

//Имя файла, в котором будут сохранены все внутренние cookies текущей передачи после закрытия дескриптора, например, после вызова curl_close.
curl_setopt($curl,CURLOPT_COOKIEJAR,dirname(__FILE__).'/cookie.txt'); #PHP>5.3.6 dirname(__FILE__) -> __DIR__

//FALSE для остановки cURL от проверки сертификата узла сети.
curl_setopt($curl,CURLOPT_SSL_VERIFYPEER,0);

//Используйте 1 для проверки существования общего имени в сертификате SSL. Используйте 2 для проверки существования общего имени и также его совпадения с указанным хостом. 0 чтобы не проверять имена. В боевом окружении значение этого параметра должно быть 2 (установлено по умолчанию).
curl_setopt($curl,CURLOPT_SSL_VERIFYHOST,0);

$out=curl_exec($curl); #Инициируем запрос к API и сохраняем ответ в переменную
$code=curl_getinfo($curl,CURLINFO_HTTP_CODE); #Получим HTTP-код ответа сервера
curl_close($curl); #Завершаем сеанс cURL
/* Теперь мы можем обработать ответ, полученный от сервера. Это пример. Вы можете обработать данные своим способом. */
$code=(int)$code;
$errors=array(
    301=>'Moved permanently',
    400=>'Bad request',
    401=>'Unauthorized',
    403=>'Forbidden',
    404=>'Not found',
    500=>'Internal server error',
    502=>'Bad gateway',
    503=>'Service unavailable'
);
try
{
    #Если код ответа не равен 200 или 204 - возвращаем сообщение об ошибке
    if($code!=200 && $code!=204)
        throw new Exception(isset($errors[$code]) ? $errors[$code] : 'Undescribed error',$code);
}
catch(Exception $E)
{
    die('Ошибка: '.$E->getMessage().PHP_EOL.'Код ошибки: '.$E->getCode());
}
/*
 Данные получаем в формате JSON, поэтому, для получения читаемых данных,
 нам придётся перевести ответ в формат, понятный PHP
 */
$Response=json_decode($out,true);
$Response=$Response['response'];
if(isset($Response['auth'])) #Флаг авторизации доступен в свойстве "auth"
    return 'Авторизация прошла успешно';
return 'Авторизация не удалась';