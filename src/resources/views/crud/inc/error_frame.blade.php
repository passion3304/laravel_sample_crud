<style>
.error-frame {
    display: none;
    position: fixed;
    z-index: 1020;
    top: 0;
}
.error-frame .content {
    --width: 90vw;
    --height: 80vh;
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
.error-frame iframe {
    border: 0;
    height: 100%;
}
.error-frame .close {
    position: absolute;
    right: 0.8rem;
    top: 0.4rem;
    cursor: pointer;
}
.error-frame .background {
    position: absolute;
    background-color: #0002;
    width: 100vw;
    height: 100vh;
}
.error-frame.active {
    display: block;
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

<div class="error-frame">
    <div class="background"></div>
    <div class="content">
        <div class="close">×</div>
        <iframe></iframe>
    </div>
</div>
