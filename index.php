<?php
// 设置API路径
define('API_URI', 'https://meting.kcn3388.com/');
// 设置中文歌词
define('TLYRIC', true);
// 设置歌单文件缓存及时间
define('CACHE', false);
define('CACHE_TIME', 86400);
// 设置短期缓存-需要安装apcu
define('APCU_CACHE', false);
// 设置AUTH密钥-更改'meting-secret'
define('AUTH', false);
define('AUTH_SECRET', 'meting-secret');

if (!isset($_GET['type']) || !isset($_GET['id'])) {
    include __DIR__ . '/public/index.php';
    exit;
}

$server = isset($_GET['server']) ? $_GET['server'] : 'netease';
$type = $_GET['type'];
$id = $_GET['id'];

if (AUTH) {
    $auth = isset($_GET['auth']) ? $_GET['auth'] : '';
    if (in_array($type, ['url', 'pic', 'lrc'])) {
        if ($auth == '' || $auth != auth($server . $type . $id)) {
            http_response_code(403);
            exit;
        }
    }
}

// 数据格式
if (in_array($type, ['song', 'playlist'])) {
    header('content-type: application/json; charset=utf-8;');
} else if (in_array($type, ['name', 'lrc', 'artist'])) {
    header('content-type: text/plain; charset=utf-8;');
}

// 允许跨站
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET');

// include __DIR__ . '/vendor/autoload.php';
// you can use 'Meting.php' instead of 'autoload.php'
include __DIR__ . '/src/Meting.php';

use Metowolf\Meting;

$api = new Meting($server);
$api->format(true);

// 设置cookie
if ($server == 'netease') {
    $api->cookie("MUSIC_A_T=1471970204932; Max-Age=2147483647; Expires=Tue, 18 Apr 2090 23:42:40 GMT; Path=/wapi/feedback;;MUSIC_R_T=1471970211562; Max-Age=2147483647; Expires=Tue, 18 Apr 2090 23:42:40 GMT; Path=/api/clientlog;;MUSIC_R_T=1471970211562; Max-Age=2147483647; Expires=Tue, 18 Apr 2090 23:42:40 GMT; Path=/api/feedback;;MUSIC_R_T=1471970211562; Max-Age=2147483647; Expires=Tue, 18 Apr 2090 23:42:40 GMT; Path=/openapi/clientlog;;MUSIC_A_T=1471970204932; Max-Age=2147483647; Expires=Tue, 18 Apr 2090 23:42:40 GMT; Path=/api/feedback;;MUSIC_A_T=1471970204932; Max-Age=2147483647; Expires=Tue, 18 Apr 2090 23:42:40 GMT; Path=/neapi/feedback;;MUSIC_A_T=1471970204932; Max-Age=2147483647; Expires=Tue, 18 Apr 2090 23:42:40 GMT; Path=/eapi/clientlog;;MUSIC_R_T=1471970211562; Max-Age=2147483647; Expires=Tue, 18 Apr 2090 23:42:40 GMT; Path=/neapi/feedback;;MUSIC_U=4251d3763d1eaebc382624e10712a2e65e9b5233b736633d17482f4c426b9234ab5c3e53575a42fab72149bd3b14523943124f3fcebe94e446b14e3f0c3f8af94212382188fe1965; Max-Age=1296000; Expires=Fri, 15 Apr 2022 20:28:33 GMT; Path=/;;MUSIC_R_T=1471970211562; Max-Age=2147483647; Expires=Tue, 18 Apr 2090 23:42:40 GMT; Path=/wapi/feedback;;MUSIC_R_T=1471970211562; Max-Age=2147483647; Expires=Tue, 18 Apr 2090 23:42:40 GMT; Path=/weapi/feedback;;MUSIC_R_T=1471970211562; Max-Age=2147483647; Expires=Tue, 18 Apr 2090 23:42:40 GMT; Path=/weapi/clientlog;;MUSIC_A_T=1471970204932; Max-Age=2147483647; Expires=Tue, 18 Apr 2090 23:42:40 GMT; Path=/api/clientlog;;MUSIC_A_T=1471970204932; Max-Age=2147483647; Expires=Tue, 18 Apr 2090 23:42:40 GMT; Path=/weapi/feedback;;MUSIC_A_T=1471970204932; Max-Age=2147483647; Expires=Tue, 18 Apr 2090 23:42:40 GMT; Path=/wapi/clientlog;;MUSIC_A_T=1471970204932; Max-Age=2147483647; Expires=Tue, 18 Apr 2090 23:42:40 GMT; Path=/openapi/clientlog;;MUSIC_A_T=1471970204932; Max-Age=2147483647; Expires=Tue, 18 Apr 2090 23:42:40 GMT; Path=/neapi/clientlog;;MUSIC_SNS=; Max-Age=0; Expires=Thu, 31 Mar 2022 20:28:33 GMT; Path=/;MUSIC_R_T=1471970211562; Max-Age=2147483647; Expires=Tue, 18 Apr 2090 23:42:40 GMT; Path=/eapi/feedback;;MUSIC_A_T=1471970204932; Max-Age=2147483647; Expires=Tue, 18 Apr 2090 23:42:40 GMT; Path=/weapi/clientlog;;__remember_me=true; Max-Age=1296000; Expires=Fri, 15 Apr 2022 20:28:33 GMT; Path=/;;MUSIC_A_T=1471970204932; Max-Age=2147483647; Expires=Tue, 18 Apr 2090 23:42:40 GMT; Path=/eapi/feedback;;MUSIC_R_T=1471970211562; Max-Age=2147483647; Expires=Tue, 18 Apr 2090 23:42:40 GMT; Path=/eapi/clientlog;;__csrf=018bec5c2af8b6d058fa1410e6e072b7; Max-Age=1296010; Expires=Fri, 15 Apr 2022 20:28:43 GMT; Path=/;;MUSIC_R_T=1471970211562; Max-Age=2147483647; Expires=Tue, 18 Apr 2090 23:42:40 GMT; Path=/wapi/clientlog;;MUSIC_R_T=1471970211562; Max-Age=2147483647; Expires=Tue, 18 Apr 2090 23:42:40 GMT; Path=/neapi/clientlog;");
}

