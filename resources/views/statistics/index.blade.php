@include('layouts.includes')
@extends('layouts.loader')

@section('content')

    <link rel="stylesheet" href="{{ mix('resources/css/stats.css') }}">

    <div class="container mt-5">
        {{-- Breadcrumbs --}}
        @php
            $breadc_title = $ss != 'NO_SESSION' ? 'Estatísticas de ' . $ss['label'] : 'Estatísticas Gerais';
        @endphp
        @component('components.breadcrumbs', [
            'title' => $breadc_title,
            'crumbs' => [
                [
                    'link' => isset($_GET['from']) ? ($_GET['from'] == 'home' ? '/' : '/pedidos') : '/',
                    'label' => isset($_GET['from']) ? ($_GET['from'] == 'home' ? 'Sessões' : 'Pedidos') : 'Sessões',
                ],
                ['link' => '/items', 'label' => 'Estatísticas'],
            ],
            'separator' => true,
        ])
        @endcomponent
    </div>

    {{-- Financial Stats --}}

    <div class="container" style="padding-bottom: 150px">
        <div class="d-flex justify-content-center">
            <h1 style="font-weight: 800">Estatísticas Financeiras</h1>
        </div>
        <div class="stat-container">
            <div class="d-flex flex-row">
                <div class="money-info w-25">
                    <div class="d-flex justify-content-center h-100 align-items-center">
                        <div>
                            <span>Total Bruto:</span><span id="tb_price" class="stats-money-val">
                                {{ $money_stats['bruto'] }}€</span><br>
                            <span>Despesas:</span><span id="d_price" class="stats-money-val">
                                {{ $money_stats['despesas'] }}€</span><br>
                            <span>Total Liquido:</span><span id="tl_price" class="stats-money-val">
                                {{ $money_stats['liquido'] }}€</span>
                        </div>
                    </div>
                </div>
                <div class="w-75">
                    <canvas id="myChart"></canvas>
                </div>
            </div>
        </div>

        {{-- Per category --}}

        <div class="d-flex justify-content-center mt-5">
            <h1 style="font-weight: 800">Vendas por Categoria</h1>
        </div>
        <input type="hidden" id="category_sales_data" value="{{ json_encode($category_sales) }}">
        <div class="stat-container" style="height: 810px">
            <div class="d-flex justify-content-start mt-3 mb-3">
                <button class="btn btn-white me-2" id="quantitativoBTN">Quantitativo</button>
                <button class="btn btn-dark" id="gainsBTN">Ganhos</button>
            </div>
            <div id="quantitativo_chart" class="barChart-contain">
                <canvas id="categoryChart"></canvas>
            </div>
            <div id="gains_chart" class="barChart-contain visually-hidden">
                <canvas id="gainscategoryChart"></canvas>
            </div>
        </div>

        {{-- Per item --}}

        <div class="d-flex justify-content-center mt-5">
            <h1 style="font-weight: 800">Vendas por Items</h1>
        </div>
        <input type="hidden" id="item_sales_data" value="{{ json_encode($item_sales) }}">
        <div class="stat-container" style="height: 810px">
            <div class="d-flex justify-content-start mt-3 mb-3">
                <button class="btn btn-white me-2" id="items_quantitativoBTN">Quantitativo</button>
                <button class="btn btn-dark" id="items_gainsBTN">Ganhos</button>
            </div>
            <div id="items_quantitativo_chart" class="barChart-contain">
                <canvas id="itemsChart"></canvas>
            </div>
            <div id="items_gains_chart" class="barChart-contain visually-hidden">
                <canvas id="gainsItemsChart"></canvas>
            </div>
        </div>

        {{-- Per session (only when viewing global stats) --}}
        <input type="hidden" id="isGlobal" value="{{ $ss == 'NO_SESSION' ? 'true' : 'false' }}">
        @if ($ss == 'NO_SESSION')
            <div class="d-flex justify-content-center mt-5">
                <h1 style="font-weight: 800">Vendas por Sessão</h1>
            </div>
            <input type="hidden" id="per_session_data" value="{{ json_encode($per_session) }}">
            <div class="stat-container" style="height: 1390px">
                <div id="session_chart" class="barChart-contain">
                    <canvas id="sessionChart"></canvas>
                </div>
                <div id="count_session_chart" class="barChart-contain mt-4">
                    <canvas id="countSessionChart"></canvas>
                </div>
            </div>
        @endif

        {{-- Total --}}
        <div class="ttl-container mt-5">
            <div class="d-flex justify-content-center align-items-center">
                <h2 style="font-weight: 750">Total de pedidos:</h2>
                <h1 class="ms-3" style="font-weight: 900">{{ $total }}</h1>
            </div>
        </div>

    </div>

    <script>
        // Estatisticas gerais
        if ($("#isGlobal").val() == "true") {
            const perSession = JSON.parse($("#per_session_data").val());
            // Prepare array for dataset
            var sessionDataSet = {
                "labels": [],
                "sales": [],
                "bruto": [],
                "despesas": [],
                "orders_count": [],
                "color": [],
            };
            $.each(perSession, (key, value) => {
                sessionDataSet['labels'][key] = value.label;
                sessionDataSet['sales'][key] = value.liquido;
                sessionDataSet['bruto'][key] = value.bruto;
                sessionDataSet['despesas'][key] = value.despesas;
                sessionDataSet['orders_count'][key] = value.orders_count;
                sessionDataSet['color'][key] = addAlpha("#1d2c3f", 0.8);
            })

            const session_chart = document.getElementById('sessionChart');


            new Chart(session_chart, {
                type: 'line',
                data: {
                    labels: sessionDataSet.labels,
                    datasets: [{
                            label: 'Liquido',
                            data: sessionDataSet.sales,
                            tension: 0.1,
                            fill: false,
                            borderColor: '#3b9119',
                        },
                        {
                            label: 'Bruto',
                            data: sessionDataSet.bruto,
                            tension: 0.1,
                            fill: false,
                            borderColor: '#5482cc',
                        },
                        {
                            label: 'Despesas',
                            data: sessionDataSet.despesas,
                            tension: 0.1,
                            fill: false,
                            borderColor: '#cf3232',
                        },
                    ]
                }
            });

            Chart.defaults.global.defaultFontColor = "#000";

            const csc = document.getElementById('countSessionChart');

            new Chart(csc, {
                type: 'bar',
                data: {
                    labels: sessionDataSet.labels,
                    datasets: [{
                        label: 'Numero de Pedidos por Sessão',
                        data: sessionDataSet.orders_count,
                        backgroundColor: sessionDataSet.color,
                        borderWidth: 2
                    }]
                },
                options: {
                    scales: {
                        y: {
                            beginAtZero: true
                        }
                    }
                }
            });
        }

        // Estatistica financeira 
        Chart.defaults.global.defaultFontColor = "#000";

        const ctx = document.getElementById('myChart');

        new Chart(ctx, {
            type: 'pie',
            data: {
                labels: ['Total Bruto', 'Despesas', 'Total Liquido'],
                datasets: [{
                    label: 'Calculos de Dinheiro',
                    data: [{{ $money_stats['bruto'] }}, {{ $money_stats['despesas'] }},
                        {{ $money_stats['liquido'] }}
                    ],
                    borderWidth: 1,
                    backgroundColor: [
                        '#27374D',
                        '#9DB2BF',
                        '#DDE6ED',
                    ],
                }]
            },
            options: {
                scales: {
                    y: {
                        beginAtZero: true
                    }
                }
            }
        });

        // Vendas por Categorias
        const salesPerCategory = JSON.parse($("#category_sales_data").val());
        // Prepare array for dataset
        var categoryDataSets = {
            "labels": [],
            "sales": [],
            "color": [],
            "lucro": [],
            "borderColor": [],
        };
        $.each(salesPerCategory, (key, value) => {
            categoryDataSets['labels'][key] = value.label;
            categoryDataSets['sales'][key] = value.sales;
            categoryDataSets['lucro'][key] = value.lucro;
            categoryDataSets['color'][key] = addAlpha(value.color, 0.6);
            categoryDataSets['borderColor'][key] = value.color;
        })

        const categoryChart = document.getElementById('categoryChart');


        new Chart(categoryChart, {
            type: 'bar',
            data: {
                labels: categoryDataSets.labels,
                datasets: [{
                    label: 'Vendas por Categoria (quantitativo)',
                    data: categoryDataSets.sales,
                    backgroundColor: categoryDataSets.color,
                    borderColor: categoryDataSets.borderColor,
                    borderWidth: 2
                }]
            },
            options: {
                scales: {
                    yAxes: [{
                        ticks: {
                            beginAtZero: true
                        }
                    }]
                }
            }
        });

        const gainsCategoryChart = document.getElementById('gainscategoryChart');


        new Chart(gainsCategoryChart, {
            type: 'bar',
            data: {
                labels: categoryDataSets.labels,
                datasets: [{
                    label: 'Vendas por Categoria (ganhos)',
                    data: categoryDataSets.lucro,
                    backgroundColor: categoryDataSets.color,
                    borderColor: categoryDataSets.borderColor,
                    borderWidth: 2
                }]
            },
            options: {
                scales: {
                    yAxes: [{
                        ticks: {
                            beginAtZero: true,
                            callback: function(value, index, values) {
                                return value.toLocaleString("en-US", {
                                    style: "currency",
                                    currency: "EUR"
                                });;

                            }
                        }
                    }]
                }
            }
        });


        // Per Items
        const salesPerItems = JSON.parse($("#item_sales_data").val());
        // Prepare array for dataset
        var itemsDataSets = {
            "labels": [],
            "sales": [],
            "color": [],
            "lucro": [],
            "borderColor": [],
        };
        $.each(salesPerItems, (key, value) => {
            itemsDataSets['labels'][key] = value.name;
            itemsDataSets['sales'][key] = value.total;
            itemsDataSets['lucro'][key] = value.lucro;
            itemsDataSets['color'][key] = addAlpha(value.color, 0.6);
            itemsDataSets['borderColor'][key] = value.color;
        })

        const itemsChart = document.getElementById('itemsChart');


        new Chart(itemsChart, {
            type: 'bar',
            data: {
                labels: itemsDataSets.labels,
                datasets: [{
                    label: 'Vendas por Items (quantitativo)',
                    data: itemsDataSets.sales,
                    backgroundColor: itemsDataSets.color,
                    borderColor: itemsDataSets.borderColor,
                    borderWidth: 2
                }]
            },
            options: {
                scales: {
                    yAxes: [{
                        ticks: {
                            beginAtZero: true
                        }
                    }]
                }
            }
        });

        const gainsItemsChart = document.getElementById('gainsItemsChart');


        new Chart(gainsItemsChart, {
            type: 'bar',
            data: {
                labels: itemsDataSets.labels,
                datasets: [{
                    label: 'Vendas por Items (ganhos)',
                    data: itemsDataSets.lucro,
                    backgroundColor: itemsDataSets.color,
                    borderColor: itemsDataSets.borderColor,
                    borderWidth: 2
                }]
            },
            options: {
                scales: {
                    yAxes: [{
                        ticks: {
                            beginAtZero: true,
                            callback: function(value, index, values) {
                                return value.toLocaleString("en-US", {
                                    style: "currency",
                                    currency: "EUR"
                                });;

                            }
                        }
                    }]
                }
            }
        });

        // Toggle Buttons

        $("#quantitativoBTN").on('click', () => {
            $("#quantitativoBTN").removeClass("btn-dark");
            $("#quantitativoBTN").addClass("btn-white");
            $("#gainsBTN").removeClass("btn-white");
            $("#gainsBTN").addClass("btn-dark");
            $("#gains_chart").addClass("visually-hidden");
            $("#quantitativo_chart").removeClass("visually-hidden");
        })
        $("#gainsBTN").on('click', () => {
            $("#gainsBTN").removeClass("btn-dark");
            $("#gainsBTN").addClass("btn-white");
            $("#quantitativoBTN").removeClass("btn-white");
            $("#quantitativoBTN").addClass("btn-dark");
            $("#quantitativo_chart").addClass("visually-hidden");
            $("#gains_chart").removeClass("visually-hidden");
        })
        $("#items_quantitativoBTN").on('click', () => {
            $("#items_quantitativoBTN").removeClass("btn-dark");
            $("#items_quantitativoBTN").addClass("btn-white");
            $("#items_gainsBTN").removeClass("btn-white");
            $("#items_gainsBTN").addClass("btn-dark");
            $("#items_gains_chart").addClass("visually-hidden");
            $("#items_quantitativo_chart").removeClass("visually-hidden");
        })
        $("#items_gainsBTN").on('click', () => {
            $("#items_gainsBTN").removeClass("btn-dark");
            $("#items_gainsBTN").addClass("btn-white");
            $("#items_quantitativoBTN").removeClass("btn-white");
            $("#items_quantitativoBTN").addClass("btn-dark");
            $("#items_quantitativo_chart").addClass("visually-hidden");
            $("#items_gains_chart").removeClass("visually-hidden");
        })
    </script>
@stop
