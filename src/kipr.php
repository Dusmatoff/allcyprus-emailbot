<?php
//require_once("includes/header.php");

/* Get variables from post */
$months = $_POST['contact']['fields']['month'];
$date_min = $_POST['contact']['fields']['date_min'];
$date_max = $_POST['contact']['fields']['date_max'];
$adults = empty($_POST['contact']['fields']['adults']) ? 2 : $_POST['contact']['fields']['adults']; //Количество взрослых
$childs = empty($_POST['contact']['fields']['childs']) ? 0 : $_POST['contact']['fields']['childs']; //Количество детей (всегда отправляем возраст 5 лет)
$daysMin = $_POST['contact']['fields']['days_min']; //Ночей, от
$daysMax = $_POST['contact']['fields']['days_max']; //Ночей, до
$meals = $_POST['contact']['fields']['food']; //Питание: Любое, Без питания - AO(id-10), Только завтрак - BB(id-4), Завтрак и ужин - HB(id-2), HB (id-40), Завтрак, обед и ужин - FB(id-1), Все включено - AI(id-11), Ультра всё включено - UAL(id-38), PAL(id-39)
$hotelStars = $_POST['contact']['fields']['hotel_stars']; //Звездность отеля 2*, 3*, 4*, 5*
$priceMin = (int)$_POST['contact']['fields']['price_min']; //Цена от
$priceMax = (int)$_POST['contact']['fields']['price_max']; //Цена до
$resort = $_POST['contact']['fields']['resort']; //Курорт Айя-Напа - 224, Лимассол - 223, Пафос - 222, Протарас - 225, Ларнака - 47
$hotelName = null; //Фильтр по названию отеля
$id = $_POST['contact']['id'];
$email = $_POST['contact']['email'];
/*$from_city = $_POST['contact']['fields']['from_city'];
$tags = $_POST['contact']['tags'];
$first_name = $_POST['contact']['first_name'];
$last_name = $_POST['contact']['last_name'];
$phone = $_POST['contact']['phone'];
$orgname = $_POST['contact']['orgname'];
$customer_acct_name = $_POST['contact']['customer_acct_name'];
$ip4 = $_POST['contact']['ip4'];
$interests = $_POST['contact']['fields']['interests'];
$offers = $_POST['contact']['fields']['offers'];
$pause = $_POST['contact']['fields']['pause'];
$status = $_POST['contact']['fields']['status'];
$visa = $_POST['contact']['fields']['visa'];*/

/*$post = file_get_contents('php://input');
$data = urldecode($post);
$file = 'test.txt';
$file1 = 'test1.txt';
file_put_contents($file, $date_min);
file_put_contents($file1, $date_max);
die();*/

$daysRange = $daysMin . "," . $daysMax;

if (!empty($date_min) && !empty($date_max)) {
    //$dateRange = (string)$date_min . ',' . (string)$date_max;
    $dateRange = "'" . $date_min . "','" . $date_max . "'";
} else {
    $currentYear = date('Y');
    $currentDate = date('d.m.Y');
    $dateRange = "'" . $currentDate . "','31.12." . $currentYear . "'";
    if ($months != '||Любой месяц||') {
        $monthArray = explode("||", $months);
        if (in_array('Январь', $monthArray)) {
            $dateRange = "'" . $currentDate . "','31.01." . $currentYear . "'";
        }
        if (in_array('Февраль', $monthArray)) {
            $dateRange = "'" . $currentDate . "','29.02." . $currentYear . "'";
        }
        if (in_array('Март', $monthArray)) {
            $dateRange = "'" . $currentDate . "','31.03." . $currentYear . "'";
        }
        if (in_array('Апрель', $monthArray)) {
            $dateRange = "'" . $currentDate . "','30.04." . $currentYear . "'";
        }
        if (in_array('Май', $monthArray)) {
            $dateRange = "'" . $currentDate . "','31.05." . $currentYear . "'";
        }
        if (in_array('Июнь', $monthArray)) {
            $dateRange = "'" . $currentDate . "','30.06." . $currentYear . "'";
        }
        if (in_array('Июль', $monthArray)) {
            $dateRange = "'" . $currentDate . "','31.07." . $currentYear . "'";
        }
        if (in_array('Август', $monthArray)) {
            $dateRange = "'" . $currentDate . "','31.08." . $currentYear . "'";
        }
        if (in_array('Сентябрь', $monthArray)) {
            $dateRange = "'" . $currentDate . "','30.09." . $currentYear . "'";
        }
        if (in_array('Октябрь', $monthArray)) {
            $dateRange = "'" . $currentDate . "','31.10." . $currentYear . "'";
        }
        if (in_array('Ноябрь', $monthArray)) {
            $dateRange = "'" . $currentDate . "','30.11." . $currentYear . "'";
        }
        if (in_array('Декабрь', $monthArray)) {
            $dateRange = "'" . $currentDate . "','31.12." . $currentYear . "'";
        }
    }
}


