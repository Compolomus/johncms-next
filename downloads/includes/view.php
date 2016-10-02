<?php

defined('_IN_JOHNCMS') or die('Error: restricted access');

/** @var PDO $db */
$db = App::getContainer()->get(PDO::class);
require '../incfiles/head.php';
require 'classes/download.php';

// Выводим файл
$req_down = $db->query("SELECT * FROM `download__files` WHERE `id` = '" . $id . "' AND (`type` = 2 OR `type` = 3)  LIMIT 1");
$res_down = $req_down->fetch();

if (!$req_down->rowCount() || !is_file($res_down['dir'] . '/' . $res_down['name'])) {
    echo _t('File not found') . '<br><a href="?">' . _t('Downloads') . '</a>';
    exit;
}

$title_pages = htmlspecialchars(mb_substr($res_down['rus_name'], 0, 30));
$textl = mb_strlen($res_down['rus_name']) > 30 ? $title_pages . '...' : $title_pages;

if ($res_down['type'] == 3) {
    echo '<div class="rmenu">' . _t('The file is on moderation') . '</div>';
    if ($rights < 6 && $rights != 4) {
        exit;
    }
}


echo '<div class="phdr"><b>' . htmlspecialchars($res_down['rus_name']) . '</b></div>';
$format_file = htmlspecialchars($res_down['name']);

// Управление закладками
if ($user_id) {
    $bookmark = $db->query("SELECT COUNT(*) FROM `download__bookmark` WHERE `file_id` = " . $id . "  AND `user_id` = " . $user_id)->fetchColumn();

    if (isset($_GET['addBookmark']) && !$bookmark) {
        $db->exec("INSERT INTO `download__bookmark` SET `file_id`='" . $id . "', `user_id`=" . $user_id);
        $bookmark = 1;
    } elseif (isset($_GET['delBookmark']) && $bookmark) {
        $db->exec("DELETE FROM `download__bookmark` WHERE `file_id`='" . $id . "' AND `user_id`=" . $user_id);
        $bookmark = 0;
    }

    echo '<div class="topmenu">';

    if (!$bookmark) {
        echo '<a href="?act=view&amp;id=' . $id . '&amp;addBookmark">' . _t('Add to Favorites') . '</a>';
    } else {
        echo '<a href="?act=view&amp;id=' . $id . '&amp;delBookmark">' . _t('Remove from Favorites') . '</a>';
    }

    echo '</div>';
}

// Получаем список скриншотов
$text_info = '';
$screen = [];

if (is_dir($screens_path . '/' . $id)) {
    $dir = opendir($screens_path . '/' . $id);

    while ($file = readdir($dir)) {
        if (($file != '.') && ($file != "..") && ($file != "name.dat") && ($file != ".svn") && ($file != "index.php")) {
            $screen[] = $screens_path . '/' . $id . '/' . $file;
        }
    }

    closedir($dir);
}

// Плейер видео файлов
if (($format_file == 'mp4' || $format_file == 'flv') && !Functions::isMobile()) {
    echo '<div class="menu"><b>' . _t('View') . '</b><br>
	<div id="mediaplayer">JW Player goes here</div>
    <script type="text/javascript" src="' . $homeurl . 'files/download/system/players/mediaplayer-5.7-viral/jwplayer.js"></script>
    <script type="text/javascript">
        jwplayer("mediaplayer").setup({
            flashplayer: "' . $homeurl . 'files/download/system/players/mediaplayer-5.7-viral/player.swf",
            file: "' . $homeurl . $res_down['dir'] . '/' . $res_down['name'] . '",
            image: "' . $homeurl . 'assets/misc/thumbinal.php?type=3&amp;img=' . rawurlencode($screen[0]) . '"
        });
    </script></div>';
}

