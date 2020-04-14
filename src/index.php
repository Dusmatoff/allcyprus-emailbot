<?php
/* POST параметры */
$months = $_POST['contact']['fields']['month'];
$date_min = $_POST['contact']['fields']['date_min'];
$date_max = $_POST['contact']['fields']['date_max'];
$adults = empty($_POST['contact']['fields']['adults']) ? 2 : (int)$_POST['contact']['fields']['adults']; //Количество взрослых (по умолчанию 2)
$childs = empty($_POST['contact']['fields']['childs']) ? 0 : $_POST['contact']['fields']['childs']; //Количество детей (по умолчанию 0)
$days_min = $_POST['contact']['fields']['days_min']; //Ночей, от
$days_max = $_POST['contact']['fields']['days_max']; //Ночей, до
$meals = $_POST['contact']['fields']['food']; //Питание: Любое - '', Без питания - AO(id-10), Только завтрак - BB(id-4), Завтрак и ужин - HB(id-2), HB (id-40), Завтрак, обед и ужин - FB(id-1), Все включено - AI(id-11), Ультра всё включено - UAL(id-38), PAL(id-39)
$hotel_stars = $_POST['contact']['fields']['hotel_stars']; //Звездность отеля 2*, 3*, 4*, 5*
$price_min = (int)$_POST['contact']['fields']['price_min']; //Цена от
$price_max = (int)$_POST['contact']['fields']['price_max']; //Цена до
$resort = $_POST['contact']['fields']['resort']; //Курорт Айя-Напа - 224, Лимассол - 223, Пафос - 222, Протарас - 225, Ларнака - 47
$id = $_POST['contact']['id'];
$email = $_POST['contact']['email'];

/* GET параметры */
$get_date_min = (string)$_GET['date_from'];
$get_date_max = (string)$_GET['date_til'];
$get_stars_from = (int)$_GET['stars_from'];
$get_price_min = (int)$_GET['price_min'];
$get_price_max = (int)$_GET['price_max'];
$get_hotels_ids = (string)$_GET['hotels'];
$offers_field = $_GET['offers_field'];
$price_min_field = $_GET['price_min_field'];
$price_max_field = $_GET['price_max_field'];
$get_tours_ids = $_GET['tours'];
$get_max_tours = empty($_GET['max_tours']) ? 10 : (int)$_GET['max_tours'];

if(!empty($get_date_min)){
    //самая поздняя из дата вылета от
    $dateMin = strtotime($date_min) > strtotime($get_date_min) ? $date_min : $get_date_min;
}else{
    $dateMin = $date_min;
}

if(!empty($get_date_max)){
    //самая раняя из дата вылета до
    $dateMax = strtotime($date_max) > strtotime($get_date_max) ? $get_date_max : $date_max;
}else{
    $dateMax = $date_max;
}

$priceMin = $price_min > $get_price_min ? $price_min : $get_price_min;
$priceMax = $price_max > $get_price_max ? $price_max : $get_price_max;

$dateRange = setDateRange($dateMin, $dateMax);
$nightsRange = setNightsRange($days_min, $days_max);
$toursIds = empty($get_tours_ids) ? '' : $get_tours_ids;
$allCities = setCities($resort);
$hotelsIds = empty($get_hotels_ids) ? '' : "$get_hotels_ids";
$postHotelStar = convertHotelStarsToInt($hotel_stars);
$hotelStars = $postHotelStar > $get_stars_from ? setHotelStars($postHotelStar) : setHotelStars($get_stars_from);
$allFoods = setFoods($meals);

/*$dateRange = setDateRange('2020-06-01', '2020-08-29');
$nightsRange = setNightsRange(16);
$allFoods = setFoods('||Только завтрак||');
$allCities = setCities('||Протарас||');
$hotelStars = setHotelStars('Все равно');*/

/*$post = file_get_contents('php://input');
$data = urldecode($post);
file_put_contents('acpost.txt', $post);
file_put_contents('acurldecode.txt', $data);*/

/* Search tours from Мастер-тур */
$result = searchMasterTours($dateRange, $nightsRange, $adults, $childs, $toursIds, $allCities, $hotelsIds, $hotelStars, $allFoods);

$toursWithPriceFilter = getToursWithPriceFilter($result, $priceMin, $priceMax);

