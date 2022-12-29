<?php
namespace Namdevel;

class AnimeMaker{
	
	private $_source;
	private $_filename;
	private $_file_extension;
	
	public function __construct($source){
		$this->_source = $source;
		$info = pathinfo($source);
		$this->_filename = $info['filename'];
		$this->_file_extension = $info['extension'];
	}
	protected function build_header($signature){
		$headers = array(
			'Host: ai.tu.qq.com',
			'User-Agent: Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/108.0.0.0 Safari/537.36',
			'Content-Type: application/json',
			'Accept: application/json, text/plain, */*',
			'x-sign-value: ' .$signature,
			'x-sign-version: v1',
			'Origin: https://h5.tu.qq.com',
		);
		return $headers;
	}
	
	protected function crop_horizontal($img){
		$crop = imagecreatetruecolor(754, 492);
		imagecopy($crop, $img, 0, 0, 22, 547, 754, 492);
		imagejpeg($crop, $this->_filename . '_anime.' . $this->_file_extension);
		imagedestroy($img);
		imagedestroy($crop);
	}
	
	protected function crop_vertical($img){
		$crop = imagecreatetruecolor(467, 702);
		imagecopy($crop, $img, 0, 0, 511, 25, 467, 702);
		imagejpeg($crop, $this->_filename . '_anime.' . $this->_file_extension);
		imagedestroy($img);
		imagedestroy($crop);
	}
	
	protected function getAnimeImage(){
		echo "Create anime..." . PHP_EOL;
		$image = file_get_contents($this->_source);
		$b64 = base64_encode($image);
		$post = '{
			"busiId": "different_dimension_me_img_entry",
			"images": ["' . $b64 . '"],
			"extra": "{\"face_rects\":[],\"version\":2,\"language\":\"en\",\"platform\":\"web\",\"data_report\":{\"parent_trace_id\":\"e249ff20-6a1e-16cb-0750-c7fa37407d10\",\"root_channel\":\"\",\"level\":0}}"
		}';
		$sign = md5("https://h5.tu.qq.com" . strlen($post) . "HQ31X02e");
		$res = $this->http('https://ai.tu.qq.com/trpc.shadow_cv.ai_processor_cgi.AIProcessorCgi/Process', $post, $this->build_header($sign));
		$parse = json_decode($res, true);
		$resimg = json_decode($parse['extra'], true)["img_urls"][0];
		return $resimg;
	}
	
	public function createAnime() {
		$resimg = $this->getAnimeImage();
		$imgdata = $this->http($resimg);
		$img = imagecreatefromstring($imgdata);
		$width = imagesx($img);
		$height = imagesy($img);
		if ($width == 1000 && $height == 930) {
			echo "Complete..." . PHP_EOL;
			$this->crop_vertical($img);
		} else {
			echo "Complete..." . PHP_EOL;
			$this->crop_horizontal($img);
		}
	}
	
	protected function http($url, $post = false, $headers = false){
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, $url);
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
		curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
		curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		if ($post) {
			curl_setopt($ch, CURLOPT_POST, 1);
			curl_setopt($ch, CURLOPT_POSTFIELDS, $post);
		}
		if ($headers) {
			curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
			curl_setopt($ch, CURLOPT_HEADER, 0);
		}
		$response = curl_exec($ch);
		if (curl_exec($ch) === false) {
			echo 'Curl error: ' . curl_error($ch);
		}
		curl_close($ch);
		return $response;
	}
}