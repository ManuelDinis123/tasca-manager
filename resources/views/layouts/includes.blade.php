{{-- Font --}}
<link href="{{mix("plugins/font/AntonioVariableFont_wght.css")}}" rel="stylesheet">
{{-- Bootstrap --}}
<link rel="stylesheet" href="{{mix('plugins/bootstrap/css.css')}}">
<script src="{{mix('plugins/bootstrap/js.js')}}"></script>
{{-- JQUERY --}}
<script src="{{mix('plugins/jquery/jquery.js')}}"></script>
{{-- Font Awesome 6 --}}
<link href="{{mix('plugins/font-awesome-pro-v6-6.2.0/css/all.min.css')}}" rel="stylesheet"
    type="text/css" />
{{-- Notyf --}}
<link rel="stylesheet" href="{{ mix('node_modules/notyf/notyf.min.css') }}"></link>
<script src="{{ mix('node_modules/notyf/notyf.min.js') }}"></script>
{{-- Datatables --}}
<link rel="stylesheet" href="{{ mix('node_modules/datatables.net-dt/css/jquery.dataTables.min.css') }}"></link>
<script src="{{ mix('node_modules/datatables.net/js/jquery.dataTables.min.js') }}"></script>
{{-- animate.css --}}
<link rel="stylesheet" href="{{ mix('node_modules/animate.css/animate.min.css') }}"></link>
{{-- Swal --}}
<link rel="stylesheet" href="{{ mix('node_modules/sweetalert2/dist/sweetalert2.min.css') }}"></link>
<script src="{{ mix('node_modules/sweetalert2/dist/sweetalert2.min.js') }}"></script>
{{-- Chart.js --}}
<script src="{{ mix('plugins/chartjs/chart.js') }}"></script>

{{-- ! Stuff that should always be the last to be included --}}

{{-- Global JS --}}
<script src="{{ mix('resources/js/global.js') }}"></script>
{{-- Global CSS --}}
<link rel="stylesheet" href="{{ mix('resources/css/global.css') }}"></link>