$foods = [];
$foodsArray = explode("||", $meals);
if (in_array('Без питания', $foodsArray)) {
    array_push($foods, 10);
}
if (in_array('Только завтрак', $foodsArray)) {
    array_push($foods, 4);
}
if (in_array('Завтрак и ужин', $foodsArray)) {
    array_push($foods, 2);
}
if (in_array('Завтрак, обед и ужин', $foodsArray)) {
    array_push($foods, 1);
}
if (in_array('Все включено', $foodsArray)) {
    array_push($foods, 11);
}
if (in_array('Ультра всё включено', $foodsArray)) {
    array_push($foods, 38);
}
$allFoods = implode(", ", $foods);

$cities = [];
$citiesArray = explode("||", $resort);
if (in_array('Айя-Напа', $citiesArray)) {
    array_push($cities, 224);
}
if (in_array('Протарас', $citiesArray)) {
    array_push($cities, 225);
}
if (in_array('Ларнака', $citiesArray)) {
    array_push($cities, 47);
}
if (in_array('Лимассол', $citiesArray)) {
    array_push($cities, 223);
}
if (in_array('Пафос', $citiesArray)) {
    array_push($cities, 222);
}
$allCities = implode(", ", $cities);

/* Search tours from Мастер-тур */
$curl = curl_init();
curl_setopt_array($curl, array(
    CURLOPT_URL => "http://178.172.133.139:81/SearchTour/json_handler.ashx",
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_ENCODING => "",
    CURLOPT_MAXREDIRS => 10,
    CURLOPT_TIMEOUT => 0,
    CURLOPT_FOLLOWLOCATION => true,
    CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
    CURLOPT_CUSTOMREQUEST => "POST",
    CURLOPT_POSTFIELDS => "{'id': 1,'method': 'SearchTours','params': {'paging': {'size': 20,'page': 1},'where': {'date': [" . $dateRange . "],'cityFrom': 448,'countryKey': 10,'nights': [" . $daysRange . "],'adults': " . $adults . ",'childs': " . $childs . ",'tourTypes': [],'tours': [],'cities': [" . $allCities . "],'hotels': [],'stars': [],'meals': [" . $allFoods . "]},'sort': {'price': 'asc'}}}",
    CURLOPT_HTTPHEADER => array(
        "Content-Type: application/json"
    ),
));
$response = curl_exec($curl);
curl_close($curl);
$result = json_decode($response, true);

$toursWithPriceFilter = [];

foreach ($result['result']['List'] as $tour) {
    if ($tour['prices']['EUR'] > $priceMin && $tour['prices']['EUR'] < $priceMax) {
        array_push($toursWithPriceFilter, $tour);
    }
}

/* Filter with hotel stars */
$toursWithHotelStarsFilter = [];

if (!empty($toursWithPriceFilter)) {
    foreach ($toursWithPriceFilter as $tour) {
        if ($hotelStars == '||Не ниже 3*||') {
            if ($tour['hotel']['star'] == "3*" || $tour['hotel']['star'] == "4*" || $tour['hotel']['star'] == "5*") {
                array_push($toursWithHotelStarsFilter, $tour);
            }
        }
        if ($hotelStars == '||Не ниже 4*||') {
            if ($tour['hotel']['star'] == "4*" || $tour['hotel']['star'] == "5*") {
                array_push($toursWithHotelStarsFilter, $tour);
            }
        }
        if ($hotelStars == '||5*||') {
            if ($tour['hotel']['star'] == "5*") {
                array_push($toursWithHotelStarsFilter, $tour);
            }
        }
    }
}

