<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Karinthy</title>
    <script defer src="https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <script src="https://d3js.org/d3.v5.min.js"></script>
    <script type="text/javascript" src="https://cdnjs.cloudflare.com/ajax/libs/graphdracula/1.0.3/dracula.min.js"></script>
    @vite('resources/css/app.css')
    @livewireStyles
</head>

<body class="flex flex-col h-screen justify-between"
style="background: radial-gradient(circle#F3F4F6 0%,#F3F4F6 100%);">
<nav style="background: radial-gradient(circle, #012e40 0%, #aad7e9 93%);" class="p-4 top-0 w-full">
        <div class='mx-auto text-center'>
            <a class="navbar-brand text-white" href="https://fr.wikipedia.org/wiki/Frigyes_Karinthy" target="_blank">
                Karinthy
            </a>
        </div>
    </nav>
    <main class="w-4/5 self-center items-center">
        <livewire:search />
        <livewire:path-display />
        <livewire:results />
    </main>
    <footer class='text-white' style="background: radial-gradient(circle, #012e40 0%, #aad7e9 93%);">
        <p class='mx-auto text-center p-4'>Juillet 2023 -
            <a href="https://www.gaelcarmona.com" target="_blank">Gaël Carmona</a>
        </p>
    </footer>
    @livewireScripts
</body>

</html>