/*echo "<pre>";
var_dump($result);
echo "</pre>";
die();*/

if (!empty($toursWithPriceFilter)) {
    $offers = '';
    $offersPrices = [];
    $counter = 0;
    foreach ($toursWithPriceFilter as $item) {
        $counter++;

        array_push($offersPrices, $item['prices']['EUR']);

        $meal = $item['meal']['name'];
        $food = '';
        if ($meal == 'AO') {
            $food = 'Без питания';
        } elseif ($meal == 'BB') {
            $food = 'Только завтрак';
        } elseif ($meal == 'HB') {
            $food = 'Завтрак и ужин';
        } elseif ($meal == 'FB') {
            $food = 'Завтрак, обед и ужин';
        } elseif ($meal == 'AI') {
            $food = 'Все включено';
        } elseif ($meal == 'UAL' || $meal == 'PAL') {
            $food = 'Ультра всё включено';
        } else {
            $food = 'Любое, Без питания';
        }
        $hotelName = $item['hotel']['name'];
        $hotelPicUrl = getHotelPicture($hotelName);
        $hotelStar = $item['hotel']['star'];
        $city = $item['city']['name'];
        $priceEUR = $item['prices']['EUR'];
        $priceBYN = $item['prices']['BYN'];
        $nights = $item['night'];
        $cityFrom = 'Минск';
        $date = $item['tourDate'];
        $room = $item['roomCat']['name'];

        $offers .= '<table class="cart_tour" style="box-sizing: border-box; grid-auto-flow: column; width: fit-content; display: grid; grid-auto-flow: column; font-family: Roboto, Helvetica, Arial, sans-serif; word-wrap: break-word;"><tr><td class="cart_image_block"style="box-sizing: border-box; width: 240px; float: left; padding: 45px 0px 40px 40px; display: table-cell;vertical-align: middle;"><div width="198" height="148"><img style="width: 100%; display: table-cell; vertical-align: middle;" src="' . $hotelPicUrl . '"></div></td><td><table class="cart_content_block" style="box-sizing: border-box; width: 360px; float: left; display: grid; padding: 20px 0px 20px 40px;"><tr class="cart_content_h" style="box-sizing: border-box; width: 100%; height: fit-content;"><td><span style="font-weight: bold; font-size: 16px;">' . $hotelName . ' ' . $hotelStar . '</span></td></tr><tr class="cart_columns" style="box-sizing: border-box;"><td><table><tr class="cart_heads" style="box-sizing: border-box;"><td class="cart_first_column_head" style="box-sizing: border-box; float: left; font-size: 14px; width: 60%;">' . $city . '</td><td class="cart_second_column_head" style="box-sizing: border-box; float: left; font-size: 14px; width: 40%;"><div class="price_block" style="box-sizing: border-box; width: fit-content; background-color: #FFCD80; border-radius: 22px; padding: 0px 6px 3px 5px;"><span style="font-size: 10px; color: #333333;"><b>' . $priceEUR . ' € | </b>' . $priceBYN . ' BYN</span></div></td></tr><tr><td class="cart_first_column" style="box-sizing: border-box; float: left; color: rgb(0, 0, 0); font-style: normal; font-weight: 100; word-spacing: 0.015625px; font-size: 14px; padding-top: 10px; width: 60%;" align="inherit"><span style="font-weight: bold;">Длительность:</span> ' . $nights . ' ночей;<br><span style="font-weight: bold;">Питание:</span> ' . $food . '<br><span style="font-weight: bold;">Номер:</span> ' . $room . '<br><br></td><td class="cart_second_column" style="box-sizing: border-box; float: left; color: rgb(0, 0, 0); font-style: normal; font-weight: 100; word-spacing: 0.015625px; font-size: 14px; padding-top: 10px; width: 40%;" align="inherit"><span style="font-weight: bold;">Дата:</span> ' . $date . '<br><span style="font-weight: bold;">Вылет:</span> ' . $cityFrom . '</td></tr></table></td></tr></table></td></tr></table>';
        if ($counter >= $get_max_tours) {
            break;
        }
    }
    $offersMinPrice = min($offersPrices);
    $offersMaxPrice = max($offersPrices);
} else {
    $offers = null;//Empty result
}

