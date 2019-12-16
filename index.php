<?php 
header('Content-Type: image/png');

error_reporting(E_ALL);
ini_set('display_errors', '1');

include('ogp.class.php');
ob_clean(); // include above does somehow print a newline char, get rid of it to prevent image error

function error_img() {
	$im = imagecreatetruecolor(500, 122);

	$red = imagecolorallocate($im, 230, 5, 5);
	$width = imagesx($im);
	$height = imagesy($im);

	imagestring($im, 5, 230, 55, "ERROR", $red);

	imagepng($im);

	imagedestroy($im);

	exit(0);
}

if (!isset($_GET['ipv4'], $_GET['port'])) {
	error_img();
}

if (filter_var($_GET['ipv4'], FILTER_VALIDATE_IP, FILTER_FLAG_IPV4 | FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE) === FALSE) {
	error_img();
}

$ip = $_GET['ipv4'];
$port = intval($_GET['port']) - 1;

if ($port < 5 || $port > 65535) {
	error_img();
}

$ogp = new OGP($ip, $port);

if (!$ogp->getStatus())
{
	echo("OGP Error: " . $ogp->error);
	die();
}

if (!isset($ogp->SERVERINFO['GameName']) ||
	!isset($ogp->SERVERINFO['ConnectPort']) ||
	!isset($ogp->SERVERINFO['SlotMax']) ||
	!isset($ogp->SERVERINFO['MODINFO']) ||
	!isset($ogp->SERVERINFO['MODINFO']['ModVersion']))
{
	error_img();
}

if ($ogp->SERVERINFO['GameName'] !== 'Horizon') {
	error_img();
}

function stripBBCode($text_to_search) {
	$pattern = '|[[\/\!]*?[^\[\]]*?]|si';
	$replace = '';
	return preg_replace($pattern, $replace, $text_to_search);
}

$hostname = $ogp->SERVERINFO['HostName'];
//$hostname = preg_replace('/[?]/', '', $hostname);
$hostname = preg_replace('/[\x00-\x1F\x80-\xFF]/', '', stripBBCode(strip_tags($hostname)));
//$hostname = iconv('UTF-8', 'ASCII//TRANSLIT', $hostname);

$im = imagecreatefrompng("sig02.png");

$blur = 5;

for ($i = 0; $i < $blur; $i++)
{
	imagefilter($im, IMG_FILTER_GAUSSIAN_BLUR);
}

$white = imagecolorallocate($im, 230, 230, 230);
$width = imagesx($im);
$height = imagesy($im);
$font = 3;

imagettftext($im, 11, 0, 10, 30, $white, "./RussoOne-Regular.ttf", $hostname);

imagettftext($im, 11, 0, 10, 70, $white, "./Roboto-Medium.ttf", "Server IP:");
imagettftext($im, 11, 0, 10, 90, $white, "./Roboto-Medium.ttf", $ip . ":" . $ogp->SERVERINFO['ConnectPort']);

imagettftext($im, 11, 0, 190, 70, $white, "./Roboto-Medium.ttf", "Players:");
imagettftext($im, 11, 0, 190, 90, $white, "./Roboto-Medium.ttf", $ogp->SERVERINFO['PlayerCount'] . " / " . $ogp->SERVERINFO['SlotMax']);

imagettftext($im, 11, 0, 290, 70, $white, "./Roboto-Medium.ttf", "Web:");
imagettftext($im, 11, 0, 290, 90, $white, "./Roboto-Medium.ttf", $ogp->SERVERINFO['MAPINFO']['MapURL']);

imagettftext($im, 9, 0, $width - 95, $height - 7, $white, "./RussoOne-Regular.ttf", "playonset.com");
imagettftext($im, 5, 0, 10, $height - 7, $white, "./Roboto-Medium.ttf", "Version: " . $ogp->SERVERINFO['MODINFO']['ModVersion']);

imagepng($im);

imagecolordeallocate($im, $white);
imagedestroy($im);

?>