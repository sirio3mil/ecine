<?php
include_once 'includes/media.inc';
include_once 'clases/reloj.php';
include_once 'vendor/autoload.php';
global $mysqli;
use Mhor\MediaInfo\MediaInfo;

$directorios = [
    '/multimedia'
];
$extensiones = [
    'mpg',
    'mpeg',
    'mpv',
    'avi',
    'mkv',
    'ogm',
    'avi',
    'divx',
    'wmv',
    'vob',
    'mov',
    'qt',
    '3gp',
    'mp4',
    'm4v',
    'rmvb',
    'flv'
];
$mediaInfo = new MediaInfo();
$query = "SELECT MAX(file_id) FROM file";
$updated_id_limit = $mysqli->fetch_value($query);
echo date("H:i:s"), " máximo id encontrado ", $updated_id_limit, "<br />";
try {
    $query = "SELECT CURRENT_TIMESTAMP";
    $start = new DateTime($mysqli->fetch_value($query));
    $file_extension = $file_last_modification_date = $file_name = $file_size = $codec = $codec_url = $codecs_video = $count_of_audio_streams = $count_of_menu_streams = $count_of_text_streams = $count_of_video_streams = $audio_codecs = $audio_format_list = $audio_language_list = $duration = $format = $format_url = $format_version = $overall_bit_rate = $writing_application = $writing_library = $video_format_list = $video_language_list = $complete_name = null;
    $query = "INSERT INTO file (file_extension, file_last_modification_date, file_name, file_size, codec, codec_url, codecs_video, count_of_audio_streams, count_of_menu_streams, count_of_text_streams, count_of_video_streams, audio_codecs, audio_format_list, audio_language_list, duration, format, format_url, format_version, overall_bit_rate, writing_application, writing_library, video_format_list, video_language_list, complete_name, comprobado) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, CURRENT_TIMESTAMP) ON DUPLICATE KEY UPDATE file_id = LAST_INSERT_ID(file_id), complete_name = ?, existente = 1, comprobado = CURRENT_TIMESTAMP";
    $stmtFile = $mysqli->prepare($query);
    if (! $stmtFile) {
        throw new Exception($mysqli->error);
    }
    if (! $stmtFile->bind_param("sssisssiiiisssisssissssss", $file_extension, $file_last_modification_date, $file_name, $file_size, $codec, $codec_url, $codecs_video, $count_of_audio_streams, $count_of_menu_streams, $count_of_text_streams, $count_of_video_streams, $audio_codecs, $audio_format_list, $audio_language_list, $duration, $format, $format_url, $format_version, $overall_bit_rate, $writing_application, $writing_library, $video_format_list, $video_language_list, $complete_name, $complete_name)) {
        throw new Exception($stmtFile->error);
    }
    $encoded_library_version = $encoded_library_name = $file = $format_profile = $format_settings = $width = $height = $display_aspect_ratio = $frame_count = $frame_rate = $frame_rate_mode = $color_space = $chroma_subsampling = $bit_depth = $scan_type = $streamorder = $commercial_name = $internet_media_type = $codec_family = $codec_info = $codec_profile = $codec_id = $codec_id_url = $pixel_aspect_ratio = $resolution = $colorimetry = $interlacement = $delay = $language = $unique_id = $colour_description_present = $forced = null;
    $query = "INSERT INTO file_video (file, format, format_profile, format_settings, format_url, duration, width, height, display_aspect_ratio, frame_count, frame_rate, frame_rate_mode, color_space, chroma_subsampling, bit_depth, scan_type, streamorder, commercial_name, internet_media_type, codec, codec_family, codec_info, codec_url, codec_profile, codec_id, codec_id_url, pixel_aspect_ratio, resolution, colorimetry, interlacement, delay, writing_library, encoded_library_name, encoded_library_version, language, unique_id, colour_description_present, forced) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?) ON DUPLICATE KEY UPDATE file_video_id = LAST_INSERT_ID(file_video_id)";
    $stmtVideo = $mysqli->prepare($query);
    if (! $stmtVideo) {
        throw new Exception($mysqli->error);
    }
    if (! $stmtVideo->bind_param("issssiiididsssisisssssssssdississsssss", $file, $format, $format_profile, $format_settings, $format_url, $duration, $width, $height, $display_aspect_ratio, $frame_count, $frame_rate, $frame_rate_mode, $color_space, $chroma_subsampling, $bit_depth, $scan_type, $streamorder, $commercial_name, $internet_media_type, $codec, $codec_family, $codec_info, $codec_url, $codec_profile, $codec_id, $codec_id_url, $pixel_aspect_ratio, $resolution, $colorimetry, $interlacement, $delay, $writing_library, $encoded_library_name, $encoded_library_version, $language, $unique_id, $colour_description_present, $forced)) {
        throw new Exception($stmtVideo->error);
    }
    $channel_s = $channel_positions = $sampling_rate = $compression_mode = $samples_count = $delay_origin = $channellayout = $default = null;
    $query = "INSERT INTO file_audio (file, format, format_profile, duration, channel_s, channel_positions, sampling_rate, compression_mode, streamorder, commercial_name, codec, codec_family, codec_id, samples_count, delay, delay_origin, channellayout, language, unique_id, `default`, forced) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?) ON DUPLICATE KEY UPDATE file_audio_id = LAST_INSERT_ID(file_audio_id)";
    $stmtAudio = $mysqli->prepare($query);
    if (! $stmtAudio) {
        throw new Exception($mysqli->error);
    }
    if (! $stmtAudio->bind_param("issiisisisssiiissssss", $file, $format, $format_profile, $duration, $channel_s, $channel_positions, $sampling_rate, $compression_mode, $streamorder, $commercial_name, $codec, $codec_family, $codec_id, $samples_count, $delay, $delay_origin, $channellayout, $language, $unique_id, $default, $forced)) {
        throw new Exception($stmtAudio->error);
    }
    foreach ($directorios as $directorio) {
        echo date("H:i:s"), " procesando ", $directorio, "<br />";
        $iterator = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($directorio, FilesystemIterator::SKIP_DOTS), RecursiveIteratorIterator::SELF_FIRST);
        foreach ($iterator as $path) {
            if (! $path->isDir()) {
                $complete_name = $path->__toString();
                if (! file_exists($complete_name)) {
                    echo date("H:i:s"), " <strong>error</strong> omitido ", $complete_name, "<br />";
                    continue;
                }
                $info = pathinfo($complete_name);
                if(!array_key_exists('extension', $info)){
                    continue;
                }
                $file_extension = strtolower($info['extension']);
                if (! in_array($file_extension, $extensiones)) {
                    continue;
                }
                try {
                    $mediaInfoContainer = $mediaInfo->getInfo($complete_name);
                    $general = $mediaInfoContainer->getGeneral();
                    $videos = $mediaInfoContainer->getVideos();
                    $audios = $mediaInfoContainer->getAudios();
                    $subtitles = $mediaInfoContainer->getSubtitles();
                    $menus = $mediaInfoContainer->getMenus();
                } catch (Exception $e) {
                    echo date("H:i:s"), " <strong>error</strong> captura ", $complete_name, "<br />";
                    if (trim($e->getMessage())) {
                        echo date("H:i:s"), " ", $e->getMessage(), "<br />";
                    }
                    echo date("H:i:s"), " linea ", $e->getLine(), " ", $e->getFile(), "<br />";
                    continue;
                }
                $file_extension = $file_last_modification_date = $file_name = $file_size = $codec = $codec_url = $codecs_video = $count_of_audio_streams = $count_of_menu_streams = $count_of_text_streams = $count_of_video_streams = $audio_codecs = $audio_format_list = $audio_language_list = $duration = $format = $format_url = $format_version = $overall_bit_rate = $writing_application = $writing_library = $video_format_list = $video_language_list = $complete_name = $complete_name = null;
                extract($general->__toArray(), EXTR_IF_EXISTS);
                $format = $format->getFullName();
                $codec = $codec->getFullName();
                $file_size = intval($file_size->getBit());
                $duration = intval($duration->getMilliseconds());
                $file_last_modification_date = $file_last_modification_date->format("Y-m-d H:i:s");
                $overall_bit_rate = intval($overall_bit_rate->getShortName());
                $writing_application = $writing_application[0];
                if ($writing_library) {
                    $writing_library = $writing_library->getFullName();
                }
                $count_of_video_streams = count($videos);
                $count_of_audio_streams = count($audios);
                $count_of_text_streams = count($subtitles);
                $count_of_menu_streams = count($menus);
                if (! $stmtFile->execute()) {
                    throw new Exception($stmtFile->error);
                }
                $file = $mysqli->insert_id;
                if ($file <= $updated_id_limit) {
                    echo date("H:i:s"), " comprobado archivo ", $complete_name, " con el id ", $file, "<br />";
                    continue;
                }
                echo date("H:i:s"), " insertando archivo <strong>", $complete_name, "</strong> con el id ", $file, "<br />";
                foreach ($videos as $video) {
                    $data = $video->__toArray();
                    $format = $format_profile = $format_settings = $format_url = $duration = $width = $height = $display_aspect_ratio = $frame_count = $frame_rate = $frame_rate_mode = $color_space = $chroma_subsampling = $bit_depth = $scan_type = $streamorder = $commercial_name = $internet_media_type = $codec = $codec_family = $codec_info = $codec_url = $codec_profile = $codec_id = $codec_id_url = $pixel_aspect_ratio = $resolution = $colorimetry = $interlacement = $delay = $writing_library = $encoded_library_name = $encoded_library_version = $language = $unique_id = $colour_description_present = $forced = null;
                    extract($data, EXTR_IF_EXISTS);
                    if ($unique_id) {
                        $unique_id = $unique_id->getFullName();
                    } else {
                        $unique_id = sha1(base64_encode(serialize($data)));
                    }
                    $format = $format->getFullName();
                    $codec = $codec->getShortName();
                    $duration = intval($duration->getMilliseconds());
                    $width = $width->getAbsoluteValue();
                    $height = $height->getAbsoluteValue();
                    $display_aspect_ratio = $display_aspect_ratio->getAbsoluteValue();
                    if ($frame_rate_mode) {
                        $frame_rate_mode = $frame_rate_mode->getShortName();
                    }
                    if ($frame_rate) {
                        $frame_rate = explode(" ", $frame_rate->getTextValue())[0];
                    }
                    if ($resolution) {
                        $resolution = $resolution->getAbsoluteValue();
                    }
                    if ($bit_depth) {
                        $bit_depth = $bit_depth->getAbsoluteValue();
                    }
                    if ($scan_type) {
                        $scan_type = $scan_type->getShortName();
                    }
                    if ($interlacement) {
                        $interlacement = $interlacement->getShortName();
                    }
                    if ($writing_library) {
                        $writing_library = $writing_library->getShortName();
                    }
                    if ($language && is_array($language)) {
                        $language = $language[0];
                    }
                    if ($chroma_subsampling && is_array($chroma_subsampling)) {
                        $chroma_subsampling = $chroma_subsampling[0];
                    }
                    if ($forced) {
                        $forced = $forced->getShortName();
                    }
                    if ($delay) {
                        $delay = $delay->getMilliseconds();
                    }
                    if (! $stmtVideo->execute()) {
                        throw new Exception($stmtVideo->error);
                    }
                    echo date("H:i:s"), " insertando video ", $codec, "<br />";
                }
                foreach ($audios as $audio) {
                    $data = $audio->__toArray();
                    $format = $format_profile = $duration = $channel_s = $channel_positions = $sampling_rate = $compression_mode = $streamorder = $commercial_name = $codec = $codec_family = $codec_id = $samples_count = $delay = $delay_origin = $channellayout = $language = $unique_id = $default = $forced = null;
                    extract($data, EXTR_IF_EXISTS);
                    if ($unique_id) {
                        $unique_id = $unique_id->getFullName();
                    } else {
                        $unique_id = sha1(base64_encode(serialize($data)));
                    }
                    $format = $format->getFullName();
                    $codec = $codec->getShortName();
                    $duration = intval($duration->getMilliseconds());
                    $channel_s = $channel_s->getAbsoluteValue();
                    if ($channel_positions) {
                        $channel_positions = $channel_positions->getShortName();
                    }
                    if ($sampling_rate) {
                        $sampling_rate = $sampling_rate->getAbsoluteValue();
                    }
                    if ($compression_mode) {
                        $compression_mode = $compression_mode->getFullName();
                    }
                    if ($delay) {
                        $delay = $delay->getMilliseconds();
                    }
                    if ($delay_origin) {
                        $delay_origin = $delay_origin->getShortName();
                    }
                    if ($language && is_array($language)) {
                        $language = $language[0];
                    }
                    if ($default) {
                        $default = $default->getShortName();
                    }
                    if ($forced) {
                        $forced = $forced->getShortName();
                    }
                    if (! $stmtAudio->execute()) {
                        throw new Exception($stmtAudio->error);
                    }
                    echo date("H:i:s"), " insertando audio ", $codec, "<br />";
                }
            }
        }
    }
    $start->sub(new DateInterval("PT1M"));
    $desde = $start->format("Y-m-d H:i:s");
    $query = "UPDATE file SET
                existente = 0
            WHERE existente = 1
                AND IFNULL(comprobado, ?) <= ?
                AND (
                    complete_name like '/multimedia/cine/Pendientes/%' OR complete_name like '/multimedia/descargas/%'
                )";
    $stmt = $mysqli->prepare($query);
    if (! $stmt) {
        throw new Exception($mysqli->error);
    }
    if (! $stmt->bind_param("ss", $desde, $desde)) {
        throw new Exception($stmtVideo->error);
    }
    if (! $stmt->execute()) {
        throw new Exception($stmt->error);
    }
    echo date("H:i:s"), " desactivados ", $stmt->affected_rows, "<br />";
} catch (Exception $e) {
    if (trim($e->getMessage())) {
        echo date("H:i:s"), " ", $e->getMessage(), "<br />";
    }
    echo date("H:i:s"), " linea ", $e->getLine(), " ", $e->getFile(), "<br />";
} finally {
    echo Reloj::DevolverDuracionFormateada(Reloj::CalcularDuracionScript());
}