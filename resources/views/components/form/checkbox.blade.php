<div class="m-1">
    <input type="checkbox" {{empty($checked) ? '' : 'checked'}} class="btn-check" name="{{$name}}" id="{{$id}}" 
    value="{{$value}}" autocomplete="off" >
    <label class="btn" for="{{$id}}">{{$label}}</label>
</div>