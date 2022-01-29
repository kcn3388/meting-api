<?php
// 设置API路径
define('API_URI', 'https://meting.kcn3388.club/');
// 设置中文歌词
define('LYRIC_CN', true);
// 设置文件缓存及时间
define('CACHE', false);
define('CACHE_TIME', 86400);
// 设置AUTH密钥-更改'meting-secret'
define('AUTH', false);
define('AUTH_SECRET', 'meting-secret');

function auth($name)
{
    return hash_hmac('sha1', $name, AUTH_SECRET);
}

if (!isset($_GET['type']) || !isset($_GET['id'])) {
    include __DIR__ . '/public/index.html';
    exit;
}

$server = isset($_GET['server']) ? $_GET['server'] : 'netease';
$type = $_GET['type'];
$id = $_GET['id'];

if (AUTH) {
    $auth = isset($_GET['auth']) ? $_GET['auth'] : '';
    if (in_array($type, ['url', 'cover', 'lrc'])) {
        if ($auth == '' || $auth != auth($server . $type . $id)) {
            http_response_code(403);
            exit;
        }
    }
}

// 数据格式
if (in_array($type, ['song', 'playlist'])) {
    header('content-type: application/json; charset=utf-8;');
} elseif (in_array($type, ['lrc'])) {
    header('content-type: text/plain; charset=utf-8;');
}

// 允许跨站
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET');

// require __DIR__ . '/vendor/autoload.php';
// you can use 'Meting.php' instead of 'autoload.php'
require __DIR__ . '/src/Meting.php';

use Metowolf\Meting;

$api = new Meting($server);
$api->format(true);

