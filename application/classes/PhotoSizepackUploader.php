<?php

class PhotoSizepackUploader {

	static public function is_deleting($dont_upload_photo) {
		return $dont_upload_photo && $_POST['photo_changed'] == 1 && $_FILES['photo']['size'] == 0;
	}

	// ---------------------------------------------------
	// Функции загрузки фоток
	// ---------------------------------------------------
	static public function delete_photo_sizepack($directory, $filename, $sizes) {
		foreach ($sizes as $size) {
			$delete_path = $directory . $size['width'] . 'x' . $size['height'] . '/' . $filename;
			if (file_exists($delete_path)) {
				unlink($delete_path);
			}
		}
		$delete_path = $directory . 'original/' . $filename;
		if (file_exists($delete_path)) {
			unlink($delete_path);
		}
	}

	static public function save_photo_sizepack($directory, $filename, $sizes) {
		$new_filename = static::upload_photo_sizepack($directory, $filename, $sizes);
		if (!$new_filename) {
			return false;
		} else {
			return $new_filename;
		}
	}

	static public function change_photo_sizepack($directory, $filename_new, $filename_old, $sizes) {
		$new_filename = static::upload_photo_sizepack($directory, $filename_new, $sizes);
		if (!$new_filename) {
			return false;
		}
		static::delete_photo_sizepack($directory, $filename_old, $sizes);
		return $new_filename;
	}

	/**
	 * Функция для уменьшения png с сохранением прозрачности (нашел в интернете).
	 * @param $upload_file
	 * @param $upload_dir
	 * @param $normal_dimensions
	 */
	static public function save_png($upload_file, $upload_dir, $normal_dimensions) {
		$normal_width = $normal_dimensions['width'];
		$normal_height = $normal_dimensions['height'];
		list($uploadWidth, $uploadHeight) = getimagesize($upload_file);
		$srcImage = imagecreatefrompng($upload_file);
		$targetImage = imagecreatetruecolor($normal_width, $normal_height);
		imagealphablending($targetImage, false);
		imagesavealpha($targetImage, true);

		imagecopyresampled($targetImage, $srcImage,
			0, 0,
			0, 0,
			$normal_width, $normal_height,
			$uploadWidth, $uploadHeight);

		imagepng($targetImage, $upload_dir, 9);
	}

	static public function upload_photo_sizepack($directory, $filename, $sizes) {
		ini_set('max_execution_time', 90);
		ini_set("max_input_time", 600);

		$upload_dir_original = $directory . 'original/';

		$upload_file = $_FILES['photo']['tmp_name'];

		$ext_int = exif_imagetype($upload_file);
		switch ($ext_int) {
			case 1:
				$ext = '.gif';
				$imageTmp = imagecreatefromgif($upload_file);
				//сделал jpeg, потому что анимация все равно не работает. Надо бы исправить.
				$create_img_func = 'imagejpeg';
				break;
			case 2:
				$ext = '.jpeg';
				$imageTmp = imagecreatefromjpeg($upload_file);
				$create_img_func = 'imagejpeg';
				break;
			case 3:
				$ext = '.png';
				$imageTmp = imagecreatefrompng($upload_file);
				$create_img_func = 'imagepng';
				imagesavealpha($imageTmp, true);
				break;
		}
		if (!$ext_int) {
			return false;
		}

		$filename = $filename . $ext;
		$upload_original_path = $upload_dir_original . $filename;

		//сохранение оригинальной фотки - на случай, если потом добавлю еще размер, который будет больше текущего максимального
		$create_img_func($imageTmp, $directory . 'original/' . $filename);

		//изменениер размера (нормализация)
		$original_size = getimagesize($upload_file);
		foreach ($sizes as $size) {
			$normal_width = $original_size[0];
			$normal_height = $original_size[1];
			if ($normal_width > $size['width']) {
				$percents = $size['width'] / $normal_width * 100;
				$normal_width = $size['width'];
				$normal_height = $normal_height / 100 * $percents;
			}
			$imageNml = imagecreatetruecolor($normal_width, $normal_height);
			imagecopyresampled($imageNml, $imageTmp, 0, 0, 0, 0, $normal_width, $normal_height, $original_size[0], $original_size[1]);
			$catalog_name = $size['width'] . 'x' . $size['height'] . '/' . $filename;

			if ($create_img_func == 'imagepng') {
				static::save_png($upload_file, $directory . $catalog_name, array('width' => $normal_width, 'height' => $normal_height));
			} else {
				$create_img_func($imageNml, $directory . $catalog_name);
			}
			imagedestroy($imageNml);
		}
		imagedestroy($imageTmp);

		if (file_exists($upload_original_path)) {
			return $filename;
		} else {
			return false;
		}
	}

	static public function get_string_types($types) {
		$image_types = '';
		foreach ($types as $type) {
			$image_types .= static::get_string_type_by_int($type) . ' ';
		}
		$image_types = trim($image_types);
		return $image_types;
	}

	static public function get_string_type_by_int($type_int) {
		switch ($type_int) {
			case IMAGETYPE_GIF:
				return 'gif';
				break;
			case IMAGETYPE_JPEG:
				return 'jpeg jpg';
				break;
			case IMAGETYPE_PNG:
				return 'png';
				break;
		}
	}

	static public function find_validation_errors($exts, $maxsize) {
		if (!in_array(exif_imagetype($_FILES['photo']['tmp_name']), $exts)) {
			return 'Изображение имеет неподходящий формат';
		}
		if ($_FILES['photo']['size'] > $maxsize) {
			return 'Превышен максимальный размер файла';
		}
		return false;
	}

}
