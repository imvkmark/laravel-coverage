<?php

namespace Poppy\System\Http\Forms\Backend;

use Poppy\Framework\Classes\Resp;
use Poppy\Framework\Exceptions\ApplicationException;
use Poppy\Framework\Validation\Rule;
use Poppy\System\Action\Ban;
use Poppy\System\Classes\Traits\PamTrait;
use Poppy\System\Classes\Widgets\FormWidget;
use Poppy\System\Models\PamBan;

class FormBanEstablish extends FormWidget
{

    use PamTrait;

    public $ajax = true;


    private $id;

    /**
     * @var PamBan
     */
    private $item;

    /**
     * 设置id
     * @param $id
     * @return $this
     * @throws ApplicationException
     */
    public function setId($id)
    {
        $this->id = $id;
        if ($id) {
            $this->item = PamBan::find($id);

            if (!$this->item) {
                throw new ApplicationException('无设备信息');
            }
        }
        return $this;
    }

    public function handle()
    {
        $Ban = new Ban();
        if (!$Ban->establish(input())) {
            return Resp::error($Ban->getError());
        }

        return Resp::success('操作成功', '_top_reload|1');
    }

    public function data(): array
    {
        if ($this->id) {
            return [
                'id'    => $this->item->id,
                'type'  => $this->item->type,
                'value' => $this->item->value,
            ];
        }
        return [];
    }

    public function form()
    {
        if ($this->id) {
            $this->hidden('id', '设备id');
        }

        $this->select('type', '类型')->options(PamBan::kvType());
        $this->text('value', '限制值')->rules([
            Rule::nullable(),
        ]);
    }
}