if ($type == 'playlist') {

    if (CACHE) {
        $file_path = __DIR__ . '/cache/playlist/' . $server . '_' . $id . '.json';
        if (file_exists($file_path)) {
            if ($_SERVER['REQUEST_TIME'] - filectime($file_path) < CACHE_TIME) {
                echo file_get_contents($file_path);
                exit;
            }
        }
    }

    $data = $api->playlist($id);
    if ($data == '[]') {
        echo '{"error":"unknown playlist id"}';
        exit;
    }
    $data = json_decode($data);
    $playlist = array();
    foreach ($data as $song) {
        $playlist[] = array(
            'name'   => $song->name,
            'artist' => implode('/', $song->artist),
            'url'    => API_URI . '?server=' . $song->source . '&type=url&id=' . $song->url_id . (AUTH ? '&auth=' . auth($song->source . 'url' . $song->url_id) : ''),
            'pic'    => API_URI . '?server=' . $song->source . '&type=pic&id=' . $song->pic_id . (AUTH ? '&auth=' . auth($song->source . 'pic' . $song->pic_id) : ''),
            'lrc'    => API_URI . '?server=' . $song->source . '&type=lrc&id=' . $song->lyric_id . (AUTH ? '&auth=' . auth($song->source . 'lrc' . $song->lyric_id) : '')
        );
    }
    $playlist = json_encode($playlist);

    if (CACHE) {
        // ! mkdir /cache/playlist
        file_put_contents($file_path, $playlist);
    }

    echo $playlist;
} else {
    $need_song = !in_array($type, ['url', 'pic', 'lrc']);
    if ($need_song && !in_array($type, ['name', 'artist', 'song'])) {
        echo '{"error":"unknown type"}';
        exit;
    }

    if (APCU_CACHE) {
        $apcu_time = $type == 'url' ? 600 : 36000;
        $apcu_type_key = $server . $type . $id;
        if (apcu_exists($apcu_type_key)) {
            $data = apcu_fetch($apcu_type_key);
            return_data($type, $data);
        }
        if ($need_song) {
            $apcu_song_id_key = $server . 'song_id' . $id;
            if (apcu_exists($apcu_song_id_key)) {
                $song = apcu_fetch($apcu_song_id_key);
            }
        }
    }

    if (!$need_song) {
        $data = song2data($api, null, $type, $id);
    } else {
        if (!isset($song)) $song = $api->song($id);
        if ($song == '[]') {
            echo '{"error":"unknown song"}';
            exit;
        }
        if (APCU_CACHE) {
            apcu_store($apcu_song_id_key, $song, $apcu_time);
        }
        $data = song2data($api, json_decode($song)[0], $type, $id);
    }

    if (APCU_CACHE) {
        apcu_store($apcu_type_key, $data, $apcu_time);
    }

    return_data($type, $data);
}

