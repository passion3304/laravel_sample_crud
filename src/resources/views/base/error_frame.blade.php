<style>
.error-frame {
    position: fixed;
    z-index: 1020;
    top: 0;
}
.error-frame .content {
    --width: 80vw;
    --height: 60vh;
    position: absolute;
    width: var(--width);
    height: var(--height);
    box-shadow: 0px 0px 4rem;
    transform: translate(calc((100vw - var(--width)) / 2), calc((100vh - var(--height)) / 2));
    border-radius: 0.4rem;
    background-color: #FFF;
    display: flex;
    flex-direction: column;
    overflow: hidden;
}
.error-frame .header {
    padding: 2rem 1.4rem 1.4rem;
}
.error-frame .header a {
    color: #FFF;
}
.error-frame ul {
    list-style: none;
    overflow-y: auto;
    padding: 0;
    margin: 0;
}
.error-frame ul li {
    border-top: 1px solid #0002;
    padding: 0.4rem 1.4rem;
}
.error-frame ul li a {
    color: var(--dark);
}
.error-frame p {
    margin: 0
}
.close {
    position: absolute;
    right: 0.8rem;
    top: 0.4rem;
    cursor: pointer;
}
.background {
    position: absolute;
    background-color: #0002;
    width: 100vw;
    height: 100vh;
}
.fadeIn {
    opacity: 0;
    animation-name: fadeIn;
    animation-duration: .4s;
    animation-fill-mode: forwards;
}
@keyframes fadeIn {
    from { opacity: 0; }
    to { opacity: 1; }
}
</style>

<div class="error-frame fadeIn">
    <div class="background"></div>
    <div class="content">
        <div class="close">×</div>
        <div class="header bg-danger">
            <p><strong>{{ $message }}</strong></p>
            @if(function_exists('link_to_code_editor'))
            <p>in <a href="{{ link_to_code_editor("$file:$line") }}">{{ Str::of($file)->replace('/', '\\')->after(base_path() . '\\') }} (line {{ $line }})</a></p>
            @else
            <p>in {{ Str::of($file)->replace('/', '\\')->after(base_path() . '\\') }} (line {{ $line }})</p>
            @endif
        </div>
        <ul>
            @foreach(collect($trace) as $entry)
            <li>
                <p>at {{ Str::afterLast($entry['class'] ?? '', '\\') }}{{ $entry['type'] ?? '' }}<strong>{{ $entry['function'] }}</strong></p>
                @if(function_exists('link_to_code_editor'))
                <p>in <a href="{{ link_to_code_editor($entry['file'] . ':' . $entry['line']) }}"><strong>{{ Str::of($entry['file'])->replace('/', '\\')->afterLast('\\') }}</strong> (line {{ $entry['line'] }})</a></p>
                @else
                <p>in <strong>{{ Str::of($entry['file'])->replace('/', '\\')->afterLast('\\') }}</strong> (line {{ $entry['line'] }})</p>
                @endif
            </li>
            @endforeach
        </ul>
    </div>
</div>
