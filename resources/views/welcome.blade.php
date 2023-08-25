<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Laravel</title>
    <script defer src="https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js"></script>
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
@livewireScripts
</body>

</html>
