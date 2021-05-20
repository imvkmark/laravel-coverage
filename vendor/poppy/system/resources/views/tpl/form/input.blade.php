<div class="{{$viewClass['form-group']}} {!! (isset($errors) && !$errors->has($errorKey)) ? '' : 'has-error' !!}">

	<div class="{{$viewClass['label']}}">
		<label for="{{$id}}" class="layui-form-auto-label {{$viewClass['label_element']}}">
			@include('py-system::tpl.form.help-tip')
			{{$label}}
		</label>
	</div>

	<div class="{{$viewClass['field']}}">
		<div class="layui-form-auto-field">
			@if($type === 'text')
				{!! app('form')->text($name, $value, $attributes) !!}
			@endif
			@if($type === 'number')
				{!! app('form')->number($name, $value, $attributes) !!}
			@endif
			@if($type === 'password')
				{!! app('form')->password($name, $attributes) !!}
			@endif

		</div>
		@include('py-system::tpl.form.help-block')
		@include('py-system::tpl.form.error')
	</div>
</div>