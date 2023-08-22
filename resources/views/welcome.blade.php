<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Laravel</title>
    <script defer src="https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js"></script>
    {{--    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.1/dist/css/bootstrap.min.css" rel="stylesheet"--}}
    {{--        integrity="sha384-4bw+/aepP/YC94hEpVNVgiZdgIC5+VKNBQNGCHeKRQN+PtmoHDEXuppvnDJzQIu9" crossorigin="anonymous">--}}
    {{--    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.1/dist/js/bootstrap.bundle.min.js"--}}
    {{--        integrity="sha384-HwwvtgBNo3bZJJLYd8oVXjrBZt8cqVSpeBNS5n7C8IVInixGAoxmnlMuBnhbgrkm" crossorigin="anonymous">--}}
    {{--    </script>--}}
    @vite('resources/css/app.css')
    @livewireStyles
</head>

<body class="antialiased d-flex flex-column" style="background-color: #adb5bd;">
<nav style="background-color:#012e40" class="p-4 h-10">
    <div class='mx-auto text-center'>
        <a class="navbar-brand text-white" href="https://fr.wikipedia.org/wiki/Frigyes_Karinthy" target="_blank">
            Karinthy
        </a>
    </div>
</nav>

<main class="pt-4 flex-grow-1 ">
    <livewire:search/>
    <livewire:results/>
</main>
{{--<footer class='text-white py-4 mt-auto' style="background-color:#012e40">--}}
{{--    <p class='mx-auto text-center'>Juillet 2023 ---}}
{{--        <a target="blank" href="https://www.gaelcarmona.com" target="_blank">Gaël Carmona</a>--}}
{{--    </p>--}}
{{--</footer>--}}



@livewireScripts
</body>

</html>
