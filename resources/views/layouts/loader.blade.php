<style>
    .dots-container {
        display: flex;
        align-items: center;
        justify-content: center;
        height: 100%;
        width: 100%;
    }

    .dot {
        height: 20px;
        width: 20px;
        margin-right: 10px;
        border-radius: 10px;
        background-color: #b3d4fc;
        animation: pulse 1.5s infinite ease-in-out;
    }

    .dot:last-child {
        margin-right: 0;
    }

    .dot:nth-child(1) {
        animation-delay: -0.3s;
    }

    .dot:nth-child(2) {
        animation-delay: -0.1s;
    }

    .dot:nth-child(3) {
        animation-delay: 0.1s;
    }

    @keyframes pulse {
        0% {
            transform: scale(0.8);
            background-color: #b3d4fc;
            box-shadow: 0 0 0 0 rgba(178, 212, 252, 0.7);
        }

        50% {
            transform: scale(1.2);
            background-color: #6793fb;
            box-shadow: 0 0 0 10px rgba(178, 212, 252, 0);
        }

        100% {
            transform: scale(0.8);
            background-color: #b3d4fc;
            box-shadow: 0 0 0 0 rgba(178, 212, 252, 0.7);
        }

    }
    #content {
        opacity: 0;
        transition: opacity 0.5s ease;
    }
</style>
<section id="loading" class="dots-container">
    <div class="dot"></div>
    <div class="dot"></div>
    <div class="dot"></div>
    <div class="dot"></div>
    <div class="dot"></div>
</section>


<div class="height-100 visually-hidden" id="content">
    @yield('content')
</div>

<script>
    $(window).on('load', () => {
        setTimeout(() => {
            $("#loading").fadeOut(500, function() {
                // fadeOut complete. Remove the loading div
                $("#loading").remove(); //makes page more lightweight 
                $("#content").removeClass("visually-hidden");
                $("#content").css("opacity", 1);
            });
        }, 1000);
    })
</script>
