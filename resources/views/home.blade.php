@include('layouts.includes')
@extends('layouts.loader')

@section('content')
    <!DOCTYPE html>
    <html lang="en">

    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <meta http-equiv="X-UA-Compatible" content="ie=edge">
        <title>Sessões</title>
        <link rel="stylesheet" href="{{ mix('resources/css/home.css') }}">
        </link>
    </head>

    <div class="loaderFADE visually-hidden">
        <div class="loader-container" id="lc">
            <div class="loader2"></div>
        </div>
    </div>

    <body style="padding: 50px 160px">
        <div class="d-flex justify-content-center">
            <div class="left-side pe-5 me-5" style="width: 40%">
                <h1 class="text-coloring" style="font-size: 50px; font-weight: 700;">Começar nova sessão</h1>
                <hr>
                <div class="new-session-card">
                    <h2 class="form-label">Nome:</h2>
                    <input id="session_name" type="text" class="form-control" placeholder="Nome para sessão"
                        style="height: 50px; font-size: 20px !important; letter-spacing: 1px;">
                    <button id="confirm" style="height: 70px; font-size: 25px;"
                        class="btn btn-primary w-100 mt-4">Confirmar</button>
                </div>
                <button onclick="window.location.href = '/items'" id="see_stats"
                    style="height: 70px; font-size: 25px; margin-top: 90px !important;"
                    class="btn btn-primary w-100 mt-5">Configurações</button>
                <button id="see_stats" style="height: 70px; font-size: 25px;" class="btn btn-dark w-100 mt-4">Estatísticas
                    Gerais</button>
            </div>
            <div class="right-side" style="width: 60%;">
                <div class="new-session-card h-100">
                    <h1 class="form-label" style="font-weight: 700; letter-spacing: 2.56px;">Sessões</h1>
                    <hr style="color: white; opacity: 1;">

                    <div style="height: 750px; overflow: auto;"
                        class="scrollable d-flex flex-column justify-contents-center">
                        {{-- From DB --}}
                        @foreach ($sess as $session)
                            <div class="session" onclick="window.location.href = '/estatisticas/'+{{$session['id']}}+'?from=home'">
                                <div class="d-flex justify-content-between">
                                    <h3 style="color: white; font-weight: 700; margin-top: 7px;">{{$session['label']}}</h3>
                                    <h3 style="color: white; font-weight: 300; margin-top: 7px;">{{date("d/m/Y", strtotime($session['start']))}}</h3>
                                    <h3 style="color: white; font-weight: 300; margin-top: 10px;"><i
                                            class="fa-solid fa-caret-right"></i></h3>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>
    </body>

    </html>

    <script>
        var notyf = new Notyf();

        $("#confirm").on('click', () => {
            $.ajax({
                method: "post",
                url: "/startsession",
                data: {
                    "_token": "{{ csrf_token() }}",
                    "label": $("#session_name").val()
                }
            }).done((res) => {
                notyf.success(res.message);
                $(".loaderFADE").removeClass("visually-hidden");
                setTimeout(() => {
                    window.location.href = "/pedidos";
                }, 1000);
            }).fail((err) => {
                notyf.error(err.responseJSON.message);
            })
        })
    </script>

@stop
