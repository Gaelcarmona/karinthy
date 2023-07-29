<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Laravel</title>
    <script defer src="https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.1/dist/css/bootstrap.min.css" rel="stylesheet"
        integrity="sha384-4bw+/aepP/YC94hEpVNVgiZdgIC5+VKNBQNGCHeKRQN+PtmoHDEXuppvnDJzQIu9" crossorigin="anonymous">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.1/dist/js/bootstrap.bundle.min.js"
        integrity="sha384-HwwvtgBNo3bZJJLYd8oVXjrBZt8cqVSpeBNS5n7C8IVInixGAoxmnlMuBnhbgrkm" crossorigin="anonymous">
    </script>
    @livewireStyles
</head>

<body class="antialiased d-flex flex-column min-vh-100" style="background-color: #adb5bd;">
    <livewire:navbar />
    <main class="pt-4 flex-grow-1">
        <livewire:search />
        <livewire:results />
    </main>
    <footer class='footer mt-auto text-white py-4'style="background-color:#012e40">
        <p class='mx-auto text-center'>Juillet 2023 - <a target="blank" class="text-white" href="https://www.gaelcarmona.com">Gaël Carmona</a> </p>
    </footer>
    @livewireScripts
</body>

</html>
