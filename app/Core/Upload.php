<?php
namespace App\Core;
class Upload {
    public static function discardNew(array $paths): void {
        foreach ($paths as $path) {
            if (!preg_match('~^uploads/\d{4}/\d{2}/[a-f0-9]{36}\.(webp|jpg|png|gif|pdf|mp4|webm)$~D',$path)) continue;
            $absolute=dirname(__DIR__,2).'/public/'.$path;
            if (is_file($absolute)) unlink($absolute);
        }
    }
    public static function store(array $file, bool $imagesOnly=false, bool $preserveGif=false): array {
        if (($file['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_OK || !is_uploaded_file($file['tmp_name'])) throw new \InvalidArgumentException('The upload failed. Please choose a file and try again.');
        if ($file['size'] > 25*1024*1024) throw new \InvalidArgumentException('Files must be smaller than 25 MB.');
        $mime=(new \finfo(FILEINFO_MIME_TYPE))->file($file['tmp_name']);
        $types=['image/jpeg'=>'jpg','image/png'=>'png','image/webp'=>'webp','image/gif'=>'gif'];
        if (!$imagesOnly) $types+=['application/pdf'=>'pdf','video/mp4'=>'mp4','video/webm'=>'webm'];
        if (!isset($types[$mime])) throw new \InvalidArgumentException('Unsupported file. Use JPG, PNG, WebP, GIF'.($imagesOnly ? '.' : ', PDF, MP4 or WebM.'));
        $folder=dirname(__DIR__,2).'/public/uploads/'.date('Y/m');
        if (!is_dir($folder)) mkdir($folder,0755,true);
        $name=bin2hex(random_bytes(18)).'.'.$types[$mime];
        if (str_starts_with($mime,'image/')) {
            $dimensions=@getimagesize($file['tmp_name']);
            if (!$dimensions || $dimensions[0]*$dimensions[1]>24000000) throw new \InvalidArgumentException('Image is invalid or exceeds 24 megapixels.');
            if (function_exists('imagewebp') && !($preserveGif && $mime==='image/gif')) {
                $image=@imagecreatefromstring(file_get_contents($file['tmp_name']));
                if (!$image) throw new \InvalidArgumentException('Cannot decode this image.');
                $width=imagesx($image); $height=imagesy($image);
                if ($width>2000 || $height>2000) { $ratio=min(2000/$width,2000/$height); $scaled=imagescale($image,(int)($width*$ratio),(int)($height*$ratio)); imagedestroy($image); $image=$scaled; }
                imagepalettetotruecolor($image); imagealphablending($image,true); imagesavealpha($image,true);
                $name=pathinfo($name,PATHINFO_FILENAME).'.webp';
                if (!imagewebp($image,$folder.'/'.$name,82)) throw new \RuntimeException('Cannot save image.');
                imagedestroy($image); $mime='image/webp';
            } elseif (!move_uploaded_file($file['tmp_name'],$folder.'/'.$name)) throw new \RuntimeException('Cannot store upload.');
        } elseif (!move_uploaded_file($file['tmp_name'],$folder.'/'.$name)) throw new \RuntimeException('Cannot store upload.');
        return ['file_name'=>mb_substr(basename($file['name']),0,250),'file_path'=>'uploads/'.date('Y/m').'/'.$name,'file_type'=>$mime];
    }
}
