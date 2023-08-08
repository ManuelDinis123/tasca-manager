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
            [
                'label' => 'Quantidade do modificador:',
                'type' => 'number',
                'id' => 'quantityMod',
                'placeholder' => 'Quantidade do modificador',
                'step' => '0.01',
            ],
        ],
        'select' => [
            'configs' => [
                'id' => 'modifiersSelect',
                'label' => 'Modificadores:',
                'default' => 'Selecionar Modificador',
                'disabled' => false,
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

    <title>
        Pedidos</title>
    <link rel="stylesheet" href="{{ mix('resources/css/orders.css') }}">

    <input type="hidden" id="edit_id">

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
                        @php
                            $total_price = 0;
                        @endphp
                        @if (session()->get('items') !== null)
                            @foreach (session()->get('items') as $key => $item)
                                <div id="item{{ $key }}" onclick="overviewItemOpen({{$key}})" oncontextmenu="deleteItem({{$key}}); return false;"
                                    class="d-flex justify-content-between overview-item mb-3 itm-{{ $item['id'] }}">
                                    <div style="color: white;">
                                        <span>{{ $item['quantity'] }} x </span><span>{{ $item['name'] }}
                                            @if ($item['modifier'] != null)
                                                <i style="color: white; font-size:15px; vertical-align: middle;"
                                                    class="fa-solid fa-french-fries"></i>
                                            @endif
                                        </span>
                                    </div>
                                    <span
                                        style="color: white">{{ $item['quantity'] * $item['price'] + $item['modifier_price'] * $item['modifier_quantity'] }}€</span>
                                </div>
                                @php
                                    $total_price += $item['quantity'] * $item['price'] + $item['modifier_price'] * $item['modifier_quantity'];
                                @endphp
                            @endforeach
                        @endif
                        <script></script>
                    </div>
                    <div id="order_total" class="d-flex align-items-end justify-content-end">
                        <span style="font-size: 26px; font-weight:800; color:white;">{{ $total_price }}€</span>
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

        // Filtering

        $("#search_items").on("keydown", function(event){
            if(event.keyCode == 13){
                
            }
        })

        // Order system

        $("#chooseItem").on("hidden.bs.modal", () => {
            $(".removable-option").remove();
            $("#quantity").val("");
            $("#quantityMod").val("");
            $("#modifiersSelect").val(null);
            $("#quantityMod").removeAttr("disabled");
            $("#modifiersSelect").removeAttr("disabled", "false");
        })

        $(".item").on('click', function() {
            $(".loaderFADE").removeClass("visually-hidden");
            var itemId = this.className.replace("item item-", "");
            $("#itemId").val(itemId);
            gettingModifiers(itemId);
        })

        $("#save").on('click', () => {
            if (hasEmpty(["quantity"])) return;
            console.log($("#edit_id").val())
            $.ajax({
                method: "post",
                url: "/addcart",
                data: {
                    "_token": "{{ csrf_token() }}",
                    "id": $("#itemId").val(),
                    "quantity": $("#quantity").val(),
                    "quantityMod": $("#quantityMod").val(),
                    "modifier": $("#modifiersSelect").val() == 0 || $("#modifiersSelect").val() == null ?
                        null : $("#modifiersSelect").val(),
                    "isEdit": $("#edit_id").val()
                }
            }).done((res) => {
                notyf.success(res.message);
                $("#chooseItem").modal("toggle");
                $("#order_view").load(" #order_view > *");
                $("#order_total").load(" #order_total > *");
                $("#edit_id").val(null);
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
                    $("#order_total").load(" #order_total > *");
                })
            })
        })

        function deleteItem(id){
            $(".loaderFADE").removeClass("visually-hidden");
            $.ajax({
                method: "post",
                url: "/removeoverviewitem",
                data: {
                    "_token": "{{csrf_token()}}",
                    "id": id
                }
            }).done((res)=>{
                notyf.success(res.message);
                $("#order_view").load(" #order_view > *");
                $("#order_total").load(" #order_total > *");
                $(".loaderFADE").addClass("visually-hidden");
            }).fail((err)=>{
                $(".loaderFADE").addClass("visually-hidden");
                console.error(err);
            })            
        }

        function overviewItemOpen(id) {
            $("#edit_id").val(id);
            $.ajax({
                method: "post",
                url: "/getitemdata",
                data: {
                    "_token": "{{ csrf_token() }}",
                    "id": id
                }
            }).done((res) => {
                $("#quantity").val(res.quantity);
                $("#quantityMod").val(res.modifier_quantity);
                $("#itemId").val(res.id);
                gettingModifiers(res.id, res.modifier)
            })
        }

        $("#confirm_order").on('click', () => {
            swal.fire({
                title: "Confirmar Pedido",
                html: `
                        <input type="number" step="0.01" class="swal2-input" style="width:373px" placeholder="Valor pelo cliente" id="clientValue"><br />
                        <span class="text-muted">Adicione o valor dado pelo cliente para calcular o troco, não é obrigatório</span>
                    `,
                showCancelButton: true,
                inputPlaceholder: "Valor do cliente",
                confirmButtonColor: '#27374d',
                confirmButtonText: 'Confirmar',
                denyButtonText: `Cancelar`,
            }).then((res) => {
                if (res.isConfirmed) {
                    $.ajax({
                        method: "post",
                        url: "/confirmorder",
                        data: {
                            "_token": "{{ csrf_token() }}",
                            "client_value": $("#clientValue").val()
                        }
                    }).done((res) => {
                        $("#order_view").load(" #order_view > *");
                        $("#order_total").load(" #order_total > *");
                        notyf.success(res.message);
                        if (res.change != "NO_VALUE") {
                            swal.fire({
                                icon: "info",
                                title: "Troco: " + res.change + "€",
                                showCancelButton: false,
                                confirmButtonColor: '#27374d',
                                confirmButtonText: 'Ok',
                            })
                        }
                    }).fail((err) => {
                        notyf.error(err.responseJSON.message)
                    })
                }
            });
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

        $("#quantity").on('keyup', function() {
            if (!$('#quantityMod').prop('disabled')) {
                $("#quantityMod").val(this.value);
            };
        })

        // Gets modifiers for select and opens the modal
        function gettingModifiers(itemId, selected_option = null) {
            $.ajax({
                method: "post",
                url: "/getmods",
                data: {
                    "_token": "{{ csrf_token() }}",
                    "id": itemId
                }
            }).done((res) => {
                if (res.length <= 0) {
                    $("#quantityMod").attr("disabled", "disabled");
                    $("#modifiersSelect").attr("disabled", "disabled");
                } else {
                    $.each(res, (key, val) => {
                        $("#modifiersSelect").append(`
                            <option class="removable-option" value="${val.id}">${val.name}</option>
                        `);
                    })
                }
                $(".loaderFADE").addClass("visually-hidden");
                if (selected_option != null) {
                    $("#modifiersSelect").val(selected_option)
                }
                $("#chooseItem").modal("toggle");
            }).fail((err) => {
                console.log(err);
                $(".loaderFADE").addClass("visually-hidden");
            })
        }
    </script>
@stop