// Получаем данные
if ($format_file == 'jpg' || $format_file == 'jpeg' || $format_file == 'gif' || $format_file == 'png') {
    $info_file = getimagesize($res_down['dir'] . '/' . $res_down['name']);
    //echo '<div class="gmenu"><img src="' . Vars::$HOME_URL . 'assets/misc/thumbinal.php?type=2&amp;img=' . rawurlencode($res_down['dir'] . '/' . $res_down['name']) . '" alt="preview" /></div>';
    $screen[] = $res_down['dir'] . '/' . $res_down['name'];
    $text_info = '<b>' . _t('Resolution') . ': </b>' . $info_file[0] . 'x' . $info_file[1] . ' px<br>';
} else {
    if (($format_file == '3gp' || $format_file == 'avi' || $format_file == 'mp4') && !$screen && $set_down['video_screen']) {
        $screen[] = Download::screenAuto($res_down['dir'] . '/' . $res_down['name'], $res_down['id'], $format_file);
    } elseif (($format_file == 'thm' || $format_file == 'nth') && !$screen && $set_down['theme_screen']) {
        $screen[] = Download::screenAuto($res_down['dir'] . '/' . $res_down['name'], $res_down['id'], $format_file);
    } elseif ($format_file == 'mp3') {
        if (!Functions::isMobile()) {//TODO: убрать Flash
            $text_info = '<object type="application/x-shockwave-flash" data="' . $homeurl . 'files/download/system/players/player.swf" width="240" height="20" id="dewplayer" name="dewplayer">' .
                '<param name="wmode" value="transparent" /><param name="movie" value="' . $homeurl . 'files/download/system/download/players/player.swf" />' .
                '<param name="flashVars" value="mp3=' . $homeurl . str_replace('../', '', $res_down['dir']) . '/' . $res_down['name'] . '" /> </object><br>';
        }

        require(SYSPATH . 'lib/getid3/getid3.php');
        $getID3 = new getID3;
        $getID3->encoding = 'cp1251';
        $getid = $getID3->analyze($res_down['dir'] . '/' . $res_down['name']);
        $mp3info = true;

        if (!empty($getid['tags']['id3v2'])) {
            $tagsArray = $getid['tags']['id3v2'];
        } elseif (!empty($getid['tags']['id3v1'])) {
            $tagsArray = $getid['tags']['id3v1'];
        } else {
            $mp3info = false;
        }

        $text_info .= '<b>' . _t('Channels') . '</b>: ' . $getid['audio']['channels'] . ' (' . $getid['audio']['channelmode'] . ')<br>' .
            '<b>' . _t('Sample rate') . '</b>: ' . ceil($getid['audio']['sample_rate'] / 1000) . ' KHz<br>' .
            '<b>' . _t('Bitrate') . '</b>: ' . ceil($getid['audio']['bitrate'] / 1000) . ' Kbit/s<br>' .
            '<b>' . _t('Duration') . '</b>: ' . date('i:s', $getid['playtime_seconds']) . '<br>';

        if ($mp3info) {
            if (isset($tagsArray['artist'][0])) {
                $text_info .= '<b>' . _t('Artist') . '</b>: ' . Download::mp3tagsOut($tagsArray['artist'][0]) . '<br>';
            }
            if (isset($tagsArray['title'][0])) {
                $text_info .= '<b>' . _t('Title') . '</b>: ' . Download::mp3tagsOut($tagsArray['title'][0]) . '<br>';
            }
            if (isset($tagsArray['album'][0])) {
                $text_info .= '<b>' . _t('Album') . '</b>: ' . Download::mp3tagsOut($tagsArray['album'][0]) . '<br>';
            }
            if (isset($tagsArray['genre'][0])) {
                $text_info .= '<b>' . _t('Genre') . '</b>: ' . Download::mp3tagsOut($tagsArray['genre'][0]) . '<br>';
            }
            if (intval($tagsArray['year'][0])) {
                $text_info .= '<b>' . _t('Year') . '</b>: ' . (int)$tagsArray['year'][0] . '<br>';
            }
        }
    }
}

// Выводим скриншоты
if ($screen) {
    $total = count($screen);
    if ($total > 1) {
        if ($page >= $total) {
            $page = $total;
        }

        echo '<div class="topmenu"> ' . Functions::displayPagination('?act=view&amp;id=' . $id . '&amp;', $page - 1, $total, 1) . '</div>' .
            '<div class="gmenu"><b>' . _t('Screenshot') . ' (' . $page . '/' . $total . '):</b><br>' .
            '<img src="' . $homeurl . 'assets/misc/thumbinal.php?type=2&amp;img=' . rawurlencode($screen[$page - 1]) . '" alt="screen" /></div>';
    } else {
        echo '<div class="gmenu"><b>' . _t('Screenshot') . ':</b><br>' .
            '<img src="' . $homeurl . 'assets/misc/thumbinal.php?type=2&amp;img=' . rawurlencode($screen[0]) . '" alt="screen" /></div>';
    }
}

// Выводим данные
//TODO: Разобраться с пользователем
$user = 'Admin';
echo '<div class="list1"><b>' . _t('Name of the server') . ':</b> ' . $res_down['name'] . '<br>' .//TODO: Разобраться с фразой
    '<b>' . _t('Uploaded by') . ':</b> ' . $user . '<br>' . $text_info .
    '<b>' . _t('Downloads') . ':</b> ' . $res_down['field'] . '<br>';

if ($res_down['about']) {
    echo '<b>' . _t('Description') . ':</b> ' . htmlspecialchars($res_down['about']);
}

echo '<div class="sub"></div>';

// Рейтинг файла
$file_rate = explode('|', $res_down['rate']);
if ((isset($_GET['plus']) || isset($_GET['minus'])) && !isset($_SESSION['rate_file_' . $id]) && $user_id) {
    if (isset($_GET['plus'])) {
        $file_rate[0] = $file_rate[0] + 1;
    } else {
        $file_rate[1] = $file_rate[1] + 1;
    }

    $db->exec("UPDATE `download__files` SET `rate`='" . $file_rate[0] . '|' . $file_rate[1] . "' WHERE `id`=" . $id);
    echo '<b><span class="green">' . _t('Voice adopted') . '</span></b><br>';
    $_SESSION['rate_file_' . $id] = true;
}