if (!empty($toursWithHotelStarsFilter)) {
    $offers = '';
    foreach ($toursWithHotelStarsFilter as $item) {//Final result
        $meal = $item['meal']['name'];
        $food = '';
        switch ($meal) {
            case 'AO':
                $food = 'Без питания';
                break;
            case 'BB':
                $food = 'Только завтрак';
                break;
            case 'HB':
                $food = 'Завтрак и ужин';
                break;
            case 'FB':
                $food = 'Завтрак, обед и ужин';
                break;
            case 'AI':
                $food = 'Все включено';
                break;
            case 'UAL':
            case 'PAL':
                $food = 'Ультра всё включено';
                break;
            default:
                $food = 'Любое, Без питания';
        }
        $hotelName = $item['hotel']['name'];
        $hotelSlug = strtolower(trim(preg_replace('/[^A-Za-z0-9-]+/', '-', $hotelName)));
        $hotelStar = $item['hotel']['star'];
        $city = $item['city']['name'];
        $priceEUR = $item['prices']['EUR'];
        $priceBYN = $item['prices']['BYN'];
        $nights = $item['night'];
        $cityFrom = 'Минск';
        $date = $item['tourDate'];
        $room = $item['roomCat']['name'];

        $offers .= '<table bgcolor="#ffffff" class="es-content-body" align="center" cellpadding="0" cellspacing="0" width="600"
       style="width:auto!important;mso-table-lspace:0pt;mso-table-rspace:0pt;border-collapse:collapse;border-spacing:0px;background-color:#FFFFFF;">
    <tbody>
    <tr style="border-collapse:collapse;">
        <td align="left" style="padding: 0;margin:0;"><!--[if mso]>
            <table width="600" cellpadding="0" cellspacing="0">
                <tr>
                    <td width="320" valign="top"><![endif]--> <!--[if mso]></td>
            <td width="0"></td>
            <td width="280" valign="top"> <![endif]-->
            <table cellpadding="0" cellspacing="0" align="right"
                   style="width:auto!important;mso-table-lspace:0pt;mso-table-rspace:0pt;border-collapse:collapse;border-spacing:0px;">
                <tbody>
                <tr style="border-collapse:collapse;">
                    <td align="left"
                        style="margin:0;padding: 20px 20px 20px 25px;">
                        <table cellpadding="0" cellspacing="0" class="es-left" align="left"
                               style="width:auto!important;mso-table-lspace:0pt;mso-table-rspace:0pt;border-collapse:collapse;border-spacing:0px;float:left;">
                            <tbody>
                            <tr style="border-collapse:collapse;">
                                <td width="168" class="es-m-p0r es-m-p20b" valign="top" align="center"
                                    style="padding: 0;margin:0;">
                                    <table cellpadding="0" cellspacing="0" width="100%"
                                           style="width:auto!important;mso-table-lspace:0pt;mso-table-rspace:0pt;border-collapse:collapse;border-spacing:0px;">
                                        <tbody>
                                        <tr style="border-collapse:collapse;">
                                            <td align="center" style="/* padding:7px;*/margin:0;"><img class="adapt-img"
                                                                                                       src="https://allcyprus-emailbot.leadgenbot.ru/hotels-pic/' . $hotelSlug . '.jpg"
                                                                                                       alt="' . $hotelName . '"
                                                                                                       width="168"
                                                                                                       title="' . $hotelName . '"
                                                                                                       style="display:block;border-width:0;outline-style:none;text-decoration:none;-ms-interpolation-mode:bicubic;width:213px;">
                                            </td>
                                        </tr>
                                        </tbody>
                                    </table>
                                </td>
                            </tr>
                            </tbody>
                        </table>
                        <table cellpadding="0" cellspacing="0" align="right"
                               style="width:auto!important;mso-table-lspace:0pt;mso-table-rspace:0pt;border-collapse:collapse;border-spacing:0px;">
                            <tbody>
                            <tr style="border-collapse:collapse;">
                                <td width="300px" align="left"
                                    style="padding: 0px;margin:0;">
                                    <table cellpadding="0" cellspacing="0" width="100%"
                                           style="mso-table-lspace:0pt;mso-table-rspace:0pt;border-collapse:collapse;border-spacing:0px;padding-left:34px;">
                                        <tbody>
                                        <tr style="border-collapse:collapse;">
                                            <td align="left"
                                                style="padding: 0 0 22px;margin:0;">
                                                <p style="margin:0;-webkit-text-size-adjust:none;-ms-text-size-adjust:none;mso-line-height-rule:exactly;font-size:16px;font-family:roboto, helvetica-neue, helvetica, arial, sans-serif;line-height:14px;color:#333333;">
                                                    <strong>' . $hotelName . ' ' . $hotelStar . '</strong></p></td>
                                        </tr>
                                        <tr style="border-collapse:collapse;">
                                            <td style="padding: 0 0 10px;margin:0;">
                                                <table style="width:auto!important;mso-table-lspace:0pt;mso-table-rspace:0pt;border-collapse:collapse;border-spacing:0px;margin-top:0;margin-bottom:0;margin-right:0;margin-left:0;width:100%;">
                                                    <tbody>
                                                    <tr style="border-collapse:collapse;width:100%;">
                                                        <td width="55%"
                                                            style="padding: 0;margin:0;">
                                                            <span style="font-size:14px;">' . $city . '</span></td>
                                                        <td width="70%"
                                                            style="padding: 0;margin:0;">
                                                            <span style="background-color:#FFCD80;padding: 5px 10px;border-radius:20px;font-size:12px;white-space: nowrap;"><b>' . $priceEUR . ' €</b> | ' . $priceBYN . ' BYN</span>
                                                        </td>
                                                    </tr>
                                                    </tbody>
                                                </table>
                                            </td>
                                        </tr>
                                        <tr style="border-collapse:collapse;/* float:right;">
                                            <td style="padding: 0 0 26px;margin:0;">
                                                <table style="width:auto!important;mso-table-lspace:0pt;mso-table-rspace:0pt;border-collapse:collapse;border-spacing:0px;">
                                                    <tbody>
                                                    <tr style="border-collapse:collapse;">
                                                        <td width="60%"
                                                            style="padding: 0px 24px 0px 0px;margin:0;font-size:10px;line-height:16px;">
                                                            <b>Длительность тура:</b> ' . $nights . ' ночей
                                                        </td>
                                                        <td width="40%"
                                                            style="padding: 0;margin:0;font-size:10px;line-height:16px;">
                                                            <b>Город вылета:</b> ' . $cityFrom . '
                                                        </td>
                                                    </tr>
                                                    <tr style="border-collapse:collapse;">
                                                        <td width="50%"
                                                            style="padding: 0;margin:0;font-size:10px;line-height:16px;">
                                                            <b>Питание:</b> ' . $food . '
                                                        </td>
                                                        <td width="50%"
                                                            style="padding: 0;margin:0;font-size:10px;line-height:16px;">
                                                            <b>Дата вылета:</b> ' . $date . '
                                                        </td>
                                                    </tr>
                                                    <tr style="border-collapse:collapse;">
                                                        <td colspan="2"
                                                            style="padding: 0;margin:0;font-size:10px;line-height:16px;">
                                                            <b>Номер:</b> ' . $room . '
                                                        </td>
                                                    </tr>
                                                    </tbody>
                                                </table>
                                            </td>
                                        </tr>
                                        <tr style="border-collapse:collapse;">
                                            <td align="left"
                                                style="padding: 0;margin:0;">
                                                <span class="es-button-border"
                                                      style="border-style:solid;border-color:#2CB543;background-color:#42868F;background-image:none;background-repeat:repeat;background-position:top left;background-attachment:scroll;border-width:0px;display:inline-block;border-radius:4px;width:auto;"><a
                                                        href="https://www.google.com/" class="es-button" target="_blank"
                                                        style="mso-style-priority:100 !important;text-decoration:none!important;-webkit-text-size-adjust:none;-ms-text-size-adjust:none;mso-line-height-rule:exactly;font-family:roboto, helvetica-neue, helvetica, arial, sans-serif;font-size:11px;color:#FFFFFF;border-style:solid;border-color:#42868F;border-width:10px 40px;display:inline-block;background-color:#42868F;background-image:none;background-repeat:repeat;background-position:top left;background-attachment:scroll;border-radius:4px;font-weight:normal;font-style:normal;line-height:17px;width:auto;text-align:center;">Узнать подробности</a></span>
                                            </td>
                                        </tr>
                                        </tbody>
                                    </table>
                                </td>
                            </tr>
                            </tbody>
                        </table>
                    </td>
                </tr>
                <tr style="border-collapse:collapse;background-color: transparent;border: transparent solid 1px;">
                    <td align="left"
                        style="margin:0;padding: 20px;border-style:none !important;background-color:transparent !important;background-image:none !important;background-repeat:repeat !important;background-position:top left !important;background-attachment:scroll !important;">
                        <table cellspacing="0" width="100%" border="0"
                               style="mso-table-lspace:0pt;mso-table-rspace:0pt;border-collapse:collapse;border-spacing:0px;">
                            <tbody border="0">
                            <tr style="border-collapse:collapse;">
                                <td width="560" align="center" valign="top"
                                    style="padding: 0;margin:0;">
                                    <table cellpadding="0" cellspacing="0" width="100%"
                                           style="mso-table-lspace:0pt;mso-table-rspace:0pt;border-collapse:collapse;border-spacing:0px;">
                                        <tbody border="0">
                                        <tr style="border-collapse:collapse;">
                                            <td align="center"
                                                style="padding: 0;margin:0;">
                                                <table border="0" width="100%" height="100%" cellpadding="0"
                                                       cellspacing="0"
                                                       style="mso-table-lspace:0pt;mso-table-rspace:0pt;border-collapse:collapse;border-spacing:0px;">
                                                    <tbody border="0">
                                                    <tr style="border-collapse:collapse;">
                                                        <td style="padding: 0;margin:0px 0px 0px 0px;border-bottom-width:1px;border-bottom-style:solid;border-bottom-color:#CCCCCC;background-color:transparent;background-image:none;background-repeat:repeat;background-position:top left;background-attachment:scroll;height:1px;width:100%;margin-top:0px;margin-bottom:0px;margin-right:0px;margin-left:0px;"></td>
                                                    </tr>
                                                    </tbody>
                                                </table>
                                            </td>
                                        </tr>
                                        </tbody>
                                    </table>
                                </td>
                            </tr>
                            </tbody>
                        </table>
                    </td>
                </tr>
                </tbody>
            </table>
        </td>
    </tr>
    </tbody>
</table> ';
    }
} else {
    $offers = null;//Empty result
}


