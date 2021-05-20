<div class="{{$viewClass['form-group']}} {!! (isset($errors) && !$errors->has($errorKey)) ? '' : 'has-error' !!}">

	<div class="{{$viewClass['label']}}">
		<label for="{{$id}}" class="layui-form-auto-label {{$viewClass['label_element']}}">
			@include('py-system::tpl.form.help-tip')
			{{$label}}
		</label>
	</div>

	<div class="{{$viewClass['field']}}">
		<div class="layui-form-auto-field">
			{!! app('form')->textarea($name, old($column, $value), $attributes + [
				'class' => 'layui-textarea '. $class,
				'rows' => $rows,
				'placeholder' => $placeholder,
			]) !!}
		</div>
		@include('py-system::tpl.form.help-block')
		@include('py-system::tpl.form.error')
	</div>
</div>