$offersField = empty($offers_field) ? 'OFFERS' : $offers_field;
$minPriceField = empty($price_min_field) ? 'OFFERS_MIN_PRICE' : $price_min_field;
$maxPriceField = empty($price_max_field) ? 'OFFERS_MAX_PRICE' : $price_max_field;

addTag($offers, $offersMinPrice, $offersMaxPrice, $id, $email, $offersField, $minPriceField, $maxPriceField);//Add tag

function setDateRange($dateMin, $dateMax)
{
    $currentDate = date('Y-m-d');
    $currentDatePlus30Days = date('Y-m-d', strtotime('+30 days'));

    if (!empty($dateMin) && !empty($dateMax)) {
        //"Дата от" и "Дата до" заполнены
        $dateDiff = strtotime($dateMax) - strtotime($dateMin);
        $daysDiff = round($dateDiff / (60 * 60 * 24));

        if ($daysDiff <= 30) {
            //Диапазон дат меньше или равно 30 дней
            return $dateMin . "','" . $dateMax;
        }

        if (strtotime($currentDate) >= strtotime($dateMin) && strtotime($currentDate) <= strtotime($dateMax)) {
            //Дата вебхука попадает в диапазон выбранных дат пользователя
            return $currentDate . "','" . $currentDatePlus30Days;
        } else {
            //Дата вебхука НЕ попадает в диапазон выбранных дат пользователя
            $date_minPlus30Days = date('Y-m-d', strtotime('+30 days', strtotime($dateMin)));
            return $dateMin . "','" . $date_minPlus30Days;
        }
    } else {
        return $currentDate . "','" . $currentDatePlus30Days;
    }
}

function setNightsRange($daysMin = null, $daysMax = null)
{
    $nights = [];

    if (empty($daysMin)) {
        $daysMin = 7;
    }
    if (empty($daysMax)) {
        $daysMax = 21;
    }

    for ($x = $daysMin; $x <= $daysMax; $x++) {
        array_push($nights, $x);
    }

    return implode(',', $nights);
}

function convertHotelStarsToInt($stringHotelStars)
{
    if ($stringHotelStars == 'Все равно') {
        return 0;
    } elseif ($stringHotelStars == 'Не ниже 3*') {
        return 3;
    } elseif ($stringHotelStars == 'Не ниже 4*') {
        return 4;
    } else {
        return 5;
    }
}

function setHotelStars($stars)
{
    //$stars = convertHotelStarsToInt($hotelStars);

    if ($stars == 0) {
        return "";
    } elseif ($stars == 3) {
        return "'3*','4*','5*'";
    } elseif ($stars == 4) {
        return "'4*','5*'";
    } else {
        return "'5*'";
    }
}

function setFoods($meals)
{
    $foods = [];
    $foodsArray = explode("||", $meals);
    /*if (in_array('Любое', $foodsArray)) {
        array_push($foods, 10);
    }*/
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
        array_push($foods, 38);
        array_push($foods, 39);
    }
    if (in_array('Ультра всё включено', $foodsArray)) {
        array_push($foods, 38);
        array_push($foods, 39);
    }

    if (empty($foods)) {
        return '';
    }

    return implode(", ", $foods);
}

function getToursWithPriceFilter($result, $priceMin, $priceMax)
{
    $toursWithPriceFilter = [];

    if (empty($priceMin)) {
        $priceMin = 100;
    }

    if (empty($priceMax)) {
        $priceMax = 100000;
    }

    if (is_array($result['result'])) {
        foreach ($result['result']['List'] as $tour) {
            if ($tour['prices']['EUR'] >= $priceMin && $tour['prices']['EUR'] <= $priceMax) {
                array_push($toursWithPriceFilter, $tour);
            }
        }
        return $toursWithPriceFilter;
    } else {
        return null;
    }
}

function setCities($resort)
{
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

    if (empty($cities)) {
        return '';
    }

    return implode(", ", $cities);
}

