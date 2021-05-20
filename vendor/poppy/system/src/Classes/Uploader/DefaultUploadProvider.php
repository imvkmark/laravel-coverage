<?php

namespace Poppy\System\Classes\Uploader;

use Carbon\Carbon;
use Exception;
use Illuminate\Filesystem\FilesystemAdapter;
use Illuminate\Support\Str;
use Intervention\Image\Constraint;
use Intervention\Image\Image;
use Poppy\Framework\Classes\Traits\AppTrait;
use Poppy\Framework\Helper\FileHelper;
use Poppy\Framework\Helper\UtilHelper;
use Poppy\System\Classes\Contracts\UploadContract;
use Psr\Http\Message\StreamInterface;
use Storage;
use Symfony\Component\HttpFoundation\File\UploadedFile;

/**
 * 图片上传类
 */
class DefaultUploadProvider implements UploadContract
{
    use AppTrait;

    /**
     * @var string 目标路径
     */
    protected $destination = '';

    /**
     * @var string 目标磁盘
     */
    protected $disk = 'public';

    /**
     * 图片扩展的描述
     * @var array
     * @deprecated 3.1
     * @removed    4.0
     */
    protected static $extensions = [
        'images' => [
            'extension'   => ['jpg', 'jpeg', 'png', 'gif'],
            'description' => '请选择图片',
        ],
        'file'   => [
            'extension'   => ['zip', 'rp', 'rplib', 'svga', 'xls', 'xlsx', 'doc', 'docx', 'ppt', 'pptx', 'pdf'],
            'description' => '选择文件',
        ],
        'video'  => [
            'extension'   => ['mp4', 'rm', 'rmvb', 'wmv'],
            'description' => '请选择视频文件',
        ],
        'audio'  => [
            'extension'   => ['mp3', 'm4a', 'wav', 'aac'],
            'description' => '请选择音频文件',
        ],
    ];
    /**
     * @var string 文件夹
     */
    private $folder;

    /**
     * @var string 返回地址
     */
    private $returnUrl;

    /**
     * @var array 允许上传的扩展
     */
    private $allowedExtensions = ['zip'];

    /**
     * @var int 默认图片质量
     */
    private $quality = 70;

    /**
     * 重新设置大小时候的阈值
     * @var int
     */
    private $resizeDistrict = 1920;

    /**
     * @var string 图片mime类型
     */
    private $mimeType;

    public function __construct()
    {
        $this->folder    = (is_production() ? '' : 'dev/') . 'uploads';
        $this->returnUrl = config('app.url') . '/';
    }


    public function setFolder($folder = 'uploads')
    {
        $this->folder = (is_production() ? '' : 'dev/') . $folder;
    }

    /**
     * 设置类型
     * @param string $type
     */
    public function setType(string $type): void
    {
        $this->folder = (is_production() ? '' : 'dev/') . $type;
        $extensions   = UploaderTypes::kvExt($type);
        if ($extensions) {
            $this->setExtension($extensions);
        }
    }

    /**
     * 设置返回地址
     * @param string $url 地址
     */
    public function setReturnUrl($url)
    {
        if (!Str::endsWith($url, '/')) {
            $url .= '/';
        }
        $this->returnUrl = $url;
    }

    /**
     * Set Extension
     * @param array $extension 支持的扩展
     */
    public function setExtension($extension = [])
    {
        $this->allowedExtensions = $extension;
    }

    /**
     * District Size.
     * @param int $resize 设置resize 的区域
     */
    public function setResizeDistrict($resize)
    {
        $this->resizeDistrict = $resize;
    }

    /**
     * 设置图片压缩质量
     * @param int $quality
     */
    public function setQuality(int $quality): void
    {
        $this->quality = $quality;
    }

    /**
     * 设置图片mime类型
     * @param $mime_type
     */
    public function setMimeType($mime_type)
    {
        $this->mimeType = $mime_type;
    }

    /**
     * @inheritDoc
     */
    public function saveFile(UploadedFile $file): bool
    {
        if (!$file) {
            return $this->setError('没有上传任何文件');
        }

        if ($file->isValid()) {
            // 存储
            if ($file->getClientOriginalExtension() && !in_array(strtolower($file->getClientOriginalExtension()), $this->allowedExtensions, true)) {
                return $this->setError('你只允许上传 "' . implode(',', $this->allowedExtensions) . '" 格式');
            }

            // 磁盘对象
            $Disk             = $this->storage();
            $extension        = $file->getClientOriginalExtension();
            $fileRelativePath = $this->genRelativePath($extension);
            $zipContent       = file_get_contents($file);

            /* 图片进行压缩, 其他不进行处理
             * ---------------------------------------- */
            if (in_array($extension, UploaderTypes::kvExt(UploaderTypes::TYPE_IMAGES), true)) {
                if (!$extension) {
                    $extension = 'png';
                }
                // bmp 处理
                $type = mime_content_type($file->getRealPath());
                if ($type === 'image/x-ms-bmp') {
                    $img = imagecreatefrombmp($file->getRealPath());
                    if ($img) {
                        ob_start();
                        imagepng($img);
                        $imgContent = ob_get_clean();
                        $zipContent = $imgContent;
                    }
                }
                $zipContent = $this->resizeContent($extension, $zipContent);
            }

            $Disk->put($fileRelativePath, $zipContent);

            $this->destination = $fileRelativePath;

            return true;
        }

        return $this->setError($file->getErrorMessage());
    }

