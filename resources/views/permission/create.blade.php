{{Form::open(array('url'=>'permissions','method'=>'post'))}}
    <div class="form-group">
        {{Form::label('name',__('Name'),array('class'=>'form-label'))}}
        {{Form::text('name',null,array('class'=>'form-control','placeholder'=>__('Enter Permission Name')))}}
        @error('name')
        <span class="invalid-name" role="alert">
                    <strong class="text-danger">{{ $message }}</strong>
                </span>
        @enderror
    </div>
    <div class="form-group">
        {{Form::label('name',__('Module'),array('class'=>'form-label'))}}
        <select class="form-control" data-trigger name="module" id="choices-single-default" placeholder="This is a search placeholder">
            @foreach ($modules as $module)
                <option value="{{ $module }}">{{ $module }}</option>
            @endforeach
        </select>
        @error('name')
        <span class="invalid-name" role="alert">
                    <strong class="text-danger">{{ $message }}</strong>
                </span>
        @enderror
    </div>

    <div class="modal-footer">
        <button type="button" class="btn btn-rc-outline" data-bs-dismiss="modal">{{__('Cancel')}}</button>
        <button type="submit" class="btn btn-rc-primary">{{__('Create')}}</button>
    </div>
{{Form::close()}}
<script>
 var multipleCancelButton = new Choices(
        '#choices-single-defaultlq', {
            removeItemButton: true,
        }
        );

</script>
