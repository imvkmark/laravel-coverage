<div class="{{$viewClass['form-group']}} {!! (isset($errors) && !$errors->has($errorKey)) ? '' : 'has-error' !!}">

	<div class="{{$viewClass['label']}}">
		<label for="{{$id}}" class="layui-form-auto-label {{$viewClass['label_element']}}">
			@include('py-system::tpl.form.help-tip')
			{{$label}}
		</label>
	</div>

	<div class="{{$viewClass['field']}}">

		<input type="text" class="{{$class}}" name="{{$name}}" data-from="{{ old($column, $value) }}" {!! $attributes !!} />
		@include('py-system::tpl.form.help-block')
		@include('py-system::tpl.form.error')
	</div>
</div>
