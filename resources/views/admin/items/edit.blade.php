@include('layouts.includes')
@extends('layouts.loader')

<title>Items</title>
<link rel="stylesheet" href="{{ mix('resources/css/items.css') }}">

@section('content')

    <!-- Create New Mods Modal -->
    @component('components.modal_builder', [
        'modal_id' => 'addMod',
        'hasHeader' => true,
        'rawHeader' =>
            '<h5 class="modal-title" id="addModalLabel"><i class="fa-solid fa-layer-plus text-icon"></i> Adicionar Novo Modificador</h5>',
        'hasBody' => true,
        'inputs' => [
            ['label' => 'Nome:', 'type' => 'text', 'id' => 'nameMod', 'placeholder' => 'Nome do item'],
            ['label' => 'Preço:', 'type' => 'number', 'id' => 'priceMod', 'placeholder' => 'Preço €'],
            [
                'label' => 'Custo:',
                'type' => 'number',
                'id' => 'costMod',
                'placeholder' => 'Custo de produção €',
                'optional' => true,
            ],
            [
                'label' => 'Imagem:',
                'type' => 'file',
                'id' => 'imageMod',
                'placeholder' => 'https://imageurl.jpg',
                'restrictFile' => true,
            ],
        ],
        'hasFooter' => true,
        'buttons' => [
            ['label' => 'Guardar', 'id' => 'save_modifier', 'class' => 'btn btn-primary'],
            ['label' => 'Cancelar', 'id' => 'closeMdl', 'class' => 'btn btn-danger', 'dismiss' => true],
        ],
    ])
    @endcomponent

    <style>
        .img-item {
            border-radius: 7px;
            background: url({{ $item['img'] }});
            background-size: cover;
            background-position: center;
            box-shadow: -9px 11px 0px 0px rgba(0, 0, 0, 0.25);
            width: 25%;
            height: 576px;
        }
    </style>

    <input type="hidden" id="editing" value="0">
    <input type="hidden" id="editingId">

    <div class="container mt-5">
        {{-- Breadcrumbs --}}
        @component('components.breadcrumbs', [
            'title' => $item['name'],
            'crumbs' => [
                ['link' => '/', 'label' => 'Sessões'],
                ['link' => '/items', 'label' => 'Items'],
                ['link' => '/items/' . $item['id'], 'label' => $item['name']],
            ],
            'separator' => true,
        ])
        @endcomponent

        <div class="d-flex justify-content-center mb-3">
            <button id="v-gen" class="btn btn-primary form-control w-25 me-2">Geral</button>
            <button id="v-mod" class="btn btn-secondary form-control w-25">Modificadores</button>
        </div>

        <div id="general" class="d-flex justify-content-center">
            <div class="img-item"></div>
            <div class="edit-form-container">
                <h3>Nome:</h3>
                <input type="text" id="name" class="form-control w-100" placeholder="Nome do item"
                    value="{{ $item['name'] }}">
                <h3 class="mt-3">Preço:</h3>
                <input type="number" step="0.01" id="price" class="form-control w-100" placeholder="Preço do item"
                    value="{{ $item['price'] }}">
                <h3 class="mt-3">Custo:</h3>
                <input type="number" step="0.01" id="cost" class="form-control w-100" placeholder="Custo do item"
                    value="{{ $item['cost'] }}">
                <h3 class="mt-3">Imagem:</h3>
                <input type="file" accept="image/*" id="img" class="form-control w-100"
                    value="{{ $item['img'] }}">
                <h3 class="mt-3">Categoria:</h3>
                <select id="category" class="form-select w-100">
                    <option value="">Selecionar Categoria</option>
                    @foreach ($categories as $category)
                        <option value="{{ $category['value'] }}"
                            {{ $item['category_id'] == $category['value'] ? 'selected' : '' }}>
                            {{ $category['label'] }}</option>
                    @endforeach
                </select>
                <hr>
                <div class="d-flex flex-row">
                    <button class="btn btn-primary w-50 me-1" id="save-edit">Guardar</button>
                    <button class="btn btn-danger w-50 ms-1" id="cancel-edit">Voltar</button>
                </div>
            </div>
        </div>
        <div id="modifiers" class="visually-hidden">
            <div class="d-flex justify-content-center">
                <button class="btn btn-primary" id="add_modifier" data-bs-toggle="modal" data-bs-target="#addMod">Criar
                    Modificador</button>
            </div>
            <div class="mt-5">
                {{-- Table --}}
                @component('components.table_builder', [
                    'tableID' => 'modifiers_table',
                    'tableClass' => 'table w-100',
                    'cols' => [['label' => '#'], ['label' => 'Nome'], ['label' => 'Preço'], ['label' => '']],
                    'ordering' => false,
                    'paginate' => [
                        'next' => "<i class=\"fa-solid fa-caret-right paginate_change_btns\"></i>",
                        'previous' => "<i class=\"fa-solid fa-caret-left paginate_change_btns\"></i>",
                    ],
                    'method' => 'post',
                    'url' => '/getmods',
                    'data' => [
                        '_token' => csrf_token(),
                        'id' => $item['id'],
                    ],
                    'ajax_cols' => [
                        ['data' => 'id', 'width' => '15%', 'class' => 'h-start'],
                        ['data' => 'name', 'width' => '35%'],
                        ['data' => 'price', 'width' => '25%'],
                    ],
                    'actions' => [
                        'width' => '25%',
                        'class' => 'h-end',
                        'actions' => '<i onclick="toggleEdit(ROWID, \'NAME\', PRICE, COST)" class="fa-solid fa-pencil dt-action primary me-2"></i>
                                      <i onclick="removeItem(ROWID)" class="fa-solid fa-trash-can-xmark dt-action primary"></i>',
                        'replace' => [
                            'ROWID' => 'id',
                            'NAME' => 'name',
                            'PRICE' => 'price',
                            'COST' => 'cost',
                        ],
                    ],
                ])
                @endcomponent
            </div>
        </div>
    </div>
    <script>
        var notyf = new Notyf();

        // * ITEM * //
        imgFile = null;
        $('#img').on('change', function() {
            var file = this.files[0];
            var reader = new FileReader();
            reader.readAsDataURL(file);
            reader.onload = function() {
                var base64 = reader.result;
                imgFile = {
                    "dataURL": base64,
                    "type": file.type
                };
                $(".img-item").css("background", "url(" + base64 + ")");
                $(".img-item").css("background-size", "cover");
                $(".img-item").css("background-position", "center");
            };
        });
        $("#cancel-edit").on('click', () => {
            swal.fire({
                icon: "warning",
                title: "Quer Voltar?",
                text: "Se não guardou, a informação inserida sera perdida",
                showCancelButton: true,
                confirmButtonText: 'Confirmar',
                confirmButtonColor: '#d33',
                denyButtonText: `Cancelar`,
                iconColor: '#d33'
            }).then((res) => {
                if (res.isConfirmed) {
                    window.location.href = "/items"
                }
            })
        })
        $("#save-edit").on('click', () => {
            if (hasEmpty(["name", "price", "category"])) return;

            $.ajax({
                method: "post",
                url: "/updateitems",
                data: {
                    "_token": "{{ csrf_token() }}",
                    "id": {{ $item['id'] }},
                    "name": $("#name").val(),
                    "price": $("#price").val(),
                    "cost": $("#cost").val(),
                    "category": $("#category").val(),
                    "img": imgFile ? imgFile.dataURL : null,
                }
            }).done((res) => {
                notyf.success(res.message);
            }).fail((err) => {
                notyf.error(err.responseJSON.message);
            })
        })

        $("#v-gen").on('click', () => {
            changeView("modifiers", "general");
        })
        $("#v-mod").on('click', () => {
            changeView("general", "modifiers");
        })

        function changeView(id, remove) {
            if (!$("#" + id).hasClass("visually-hidden")) {
                $("#" + id).addClass("visually-hidden");
                $("#" + remove).removeClass("visually-hidden");
            }
        }

        // * MODIFIERS * //
        // Clears addMod modal
        function clearModal() {
            $("#nameMod").val("");
            $("#priceMod").val("");
            $("#costMod").val("");
            $("#imageMod").val("");
        }
        $("#addMod").on("hidden.bs.modal", () => {
            clearModal();
        })
        imgFileMod = null;
        $('#imageMod').on('change', function() {
            var file = this.files[0];
            var reader = new FileReader();
            reader.readAsDataURL(file);
            reader.onload = function() {
                var base64 = reader.result;
                imgFileMod = {
                    "dataURL": base64,
                    "type": file.type
                };
            };
        });

        function saveMod(id = null) {
            if (hasEmpty(["nameMod", "priceMod", "imageMod"])) {
                return;
            }

            $.ajax({
                method: "POST",
                url: !id ? "/savemod" : "/editmod",
                data: {
                    "_token": "{{ csrf_token() }}",
                    "name": $("#nameMod").val(),
                    "price": $("#priceMod").val(),
                    "cost": $("#costMod").val(),
                    "img": imgFileMod.dataURL,
                    "item_id": {{ $item['id'] }},
                    "id": id
                }
            }).done((res) => {
                notyf.success(res.message);
                $("#modifiers_table").DataTable().ajax.reload(null, false);
                $("#addMod").modal("toggle");
                if (id != null) {
                    $("#editing").val(1);
                    $("#editingId").val(null);
                }
                clearModal();
            }).fail((err) => {
                notyf.error(err.responseJSON.message);
            })
        }

        function toggleEdit(id, name, price, cost) {
            $("#editingId").val(id);
            $("#editing").val(1);
            $("#nameMod").val(name);
            $("#priceMod").val(price);
            $("#costMod").val(cost);
            $("#addMod").modal("toggle");
        }

        $("#save_modifier").on('click', () => {
            if ($("#editing").val() == 0) {
                saveMod();
                return;
            }
            saveMod($("#editingId").val());
        });

        // Delete items
        function removeItem(id) {
            Swal.fire({
                icon: "warning",
                title: 'Quer remover este modificador?',
                text: "Isto não pode ser revertido",
                showCancelButton: true,
                confirmButtonText: 'Confirmar',
                confirmButtonColor: '#d33',
                denyButtonText: `Cancelar`,
                iconColor: '#d33'
            }).then((result) => {
                if (result.isConfirmed) {
                    $.ajax({
                        method: "post",
                        url: "/removemod",
                        data: {
                            "_token": "{{ csrf_token() }}",
                            "id": id
                        }
                    }).done((res) => {
                        notyf.success(res.message);
                        $("#modifiers_table").DataTable().ajax.reload(null, false);
                    }).fail((err) => {
                        notyf.error(err.responseJSON.message);
                    })
                }
            })
        }
    </script>
@stop