function api_uri() // static
{
    return (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https://' : 'http://') . $_SERVER['HTTP_HOST'] . strtok($_SERVER['REQUEST_URI'], '?');
}

function auth($name)
{
    return hash_hmac('sha1', $name, AUTH_SECRET);
}

function song2data($api, $song, $type, $id)
{
    $data = '';
    switch ($type) {
        case 'name':
            $data = $song->name;
            break;

        case 'artist':
            $data = implode('/', $song->artist);
            break;

        case 'url':
            $m_url = json_decode($api->url($id, 320))->url;
            if ($m_url == '') break;
            // url format
            if ($api->server == 'netease') {
                if ($m_url[4] != 's') $m_url = str_replace('http', 'https', $m_url);
            }

            $data = $m_url;
            break;

        case 'pic':
            $data = json_decode($api->pic($id, 90))->url;
            break;

        case 'lrc':
            $lrc_data = json_decode($api->lyric($id));
            if ($lrc_data->lyric == '') {
                $lrc = '[00:00.00]这似乎是一首纯音乐呢，请尽情欣赏它吧！';
            } else if ($lrc_data->tlyric == '') {
                $lrc = $lrc_data->lyric;
            } else if (TLYRIC) { // lyric_cn
                $lrc_arr = explode("\n", $lrc_data->lyric);
                $lrc_cn_arr = explode("\n", $lrc_data->tlyric);
                $lrc_cn_map = array();
                foreach ($lrc_cn_arr as $i => $v) {
                    if ($v == '') continue;
                    $line = explode(']', $v, 2);
                    // 格式化处理
                    $line[1] = trim(preg_replace('/\s\s+/', ' ', $line[1]));
                    $lrc_cn_map[$line[0]] = $line[1];
                    unset($lrc_cn_arr[$i]);
                }
                foreach ($lrc_arr as $i => $v) {
                    if ($v == '') continue;
                    $key = explode(']', $v, 2)[0];
                    if (!empty($lrc_cn_map[$key]) && $lrc_cn_map[$key] != '//') {
                        $lrc_arr[$i] .= ' (' . $lrc_cn_map[$key] . ')';
                        unset($lrc_cn_map[$key]);
                    }
                }
                $lrc = implode("\n", $lrc_arr);
            } else {
                $lrc = $lrc_data->lyric;
            }
            $data = $lrc;
            break;

        case 'song':
            $data = json_encode(array(array(
                'name'   => $song->name,
                'artist' => implode('/', $song->artist),
                'url'    => API_URI . '?server=' . $song->source . '&type=url&id=' . $song->url_id . (AUTH ? '&auth=' . auth($song->source . 'url' . $song->url_id) : ''),
                'pic'    => API_URI . '?server=' . $song->source . '&type=pic&id=' . $song->pic_id . (AUTH ? '&auth=' . auth($song->source . 'pic' . $song->pic_id) : ''),
                'lrc'    => API_URI . '?server=' . $song->source . '&type=lrc&id=' . $song->lyric_id . (AUTH ? '&auth=' . auth($song->source . 'lrc' . $song->lyric_id) : '')
            )));
            break;
    }
    if ($data == '') exit;
    return $data;
}

function return_data($type, $data)
{
    if (in_array($type, ['url', 'pic'])) {
        header('Location: ' . $data);
    } else {
        echo $data;
    }
    exit;
}
