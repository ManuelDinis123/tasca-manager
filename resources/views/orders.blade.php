@include('layouts.includes')
@extends('layouts.loader')

@section('content')
    @component('components.modal_builder', [
        'modal_id' => 'chooseItem',
        'hasHeader' => true,
        'rawHeader' =>
            '<h5 class="modal-title" id="addModalLabel"><i class="fa-solid fa-circle-plus text-icon"></i> Adicionar Novo Item</h5>',
        'hasBody' => true,
        'inputs' => [
            ['type' => 'hidden', 'id' => 'itemId'],
            [
                'label' => 'Quantidade:',
                'type' => 'number',
                'id' => 'quantity',
                'placeholder' => 'Quantidade do item',
                'step' => '0.01',
            ],
        ],
        'select' => [
            'configs' => [
                'id' => 'modifiersSelect',
                'label' => 'Modificadores:',
                'default' => 'Selecionar Modificador',
            ],
            'options' => [],
        ],
        'hasFooter' => true,
        'buttons' => [
            ['label' => 'Confirmar', 'id' => 'save', 'class' => 'btn btn-primary'],
            ['label' => 'Cancelar', 'id' => 'closeMdl', 'class' => 'btn btn-danger', 'dismiss' => true],
        ],
    ])
    @endcomponent

    <title>Pedidos</title>
    <link rel="stylesheet" href="{{ mix('resources/css/orders.css') }}">

    <div class="loaderFADE visually-hidden">
        <div class="loader-container" id="lc">
            <div class="loader2"></div>
        </div>
    </div>

    <div class="container mt-5">
        <div class="d-flex flex-row">
            <div class="w-100">
                <div class="d-flex justify-content-between align-items-top">
                    <h1 style="font-weight: 800">{{ session()->get('sess.label') }}</h1>
                    <input id="search_items" type="text" class="form-control w-50" style="height: 50px"
                        placeholder="Procurar">
                    <div class="stats-btn">
                        <div class="d-flex justify-content-center">
                            <i class="fa-solid fa-chart-simple stats-icon"></i><br />
                        </div>
                        <span class="stats-lbl">Ver Estatísticas</span>
                    </div>
                </div>
                <hr>
                <div class="allItems">
                    <div class="items-container scroll-y scrollable w-100 d-flex flex-wrap">
                        @foreach ($items as $item)
                            <style>
                                .item-{{ $item['id'] }} {
                                    background: linear-gradient(180deg, rgba(0, 0, 0, 0.00) 46.88%, rgba(0, 0, 0, 0.78) 100%), url({{ $item['img'] }});
                                    background-size: cover;
                                    background-position: center;
                                }
                            </style>
                            <div class="item item-{{ $item['id'] }}">
                                <div class="item-content">
                                    <span class="item-text">{{ $item['name'] }}</span>
                                    <span class="item-text">{{ $item['price'] }}€</span>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
            <div class="ms-5">
                <div class="order-items">
                    <div class="d-flex justify-content-between align-items-center">
                        <h2 style="font-weight: 800">Pedidos</h2>
                        <i class="fa-sharp fa-regular fa-burger-soda edit-order mb-2"></i>
                    </div>
                    <hr style="color: white; opacity:1 !important;">
                    <div id="order_view" class="overview-container scroll-y scrollable" style="height: 458px !important">
                        @if (session()->get('items') !== null)
                            @foreach (session()->get('items') as $item)
                                <div class="d-flex justify-content-between overview-item mb-3">
                                    <div style="color: white">
                                        <span>{{ $item['quantity'] }} x </span><span>{{ $item['name'] }}</span>
                                    </div>
                                    <span
                                        style="color: white">{{ $item['quantity'] * $item['price'] + $item['modifier_price'] }}€</span>
                                </div>
                            @endforeach
                        @endif
                    </div>
                </div>
                <div class="mt-3">
                    <button class="btn btn-primary w-100 mb-3" id="confirm_order">Confirmar</button>
                    <button-btn class="btn btn-dark w-100 mb-5" id="resetbtn">Reset</button-btn>
                    <button class="btn btn-danger w-100" id="closeSession">Fechar Sessão</button>
                </div>
            </div>
        </div>

    </div>

    <script>
        var notyf = new Notyf();

        $("#chooseItem").on("hidden.bs.modal", () => {
            $(".removable-option").remove();
            $("#quantity").val("");
        })

        $(".item").on('click', function() {
            $(".loaderFADE").removeClass("visually-hidden");
            var itemId = this.className.replace("item item-", "");
            $("#itemId").val(itemId);
            $.ajax({
                method: "post",
                url: "/getmods",
                data: {
                    "_token": "{{ csrf_token() }}",
                    "id": itemId
                }
            }).done((res) => {
                $.each(res, (key, val) => {
                    $("#modifiersSelect").append(`
                        <option class="removable-option" value="${val.id}">${val.name}</option>
                    `);
                })
                $(".loaderFADE").addClass("visually-hidden");
                $("#chooseItem").modal("toggle");
            }).fail((err) => {
                console.log(err);
                $(".loaderFADE").addClass("visually-hidden");
            })
        })

        $("#save").on('click', () => {
            if (hasEmpty(["quantity"])) return;

            $.ajax({
                method: "post",
                url: "/addcart",
                data: {
                    "_token": "{{ csrf_token() }}",
                    "id": $("#itemId").val(),
                    "quantity": $("#quantity").val(),
                    "modifier": $("#modifiersSelect").val()
                }
            }).done((res) => {
                notyf.success("Adicionado ao pedido");
                $("#chooseItem").modal("toggle");
                $("#order_view").load(" #order_view > *");
            })
        })

        $("#resetbtn").on('click', () => {
            Swal.fire({
                icon: "warning",
                title: 'Quer dar reset?',
                text: "Isto não pode ser revertido",
                showCancelButton: true,
                confirmButtonText: 'Confirmar',
                confirmButtonColor: '#d33',
                denyButtonText: `Cancelar`,
                iconColor: '#d33'
            }).then((result) => {
                $.ajax({
                    method: "post",
                    url: "/reset",
                    data: {
                        "_token": "{{ csrf_token() }}"
                    }
                }).done((res) => {
                    $("#order_view").load(" #order_view > *");
                })
            })
        })


        $("#closeSession").on('click', () => {
            $.ajax({
                method: "post",
                url: "/closesess",
                data: {
                    "_token": "{{ csrf_token() }}",
                }
            }).done((res) => {
                notyf.success(res.message);
                $(".loaderFADE").removeClass("visually-hidden");
                setTimeout(() => {
                    window.location.href = "/pedidos";
                }, 500);
            }).fail((err) => {
                notyf.error("Ocorreu um erro a fechar a sessão");
            })
        })
    </script>
@stop