$sum = ($file_rate[1] + $file_rate[0]) ? round(100 / ($file_rate[1] + $file_rate[0]) * $file_rate[0]) : 50;
echo '<b>' . _t('Rating') . ' </b>';

if (!isset($_SESSION['rate_file_' . $id]) && $user_id) {
    echo '(<a href="?act=view&amp;id=' . $id . '&amp;plus">+</a>/<a href="?act=view&amp;id=' . $id . '&amp;minus">-</a>)';
} else {
    echo '(+/-)';
}

echo ': <b><span class="green">' . $file_rate[0] . '</span>/<span class="red">' . $file_rate[1] . '</span></b><br>' .
    '<img src="' . $homeurl . 'assets/misc/rating.php?img=' . $sum . '" alt="' . _t('Rating') . '" />';

// Скачка изображения в особом размере
if ($format_file == 'jpg' || $format_file == 'jpeg' || $format_file == 'gif' || $format_file == 'png') {
    $array = ['101x80', '128x128', '128x160', '176x176', '176x208', '176x220', '208x208', '208x320', '240x266', '240x320', '240x432', '352x416', '480x800'];
    echo '<div class="sub"></div>' .
        '<form action="?" method="get">' .
        '<input name="id" type="hidden" value="' . $id . '" />' .
        '<input name="act" type="hidden" value="custom_size" />' .
        _t('Custom size') . ': ' . '<select name="img_size">';
    $img = 0;

    foreach ($array as $v) {
        echo '<option value="' . $img . '">' . $v . '</option>';
        ++$img;
    }

    echo '</select><br>' .
        _t('Quality') . ': <select name="val">' .
        '<option value="100">100</option>' .
        '<option value="90">90</option>' .
        '<option value="80">80</option>' .
        '<option value="70">70</option>' .
        '<option value="60">60</option>' .
        '<option value="50">50</option>' .
        '</select><br>' .
        '<input name="proportion" type="checkbox" value="1" />&nbsp;' . _t('Keep aspect ratio') . '<br>' .
        '<input type="submit" value="' . _t('Download') . '" /></form>';
}

//TODO: Переделать на получение настроек из таблицы модулей
if ($set['mod_down_comm'] || $rights >= 7) {
    echo '<div class="sub"></div><a href="?act=comments&amp;id=' . $res_down['id'] . '">' . _t('Comments') . '</a> (' . $res_down['total'] . ')';
}

echo '</div>';

// Запрашиваем дополнительные файлы
$req_file_more = $db->query("SELECT * FROM `download__more` WHERE `refid` = " . $id . " ORDER BY `time` ASC");
$total_files_more = $req_file_more->rowCount();

// Скачка файла
echo '<div class="phdr"><b>' . ($total_files_more ? _t('Files for Download') : _t('Files for Download')) . '</b></div>' .
    '<div class="list1">' . Download::downloadLlink([
        'format' => $format_file,
        'res'    => $res_down,
    ]) . '</div>';

// Дополнительные файлы
if ($total_files_more) {
    $i = 0;
    while ($res_file_more = $req_file_more->fetch()) {
        $res_file_more['dir'] = $res_down['dir'];
        $res_file_more['text'] = $res_file_more['rus_name'];
        echo (($i++ % 2) ? '<div class="list1">' : '<div class="list2">') .
            Download::downloadLlink([
                'format' => Functions::format($res_file_more['name']),
                'res'    => $res_file_more,
                'more'   => $res_file_more['id'],
            ]) . '</div>';
    }
}

// Навигация
echo '<div class="phdr">' . Download::navigation(['dir' => $res_down['dir'], 'refid' => 1, 'count' => 0]) . '</div>';

// Управление файлами
if ($rights > 6 || $rights == 4) {
    echo '<p><div class="func">' .
        '<a href="?act=edit_file&amp;id=' . $id . '">' . _t('Edit File') . '</a><br>' .
        '<a href="?act=edit_about&amp;id=' . $id . '">' . _t('Edit Description') . '</a><br>' .
        '<a href="?act=edit_screen&amp;id=' . $id . '">' . _t('Managing Screenshots') . '</a><br>' .
        '<a href="?act=files_more&amp;id=' . $id . '">' . _t('Additional Files') . '</a><br>' .
        '<a href="?act=delete_file&amp;id=' . $id . '">' . _t('Delete File') . '</a>';

    if ($rights > 6) {
        echo '<br><a href="?act=transfer_file&amp;id=' . $id . '">' . _t('Move File') . '</a>';
        if ($format_file == 'mp3') {
            echo '<br><a href="?act=mp3tags&amp;id=' . $id . '">' . _t('Edit MP3 Tags') . '</a>';
        }
    }

    echo '</div></p>';
}

require '../incfiles/end.php';