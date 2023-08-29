        <div class="text-center bg-gray-300 w-4/5 mx-auto rounded">
            <a wire:loading href="{{ back()->getTargetUrl() }}" class="flex justify-center"> <img
                    src="{{ asset('images/retry-svgrepo-com.svg') }}" alt="retry">
            </a>
            @if ($resultMessages)
                <a href="{{ back()->getTargetUrl() }}" class="flex justify-center"> <img
                        src="{{ asset('images/retry-svgrepo-com.svg') }}" alt="retry">
                </a>
            @endif
            <ul style="list-style-type: none;">
                @if ($resultMessages)
                    @foreach ($resultMessages as $message)
                        @php
                            $links = explode('->', $message);
                            $count = count($links);
                        @endphp
                        <li class="w-full">
                        @foreach ($links as $key => $link)
                            <a href="{{ 'https://fr.wikipedia.org/wiki/' . $link }}" target="_blank"
                                style="font-size: larger;color: #03a696">{{ $link }}</a>
                            @if ($key + 1 != $count)
                                <p class="inline mx-2">-></p>
                            @endif
                            @endforeach
                        </li>
                        <br>
                    @endforeach
                @endif
            </ul>
            <div wire:loading>
                <img src="svg-loaders/puff.svg" alt="loader"/>
            </div>
        </div>
