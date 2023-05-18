<div {{$attributes->merge(['class' => 'mb-3'])}}>
    <label for="{{$name}}" class="form-label">{{$label}}</label>
    <select id="{{$name}}" name="{{$name}}" class="form-select" aria-label="Default select example"
     {{empty($required) ? '' : 'required'}}
    >
        <option value="" selected disabled>Selecione uma opção</option>
        {{$slot}}
    </select>
</div>