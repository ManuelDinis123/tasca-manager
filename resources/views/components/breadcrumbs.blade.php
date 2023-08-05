{{-- Create breadcrumbs --}}
<title>{{$title}}</title>
<div class="breadcrumbs {{isset($padding) ? ($padding?'pb-4':'') : 'pb-4'}}">
    <div class="d-flex flex-row bread-container">
        <div class="d-flex justify-content-start align-items-center">
            <h1 class="breadcrumbs-title" id="breadcrumb_title">{{ $title }}</h1>
            @if (isset($crumbs))
                <div class="d-flex flex-row ms-4">
                    @foreach ($crumbs as $key => $crumb)
                        <a id="breadcrumb_redirect" {{$crumb['link']?"href=".$crumb['link']."":''}}
                            class="{{$crumb['link']!=null?'crumbs':''}} me-2 text-muted">{{ $crumb['label'] }}</a>
                        @if (count($crumbs) != $key + 1)
                            <span class="text-muted me-2">-</span>
                        @endif
                    @endforeach
                </div>
            @endif
        </div>
    </div>
    @if (isset($separator) && $separator)
        <hr>
    @endif
</div>

<style>
    .breadcrumbs-title {
        color: rgb(46, 46, 46);
        font-weight: 700;
    }

    .crumbs {
        transition: all 0.2s;
    }

    .crumbs:hover {
        color: rgb(0, 0, 0) !important;
        text-decoration: underline;
    }

    .bread-container{
        background-color: white;
        box-shadow: 0px 4px 0px 0px rgba(0, 0, 0, 0.25) !important;
        width: fit-content;
        padding: 10px 15px;
        border-radius: 10px;
    }
</style>
