<?php

namespace Poppy\System\Http\Forms\Settings;

use Poppy\Framework\Exceptions\ApplicationException;
use Poppy\Framework\Validation\Rule;

class FormSettingUpload extends FormSettingBase
{

    protected $title = '上传配置';

    public $inbox = true;

    protected $withContent = true;

    protected $group = 'py-system::picture';

    /**
     * Build a form here.
     * @throws ApplicationException
     */
    public function form()
    {
        $uploadTypes = sys_hook('poppy.system.upload_type');
        $types       = [];
        foreach ($uploadTypes as $key => $desc) {
            $types[$key] = $desc['title'];
        }
        $this->radio('save_type', '存储位置')->options($types)->rules([
            Rule::string(),
            Rule::required(),
        ])->default('default')->help('选择本地则文件存储在本地');

        foreach ($uploadTypes as $key => $desc) {
            if (isset($desc['setting'])) {
                $url  = route($desc['route']);
                $link = <<<Link
<a class="J_iframe" href="$url"><i class="fa fa-cogs"></i> {$desc['title']}设置</a>
Link;

                $this->html($link, $desc['title']);
            }
        }
    }
}