    /**
     * @inheritDoc
     */
    public function resize($content, $width = 1920, $height = 1440, $crop = false): StreamInterface
    {
        if ($content instanceof Image) {
            $Image = $content;
        }
        else {
            $Image = \Image::make($content);
        }

        if ($crop) {
            $widthCalc  = $Image->width() > $width ? $width : $Image->width();
            $heightCalc = $Image->height() > $height ? $height : $Image->height();
            $widthMax   = $Image->width() < $width ? $width : $Image->width();
            $heightMax  = $Image->height() < $height ? $height : $Image->height();

            // calc x, calc y
            $x = 0;
            $y = 0;
            if ($widthCalc >= $width) {
                $x = ceil(($widthMax - $widthCalc) / 2);
            }
            if ($heightCalc >= $height) {
                $y = ceil(($heightMax - $heightCalc) / 2);
            }
            if ($x || $y) {
                $Image->crop($width, $height, $x, $y);
            }
        }

        $Image->resize($width, $height, function (Constraint $constraint) {
            $constraint->aspectRatio();
            $constraint->upsize();
        });

        return $Image->stream('jpg', $this->quality);
    }

    /**
     * @inheritDoc
     */
    public function saveInput($content): bool
    {
        $extension = 'png';
        if (Str::contains($this->mimeType, '/')) {
            $extension = Str::after($this->mimeType, '/');
        }

        // 磁盘对象
        $Disk             = $this->storage();
        $fileRelativePath = $this->genRelativePath($extension);

        if (UtilHelper::isUrl($content)) {
            $extension = FileHelper::ext($content);
            if (!$extension) {
                $extension = 'png';
            }
            $content = \Image::make($content)->stream();
        }

        // 缩放图片
        if ($extension !== 'gif') {
            $zipContent = $this->resizeContent($extension, $content);
        }
        else {
            $zipContent = $content;
        }

        $Disk->put($fileRelativePath, $zipContent);
        $this->destination = $fileRelativePath;

        return true;
    }

    /**
     * 获取目标路径
     * @return string
     */
    public function getDestination(): string
    {
        return $this->destination;
    }

    /**
     * @param string $destination 设置目标地址
     */
    public function setDestination(string $destination)
    {
        $this->destination = $destination;
    }

    /**
     * 图片url的地址
     * @return string
     */
    public function getUrl(): string
    {
        // 磁盘 public_uploads 对应的是根目录下的 uploads, 所以这里的目录是指定的
        return $this->returnUrl . $this->destination;
    }

    /**
     * @inheritDoc
     * @deprecated 3.1
     * @removed    4.0
     */
    public static function type(string $type, $return_type = 'ext_string')
    {
        if (!isset(self::$extensions[$type])) {
            $ext = self::$extensions['images'];
        }
        else {
            $ext = self::$extensions[$type];
        }
        switch ($return_type) {
            case 'desc':
                return $ext['description'];
            case 'ext_array':
                return explode(',', $ext['extension']);
            case 'ext_string':
            default:
                return $ext['extension'];
        }
    }

    /**
     * 获取本地磁盘存储
     */
    public function storage(): FilesystemAdapter
    {
        return Storage::disk($this->disk);
    }

    /**
     * @param string $extension 扩展名
     * @return string
     */
    private function genRelativePath(string $extension = 'png'): string
    {
        $now      = Carbon::now();
        $fileName = $now->format('is') . Str::random(8) . '.' . $extension;

        return ($this->folder ? $this->folder . '/' : '') . $now->format('Ym/d/H/') . $fileName;
    }

    /**
     * 重设内容
     * @param string $extension  扩展
     * @param mixed  $img_stream 压缩内容
     * @return bool|StreamInterface
     */
    private function resizeContent(string $extension, $img_stream)
    {
        // 缩放图片
        if ($extension !== 'gif' && in_array($extension, UploaderTypes::kvExt(UploaderTypes::TYPE_IMAGES), true)) {
            $Image  = \Image::make($img_stream);
            $width  = $Image->width();
            $height = $Image->height();
            try {
                if ($width >= $this->resizeDistrict || $height >= $this->resizeDistrict) {
                    $r_width  = ($width > $height) ? $this->resizeDistrict : null;
                    $r_height = ($width > $height) ? null : $this->resizeDistrict;
                    return $this->resize($Image, $r_width, $r_height);
                }
            } catch (Exception $e) {
                return $this->setError($e->getMessage());
            }
        }
        else {
            return $img_stream;
        }

        return $img_stream;
    }

}