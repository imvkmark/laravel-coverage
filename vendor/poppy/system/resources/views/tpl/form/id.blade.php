<div class="{{$viewClass['form-group']}}">
	<div class="{{$viewClass['label']}}">
		<label for="{{$id}}" class="layui-form-auto-label {{$viewClass['label_element']}}">
			@include('py-system::tpl.form.help-tip')
			{{$label}}
		</label>
	</div>

	<div class="{{$viewClass['field']}}">
		<div class="layui-form-auto-field">
			{!! app('form')->text($name, $value, [
				'readonly' => 'readonly',
				'class' => 'layui-input',
				'id' => $id,
			] + $attributes) !!}
		</div>
		@include('py-system::tpl.form.help-block')
	</div>
</div>