$url = 'https://cyptus.api-us1.com';

$params = array(
    'api_key' => '1d5cd52490ef3e80e2eaad53bdea81d7cdc1deb3d43ae4a1caf15b5ec0ebd56dac894bca',
    'api_action' => 'contact_edit',
    'api_output' => 'serialize',
    'overwrite' => 0,
);

if ($offers != null) {
    $post = [
        'id' => $id,
        'email' => $email,
        'tags' => 'offers_updated',
        'field[%OFFERS%,0]' => $offers,
        'p[1]' => 1,
    ];
} else {
    $post = [
        'id' => $id,
        'email' => $email,
        'tags' => 'empty_results',
        'p[1]' => 1,
    ];
}

$query = "";
foreach ($params as $key => $value) $query .= urlencode($key) . '=' . urlencode($value) . '&';
$query = rtrim($query, '& ');

$data = "";
foreach ($post as $key => $value) $data .= urlencode($key) . '=' . urlencode($value) . '&';
$data = rtrim($data, '& ');

$url = rtrim($url, '/ ');

if (!function_exists('curl_init')) die('CURL not supported. (introduced in PHP 4.0.2)');

$api = $url . '/admin/api.php?' . $query;

$request = curl_init($api);
curl_setopt($request, CURLOPT_HEADER, 0);
curl_setopt($request, CURLOPT_RETURNTRANSFER, 1);
curl_setopt($request, CURLOPT_POSTFIELDS, $data);
//curl_setopt($request, CURLOPT_SSL_VERIFYPEER, FALSE); // uncomment if you get no gateway response and are using HTTPS
curl_setopt($request, CURLOPT_FOLLOWLOCATION, true);

$response = (string)curl_exec($request);

curl_close($request);