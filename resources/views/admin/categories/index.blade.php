@include('layouts.includes')
@extends('layouts.loader')

<title>Categorias</title>
<link rel="stylesheet" href="{{ mix('resources/css/categories.css') }}">

@section('content')

    <!-- Create New Modal -->
    @component('components.modal_builder', [
        'modal_id' => 'addCategory',
        'hasHeader' => true,
        'rawHeader' =>
            '<h5 class="modal-title" id="addModalLabel"><i class="fa-solid fa-circle-plus text-icon"></i> Adicionar Nova Categoria</h5>',
        'hasBody' => true,
        'inputs' => [
            ['label' => 'Nome:', 'type' => 'text', 'id' => 'name', 'placeholder' => 'Nome da Categoria'],
            ['label' => 'Cor:', 'type' => 'color', 'id' => 'color'],
        ],
        'hasFooter' => true,
        'buttons' => [
            ['label' => 'Guardar', 'id' => 'save', 'class' => 'btn btn-primary'],
            ['label' => 'Cancelar', 'id' => 'closeMdl', 'class' => 'btn btn-danger', 'dismiss' => true],
        ],
    ])
    @endcomponent

    <div class="container mt-5">
        <div class="d-flex justify-content-between">
            {{-- Breadcrumbs --}}
            @component('components.breadcrumbs', [
                'title' => 'Categorias',
                'crumbs' => [['link' => '/', 'label' => 'Sessões'], ['link' => '/categorias', 'label' => 'Categorias']],
                'padding' => false,
            ])
            @endcomponent

            @component('components.admin_menu', [
                'active' => 'categories',
            ])
            @endcomponent
        </div>
        <hr>
    </div>

    <div class="container">
        <button class="btn btn-primary form-control w-25 mb-4" data-bs-toggle="modal" data-bs-target="#addCategory">Adicionar
            Categoria</button>
        {{-- Table --}}
        @component('components.table_builder', [
            'tableID' => 'categories',
            'tableClass' => 'table w-100',
            'cols' => [['label' => '#'], ['label' => 'Nome'], ['label' => '']],
            'ordering' => false,
            'paginate' => [
                'next' => "<i class=\"fa-solid fa-caret-right paginate_change_btns\"></i>",
                'previous' => "<i class=\"fa-solid fa-caret-left paginate_change_btns\"></i>",
            ],
            'method' => 'post',
            'url' => '/getcategories',
            'ajax_cols' => [['data' => 'id', 'width' => '15%', 'class' => 'h-start'], ['data' => 'label', 'width' => '35%']],
            'actions' => [
                'width' => '25%',
                'class' => 'h-end',
                'actions' => '<i onclick="deleteCategory(ROWID)" class="fa-solid fa-trash-can-xmark dt-action primary"></i>',
                'replace' => [
                    'ROWID' => 'id',
                ],
            ],
        ])
        @endcomponent
    </div>

    <script>
        var notyf = new Notyf();

        $("#addCategory").on("hidden.bs.modal", () => {
            $("#name").val("");
        })

        $("#save").on('click', () => {
            if (hasEmpty(["name"])) return;            

            $.ajax({
                method: "post",
                url: "/savecategories",
                data: {
                    "_token": "{{ csrf_token() }}",
                    "name": $("#name").val(),
                    "color": $("#color").val(),
                }
            }).done((res) => {
                notyf.success(res.message);
                $("#categories").DataTable().ajax.reload(null, false);
                $("#addCategory").modal("toggle");
            })
        })

        // Deletes categories
        function deleteCategory(id) {
            Swal.fire({
                icon: "warning",
                title: 'Quer remover esta categoria?',
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
                        url: "/deletecategory",
                        data: {
                            "_token": "{{ csrf_token() }}",
                            "id": id
                        }
                    }).done((res) => {
                        notyf.success(res.message);
                        $("#categories").DataTable().ajax.reload(null, false);
                    }).fail((err) => {
                        Swal.fire({
                            icon: "error",
                            title: 'Oops!',
                            text: "Parece que ocorreu um erro a remover a categoria, verifique se existem items nesta categoria e tente novamente",
                            confirmButtonText: 'OK',
                            confirmButtonColor: '#1d2c3f',
                        })
                    })
                }
            })
        }
    </script>
@stop
