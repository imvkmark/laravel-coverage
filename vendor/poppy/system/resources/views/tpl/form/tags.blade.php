{!! app('html')->script('assets/libs/jquery/tokenize2/jquery.tokenize2.js') !!}
{!! app('html')->style('assets/libs/jquery/tokenize2/tokenize2.css') !!}
<div class="{{$viewClass['form-group']}} {!! (isset($errors) && !$errors->has($errorKey)) ? '' : 'has-error' !!}">
	<div class="{{$viewClass['label']}}">
		<label for="{{$id}}" class="layui-form-auto-label {{$viewClass['label_element']}}">
			@include('py-system::tpl.form.help-tip')
			{{$label}}
		</label>
	</div>

	<div class="{{$viewClass['field']}} ">
		<div class="layui-form-auto-field">
			<div class="layui-field-tag-tokenize">
				{!! app('form')->select($name.'[]', $options, $value, $attributes + [
					'multiple',
					'id' => $id,
					'lay-ignore' => 'lay-ignore',
					'class' => 'tokenize',
				]) !!}
				<script>
				$(function() {
					let ${!! $id !!} = $('#{!! $id !!}');
					${!! $id !!}.tokenize2({
						placeholder : '{!! $placeholder !!}',
						tokensMaxItems : 0
					})
					${!! $id !!}.on("tokenize:select", function() {
						$('#{!! $id !!}').trigger('tokenize:search', "");
					});
				})
				</script>
			</div>
		</div>
		@include('py-system::tpl.form.help-block')
		@include('py-system::tpl.form.error')
	</div>
</div>
