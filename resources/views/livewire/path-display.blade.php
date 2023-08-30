<div class="ml-[40%]">
    <ul style="list-style-type: none;">
        @if ($paths)
            @foreach (json_decode($paths->data) as $path)
                <li class="w-full">
                    <a href="{{ 'https://fr.wikipedia.org/wiki/' . $start }}" target="_blank"
                        style="font-size: larger;color: #03a696">{{ $start }}</a>
                    <p class="inline mx-2">-></p>
                    @foreach ($path as $key => $link)
                        <a href="{{ 'https://fr.wikipedia.org/wiki/' . $link }}" target="_blank"
                            style="font-size: larger;color: #03a696">{{ $link }}</a>
                        <p class="inline mx-2">-></p>
                    @endforeach
                    <a href="{{ 'https://fr.wikipedia.org/wiki/' . $end }}" target="_blank"
                        style="font-size: larger;color: #03a696">{{ $end }}</a>
                </li>
                <br>
            @endforeach
        @endif
    </ul>
</div>
<script>
    setInterval(function() {
        @this.call('loadPath');
    }, 1000);
</script>
