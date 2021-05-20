<div class="{{$viewClass['form-group']}} {!! (isset($errors) && !$errors->has($errorKey)) ?: 'has-error' !!}">
    <div class="{{$viewClass['label']}}">
        <label for="{{$id}}" class=" layui-form-checkbox-label layui-form-auto-label {{$viewClass['label_element']}}">
            @include('py-system::tpl.form.help-tip')
            @if($canCheckAll)
                {!! app('form')->checkbox('_check_all_'. $name,	1,	false, [
                    'lay-skin'=> 'primary',
                    'lay-filter' => '_check_all_'.$name
                ]) !!}
                <script>
                // 实现 全选 反选
                layui.form.on('checkbox(_check_all_{!! $name !!})', function(data) {
                    $("input:checkbox[name='{!! $name !!}[]']").prop("checked", this.checked);
                });
                </script>
            @endif
            {{$label}}
        </label>
    </div>

    <div class="{{$viewClass['field']}}" id="{{$id}}">
        <div class="layui-form-auto-field {!! !$inline ? 'layui-field-checkbox-stack' : '' !!}">
            @foreach($options as $option => $label)
                <div class="layui-field-checkbox-item">
                    {!! app('form')->checkbox(
                    $name.'[]',
                    $option,
                    ($option == old($column, $value)) || (in_array($option, $checked)),
                    array_merge($attributes , [
                        'class' => 'layui-field-checkbox',
                        'id' => $column.'-'.$option,
                        'lay-ignore',
                    ])) !!}
                    {!! app('form')->label($column.'-'.$option, $label, [
                        'class' => 'layui-field-checkbox-label'
                    ]) !!}
                </div>
            @endforeach
        </div>
        @include('py-system::tpl.form.help-block')
        @include('py-system::tpl.form.error')
    </div>
</div>
