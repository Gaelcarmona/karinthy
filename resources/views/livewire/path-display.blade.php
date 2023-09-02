<div class="md:h-172 md:w-172 flex rounded p-3 items-center">
    <ul style="list-style-type: none;">
        @if ($paths)
            @if ($paths->data !== null)
                @foreach (json_decode($paths->data) as $path)
                    <li class="w-full">
                        <a href="{{ 'https://fr.wikipedia.org/wiki/' . $start }}" target="_blank"
                            style="font-size: larger;color: #03a696">{{ $start }}</a>
                        <p class="inline mx-2">-></p>
                        @foreach ($path as $key => $link)
                            @php
                                $entry = \App\Models\Entry::find($link);
                            @endphp
                            <a href="{{ 'https://fr.wikipedia.org/wiki/' . $entry->title }}" target="_blank"
                                style="font-size: larger;color: #03a696">{{ $entry->title }}</a>
                            <p class="inline mx-2">-></p>
                        @endforeach
                        <a href="{{ 'https://fr.wikipedia.org/wiki/' . $end }}" target="_blank"
                            style="font-size: larger;color: #03a696">{{ $end }}</a>
                    </li>
                    <br>
                @endforeach
            @else
            <li class="w-full">
                <a href="{{ 'https://fr.wikipedia.org/wiki/' . $start }}" target="_blank"
                    style="font-size: larger;color: #03a696">{{ $start }}</a>
                <p class="inline mx-2">-></p>
                <a href="{{ 'https://fr.wikipedia.org/wiki/' . $end }}" target="_blank"
                    style="font-size: larger;color: #03a696">{{ $end }}</a>
            </li>
            @endif
        @endif
    </ul>
</div>
<script>
    setInterval(function() {
        @this.call('loadPath');
    }, 1000);
</script>