// 设置cookie
if ($server == 'netease') {
    $api->cookie("MUSIC_R_T=1471970211562; Max-Age=2147483647; Expires=Thu, 16 Feb 2090 18:56:22 GMT; Path=/api/feedback;;MUSIC_R_T=1471970211562; Max-Age=2147483647; Expires=Thu, 16 Feb 2090 18:56:22 GMT; Path=/neapi/clientlog;;MUSIC_R_T=1471970211562; Max-Age=2147483647; Expires=Thu, 16 Feb 2090 18:56:22 GMT; Path=/eapi/feedback;;MUSIC_R_T=1471970211562; Max-Age=2147483647; Expires=Thu, 16 Feb 2090 18:56:22 GMT; Path=/wapi/clientlog;;MUSIC_A_T=1471970204932; Max-Age=2147483647; Expires=Thu, 16 Feb 2090 18:56:22 GMT; Path=/wapi/feedback;;MUSIC_R_T=1471970211562; Max-Age=2147483647; Expires=Thu, 16 Feb 2090 18:56:22 GMT; Path=/neapi/feedback;;MUSIC_R_T=1471970211562; Max-Age=2147483647; Expires=Thu, 16 Feb 2090 18:56:22 GMT; Path=/openapi/clientlog;;__csrf=a06814d878bddd3a6b4474bb179a08fa; Max-Age=1296010; Expires=Sun, 13 Feb 2022 15:42:25 GMT; Path=/;;MUSIC_A_T=1471970204932; Max-Age=2147483647; Expires=Thu, 16 Feb 2090 18:56:22 GMT; Path=/eapi/clientlog;;NMTID=00OC-ENIumyuc54pEyFgAZp_7w6UPIAAAF-poBXuA; Max-Age=315360000; Expires=Tue, 27 Jan 2032 15:42:15 GMT; Path=/;;__remember_me=true; Max-Age=1296000; Expires=Sun, 13 Feb 2022 15:42:15 GMT; Path=/;;MUSIC_R_T=1471970211562; Max-Age=2147483647; Expires=Thu, 16 Feb 2090 18:56:22 GMT; Path=/weapi/clientlog;;MUSIC_U=4251d3763d1eaebc382624e10712a2e65e9b5233b736633d9669baa769b9e11c76e3aaac98e35ad6b72149bd3b14523943124f3fcebe94e446b14e3f0c3f8af94212382188fe1965; Max-Age=1296000; Expires=Sun, 13 Feb 2022 15:42:15 GMT; Path=/;;MUSIC_A_T=1471970204932; Max-Age=2147483647; Expires=Thu, 16 Feb 2090 18:56:22 GMT; Path=/neapi/feedback;;MUSIC_A_T=1471970204932; Max-Age=2147483647; Expires=Thu, 16 Feb 2090 18:56:22 GMT; Path=/weapi/feedback;;MUSIC_R_T=1471970211562; Max-Age=2147483647; Expires=Thu, 16 Feb 2090 18:56:22 GMT; Path=/api/clientlog;;MUSIC_A_T=1471970204932; Max-Age=2147483647; Expires=Thu, 16 Feb 2090 18:56:22 GMT; Path=/wapi/clientlog;;MUSIC_A_T=1471970204932; Max-Age=2147483647; Expires=Thu, 16 Feb 2090 18:56:22 GMT; Path=/api/feedback;;MUSIC_SNS=; Max-Age=0; Expires=Sat, 29 Jan 2022 15:42:15 GMT; Path=/;MUSIC_A_T=1471970204932; Max-Age=2147483647; Expires=Thu, 16 Feb 2090 18:56:22 GMT; Path=/weapi/clientlog;;MUSIC_A_T=1471970204932; Max-Age=2147483647; Expires=Thu, 16 Feb 2090 18:56:22 GMT; Path=/eapi/feedback;;MUSIC_A_T=1471970204932; Max-Age=2147483647; Expires=Thu, 16 Feb 2090 18:56:22 GMT; Path=/openapi/clientlog;;MUSIC_R_T=1471970211562; Max-Age=2147483647; Expires=Thu, 16 Feb 2090 18:56:22 GMT; Path=/weapi/feedback;;MUSIC_R_T=1471970211562; Max-Age=2147483647; Expires=Thu, 16 Feb 2090 18:56:22 GMT; Path=/eapi/clientlog;;MUSIC_R_T=1471970211562; Max-Age=2147483647; Expires=Thu, 16 Feb 2090 18:56:22 GMT; Path=/wapi/feedback;;MUSIC_A_T=1471970204932; Max-Age=2147483647; Expires=Thu, 16 Feb 2090 18:56:22 GMT; Path=/api/clientlog;;MUSIC_A_T=1471970204932; Max-Age=2147483647; Expires=Thu, 16 Feb 2090 18:56:22 GMT; Path=/neapi/clientlog;");
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
    $playlist = [];
    foreach ($data as $song) {
        $playlist[] = array(
            'name'   => $song->name,
            'artist' => implode('/', $song->artist),
            'url'    => API_URI . '?server=' . $song->source . '&type=url&id=' . $song->url_id . (AUTH ? '&auth=' . auth($song->source . 'url' . $song->url_id) : ''),
            'cover'  => API_URI . '?server=' . $song->source . '&type=cover&id=' . $song->url_id . (AUTH ? '&auth=' . auth($song->source . 'cover' . $song->url_id) : ''),
            'lrc'    => API_URI . '?server=' . $song->source . '&type=lrc&id=' . $song->url_id . (AUTH ? '&auth=' . auth($song->source . 'lrc' . $song->url_id) : '')
        );
    }
    $playlist = json_encode($playlist);

    if (CACHE) {
        // ! mkdir /cache/playlist
        file_put_contents($file_path, $playlist);
    }
    echo $playlist;
} else {

    $song = $api->song($id);
    if ($song == '[]') {
        echo '{"error":"unknown song id"}';
        exit;
    }

    $song = json_decode($song)[0];

    switch ($type) {
        case 'name':
            echo $song->name;
            break;

        case 'artist':
            echo implode('/', $song->artist);
            break;

        case 'url':
            $m_url = json_decode($api->url($song->url_id, 320))->url;
            if ($m_url == '') {
                exit;
            }
            if ($m_url[4] != 's') {
                $m_url = str_replace('http', 'https', $m_url);
            }
            header('Location: ' . $m_url);
            break;

        case 'cover':
            $c_url = json_decode($api->pic($song->pic_id, 90))->url;
            if ($c_url == '') {
                exit;
            }
            header('Location: ' . $c_url);
            break;

        case 'lrc':
            $lrc_data = json_decode($api->lyric($song->lyric_id));
            if ($lrc_data->lyric == '') {
                echo '[00:00.00]这似乎是一首纯音乐呢，请尽情欣赏它吧！';
                exit;
            }
            if ($lrc_data->tlyric == '') {
                echo $lrc_data->lyric;
                exit;
            }

            if (LYRIC_CN) {
                $lrc_arr = explode("\n", $lrc_data->lyric);
                $lrc_cn_arr = explode("\n", $lrc_data->tlyric);
                $lrc_cn_map = [];
                foreach ($lrc_cn_arr as $i => $v) {
                    if ($v == '') continue;
                    $line = explode(']', $v);
                    $lrc_cn_map[$line[0]] = $line[1];
                    unset($lrc_cn_arr[$i]);
                }
                foreach ($lrc_arr as $i => $v) {
                    if ($v == '') continue;
                    $key = explode(']', $v)[0];
                    if (!empty($lrc_cn_map[$key]) && $lrc_cn_map[$key] != '//') {
                        $lrc_arr[$i] .= ' (' . $lrc_cn_map[$key] . ')';
                        unset($lrc_cn_map[$key]);
                    }
                }
                echo implode("\n", $lrc_arr);
                exit;
            }

            echo $lrc_data->lyric;
            break;

        case 'single':
            $single = array(
                'name'   => $song->name,
                'artist' => implode('/', $song->artist),
                'url'    => API_URI . '?server=' . $song->source . '&type=url&id=' . $song->url_id . (AUTH ? '&auth=' . auth($song->source . 'url' . $song->url_id) : ''),
                'cover'  => API_URI . '?server=' . $song->source . '&type=cover&id=' . $song->url_id . (AUTH ? '&auth=' . auth($song->source . 'cover' . $song->url_id) : ''),
                'lrc'    => API_URI . '?server=' . $song->source . '&type=lrc&id=' . $song->url_id . (AUTH ? '&auth=' . auth($song->source . 'lrc' . $song->url_id) : '')
            );
            echo json_encode($single);
            break;

        default:
            echo '{"error":"unknown type"}';
    }
}
