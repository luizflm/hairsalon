<div class="mb-3 {{empty($col)? '' : 'col'}}">
    <label for="{{$name}}" class="form-label">{{$label}}</label>
    <input type="{{$type}}" class="form-control" id="{{$name}}" name="{{$name}}" 
    placeholder="{{$placeholder ?? ''}}" {{empty($required) ? '' : 'required'}} 
    step="{{$step ?? ''}}" value="{{$value ?? ''}}">
</div>