function addTag($offers, $offersMinPrice, $offersMaxPrice, $id, $email, $offersField, $minPriceField, $maxPriceField)
{
    $url = 'https://cyptus.api-us1.com';

    $params = array(
        'api_key' => '1d5cd52490ef3e80e2eaad53bdea81d7cdc1deb3d43ae4a1caf15b5ec0ebd56dac894bca',
        'api_action' => 'contact_edit',
        'api_output' => 'serialize',
        'overwrite' => 0,
    );

    if ($offers != null) {
        $post = [
            "id" => $id,
            "email" => $email,
            "tags" => 'offers_updated',//1empty_results
            "field[%$offersField%,0]" => $offers,
            "field[%$minPriceField%,0]" => $offersMinPrice,
            "field[%$maxPriceField%,0]" => $offersMaxPrice,
            "p[1]" => 1,
        ];
    } else {
        $post = [
            'id' => $id,
            'email' => $email,
            'tags' => 'empty_results, offers_updated',
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
}

function searchMasterTours($dateRange, $nightsRange, $adults, $childs, $toursIds, $allCities, $hotelsIds, $hotelStars, $allFoods)
{
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
        CURLOPT_POSTFIELDS => "{'id': 1,'method': 'SearchTours','params': {'paging': {'size': 20,'page': 1},'where': {'date': ['" . $dateRange . "'],'cityFrom': 448,'countryKey': 10,'nights': [" . $nightsRange . "],'adults': " . $adults . ",'childs': " . $childs . ",'tourTypes': [],'tours': [" . $toursIds . "],'cities': [" . $allCities . "],'hotels': [" . $hotelsIds . "],'stars': [" . $hotelStars . "],'meals': [" . $allFoods . "]},'sort': {'price': 'asc'}}}",
        CURLOPT_HTTPHEADER => array(
            "Content-Type: application/json"
        ),
    ));
    $response = curl_exec($curl);
    curl_close($curl);
    return json_decode($response, true);
}

function getHotelPicture($hotelName)
{
    $hotelSlug = strtolower(trim(preg_replace('/[^A-Za-z0-9-]+/', '-', $hotelName)));
    $protocol = stripos($_SERVER['SERVER_PROTOCOL'],'https') === 0 ? 'https://' : 'http://';
    $picsFolder = $protocol . $_SERVER['SERVER_NAME'] . '/hotels-pic/';
    $isHotelPicFounded = file_get_contents($picsFolder . $hotelSlug . '.jpg');
    if ($isHotelPicFounded === false) {
        return $picsFolder . 'kypr-default-hotel.jpg';
    } else {
        return $picsFolder . $hotelSlug . '.jpg';
    }
}

function setMonths($months)
{
    //Решили оставить на потом
    $currentYear = date('Y');
    $currentDate = date('Y-m-d');
    $dateRange = $currentDate . ',' . $currentYear . '-12-31';;
    if ($months != '||Любой месяц||') {
        $monthArray = explode("||", $months);
        if (in_array('Январь', $monthArray)) {
            $dateRange = "2020-01-01','2020-01-31";
            //$dateRange = $currentDate . "','" . $currentYear . "-01-31'";
        } elseif (in_array('Февраль', $monthArray)) {
            $dateRange = $currentDate . "','" . $currentYear . "-02-29'";
        } elseif (in_array('Март', $monthArray)) {
            $dateRange = $currentDate . "','" . $currentYear . "-03-31'";
        } elseif (in_array('Апрель', $monthArray)) {
            $dateRange = $currentDate . "','" . $currentYear . "-04-30'";
        } elseif (in_array('Май', $monthArray)) {
            $dateRange = $currentDate . "','" . $currentYear . "-05-31'";
        } elseif (in_array('Июнь', $monthArray)) {
            $dateRange = "2020-01-01','2020-06-30";
            //$dateRange = $currentDate . "','" . $currentYear . "-06-30'";
        } elseif (in_array('Июль', $monthArray)) {
            $dateRange = $currentDate . "','" . $currentYear . "-07-31'";
        } elseif (in_array('Август', $monthArray)) {
            $dateRange = $currentDate . "','" . $currentYear . "-08-31'";
        } elseif (in_array('Сентябрь', $monthArray)) {
            $dateRange = $currentDate . "','" . $currentYear . "-09-30'";
        } elseif (in_array('Октябрь', $monthArray)) {
            $dateRange = $currentDate . "','" . $currentYear . "-10-31'";
        } elseif (in_array('Ноябрь', $monthArray)) {
            $dateRange = $currentDate . "','" . $currentYear . "-11-30'";
        } elseif (in_array('Декабрь', $monthArray)) {
            $dateRange = $currentDate . "','" . $currentYear . "-12-31'";
        }
    }

    return $dateRange;
}