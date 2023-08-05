{{-- 
    This is the table builder component, it creates a table using the datatables plugin
    To use it you must have the datatables plugin in your project included either in a global file or in the file where this
    component is used, it must always be included before using this component

    Params:
       - customContainerClass (optional) 
       - tableID (required)
       - tableClass (optional) [If this param is not set it will add "table table-striped" by default]
       - cols => [
            [
                label (required)
                class (optional)
            ]
       ] (required)
       - ordering (optional)
       -method
       -url
       -ajax_cols
       -data (optional)
--}}


<div class="table-container {{ isset($customContainerClass) ? $customContainerClass : '' }}">
    <table id="{{ $tableID }}" class="{{ isset($tableClass) ? $tableClass : 'table table-striped w-100' }}">
        <thead>
            @foreach ($cols as $col)
                <th {{ isset($col['class']) ? 'class=' . $col['class'] : '' }}>{{ $col['label'] }}</th>
            @endforeach
        </thead>
        <tbody></tbody>
    </table>
</div>

@if (isset($ordering))
    <input type="hidden" id="ordering" value="{{ $ordering }}">
@endif
@if (isset($paginate))
    <input type="hidden" id="paginate" value="{{ json_encode($paginate) }}">
@endif

<input type="hidden" id="method" value="{{$method}}">
<input type="hidden" id="ajax_url" value="{{$url}}">
<input type="hidden" id="ajax_cols" value="{{json_encode($ajax_cols)}}">
@if (isset($data))
    <input type="hidden" id="ajax_data" value="{{json_encode($data)}}">
@endif
@if (isset($actions))
    <input type="hidden" id="actions" value="{{json_encode($actions)}}">
@endif

<script>
    var table_obj = {};
    if ($("#ordering").val() != undefined) {
        table_obj["ordering"] = $("#ordering").val()
    }
    if ($("#paginate").val() != undefined) {
        const paginate_values = JSON.parse($("#paginate").val());
        table_obj["language"] = {
            "paginate": paginate_values
        };
    }

    var ajax_obj = {
        ajax: {
            method: $("#method").val(),
            url: $("#ajax_url").val(),
            data: {
                "_token": "{{csrf_token()}}",
            },
            dataSrc: ''
        }
    }

    if($("#ajax_data").val() != undefined){
        ajax_obj['ajax']['data'] = Object.assign(ajax_obj['ajax']['data'], JSON.parse($("#ajax_data").val()));
    }

    ajax_obj['columns'] = JSON.parse($("#ajax_cols").val());

    if($("#actions").val() != undefined){
        const actions = JSON.parse($("#actions").val());
        var actions_obj = {
            "data": null,
            "width": actions['width'],
            "class": actions['class']!=undefined?actions['class']:'',
            "render": function(data, type, row, meta){
                var acts = actions['actions'];
                $.each(actions['replace'], (key, data)=>{
                    acts = acts.split(key).join(row[data]);
                })
                return acts;
            }
        }
        ajax_obj['columns'][ajax_obj['columns'].length] = actions_obj
    }    

    let oTable = $("#{{ $tableID }}").DataTable(Object.assign(table_obj, ajax_obj))
</script>
