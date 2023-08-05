@include('layouts.includes')
@extends('layouts.loader')

<title>Items</title>
<link rel="stylesheet" href="{{ mix('resources/css/items.css') }}">

@section('content')

    <!-- Create New Modal -->
    @component('components.modal_builder', [
        'modal_id' => 'addItem',
        'hasHeader' => true,
        'rawHeader' =>
            '<h5 class="modal-title" id="addModalLabel"><i class="fa-solid fa-circle-plus text-icon"></i> Adicionar Novo Item</h5>',
        'hasBody' => true,
        'inputs' => [
            ['label' => 'Nome:', 'type' => 'text', 'id' => 'name', 'placeholder' => 'Nome do item'],
            ['label' => 'Preço:', 'type' => 'number', 'id' => 'price', 'placeholder' => 'Preço €'],
            [
                'label' => 'Custo:',
                'type' => 'number',
                'id' => 'cost',
                'placeholder' => 'Custo de produção €',
                'optional' => true,
            ],
            [
                'label' => 'Imagem:',
                'type' => 'file',
                'id' => 'image',
                'placeholder' => 'https://imageurl.jpg',
                'restrictFile' => true,
            ],
        ],
        'select' => [
            'configs' => [
                'id' => 'category',
                'label' => 'Categoria:',
                'default' => 'Selecionar Categoria',
            ],
            'options' => $options,
        ],
        'hasFooter' => true,
        'buttons' => [
            ['label' => 'Guardar', 'id' => 'save', 'class' => 'btn btn-primary'],
            ['label' => 'Guardar e abrir', 'id' => 'save_enter', 'class' => 'btn btn-secondary'],
            ['label' => 'Cancelar', 'id' => 'closeMdl', 'class' => 'btn btn-danger', 'dismiss' => true],
        ],
    ])
    @endcomponent

    <div class="container mt-5">
        <div class="d-flex justify-content-between">
            {{-- Breadcrumbs --}}
            @component('components.breadcrumbs', [
                'title' => 'Items',
                'crumbs' => [['link' => '/', 'label' => 'Sessões'], ['link' => '/items', 'label' => 'Items']],
                'padding' => false
            ])
            @endcomponent

            @component('components.admin_menu', [
                'active' => 'items',
            ])
            @endcomponent
        </div>
        <hr>
        <div class="d-flex flex-row">
            <div class="pb-4 d-flex justify-content-start w-50">
                <button class="btn btn-primary w-25" data-bs-toggle="modal" data-bs-target="#addItem">Novo Item</button>
            </div>
            <div class="pb-4 d-flex justify-content-end w-50">
                <input type="text" class="form-control" style="width: 40%" id="search_table" placeholder="Procurar">
            </div>
        </div>

        {{-- Table --}}
        @component('components.table_builder', [
            'tableID' => 'items',
            'tableClass' => 'table w-100',
            'cols' => [['label' => '#'], ['label' => 'Nome'], ['label' => 'Preço'], ['label' => '']],
            'ordering' => false,
            'paginate' => [
                'next' => "<i class=\"fa-solid fa-caret-right paginate_change_btns\"></i>",
                'previous' => "<i class=\"fa-solid fa-caret-left paginate_change_btns\"></i>",
            ],
            'method' => 'post',
            'url' => '/displayitems',
            'ajax_cols' => [
                ['data' => 'id', 'width' => '15%', 'class' => 'h-start'],
                ['data' => 'name', 'width' => '35%'],
                ['data' => 'price', 'width' => '25%'],
            ],
            'actions' => [
                'width' => '25%',
                'class' => 'h-end',
                'actions' =>
                    '<i class="fa-solid fa-pencil dt-action primary me-2" onclick="editRedirect(ROWID)"></i><i onclick="removeItem(ROWID)" class="fa-solid fa-trash-can-xmark dt-action primary"></i>',
                'replace' => [
                    'ROWID' => 'id',
                ],
            ],
        ])
        @endcomponent
    </div>

    <script>
        var notyf = new Notyf();

        // Clears addItems modal
        function clearModal() {
            $("#name").val("");
            $("#price").val("");
            $("#cost").val("");
            $("#category").val("");
            $("#image").val("");
        }

        // Ajax to save items
        function saveItem(enter = false) {
            if (hasEmpty(["name", "price", "image", "category"])) {
                return;
            }

            $.ajax({
                method: "POST",
                url: "/saveitems",
                data: {
                    "_token": "{{ csrf_token() }}",
                    "name": $("#name").val(),
                    "price": $("#price").val(),
                    "cost": $("#cost").val(),
                    "category": $("#category").val(),
                    "img": imgFile.dataURL,
                }
            }).done((res) => {
                if (enter) window.location.href = "/items/" + res.id;
                notyf.success(res.message);
                $("#items").DataTable().ajax.reload(null, false);
                $("#addItem").modal("toggle");
                clearModal();
            }).fail((err) => {
                notyf.error(err.responseJSON.message);
            })
        }

        // Simple redirect
        function editRedirect(id) {
            window.location.href = "items/" + id;
        }

        // Delete items
        function removeItem(id) {
            Swal.fire({
                icon: "warning",
                title: 'Quer remover este item?',
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
                        url: "/deleteitems",
                        data: {
                            "_token": "{{ csrf_token() }}",
                            "id": id
                        }
                    }).done((res) => {
                        notyf.success(res.message);
                        $("#items").DataTable().ajax.reload(null, false);
                    }).fail((err) => {
                        notyf.error(err.responseJSON.message);
                    })
                }
            })
        }

        $(document).ready(() => {
            // Get base64 of added images
            imgFile = null;
            $('#image').on('change', function() {
                var file = this.files[0];
                var reader = new FileReader();
                reader.readAsDataURL(file);
                reader.onload = function() {
                    var base64 = reader.result;
                    imgFile = {
                        "dataURL": base64,
                        "type": file.type
                    };
                };
            });

            $('#search_table').keyup(function() {
                oTable.search($(this).val()).draw();
            })

            $("#save").on('click', () => {
                saveItem();
            })

            $("#save_enter").on('click', () => {
                saveItem(true);
            })

            $("#addItem").on("hidden.bs.modal", () => {
                clearModal();
            })
        })
    </script>
@stop
