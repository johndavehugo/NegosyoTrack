@props([
    'webm' => null,
    'mov' => null,
    'mp4' => null,
])

@php
    $webm = $webm ?: asset('animations/mascot/waves-hello.webm');
    $mov = $mov ?: asset('animations/mascot/waves-hello.mov');
    $mp4 = $mp4 ?: asset('animations/mascot/waves-hello.mp4');
@endphp

<video
    {{ $attributes->merge(['class' => 'block h-auto max-w-full select-none']) }}
    width="360"
    height="360"
    autoplay
    loop
    muted
    playsinline
    preload="metadata"
    aria-hidden="true"
    data-mascot
    data-mascot-webm="{{ $webm }}"
    data-mascot-hevc="{{ $mov }}"
    data-mascot-mp4="{{ $mp4 }}"
>
    {{-- No-JS fallback. The script below chooses the right alpha format. --}}
    <source src="{{ $mov }}" type='video/mp4; codecs="hvc1"'>
    <source src="{{ $webm }}" type="video/webm">
    <source src="{{ $mp4 }}" type="video/mp4">
</video>

@once
    <script>
        (() => {
            const videos = document.querySelectorAll('video[data-mascot]');

            if (!videos.length) {
                return;
            }

            const reduceMotion = window.matchMedia('(prefers-reduced-motion: reduce)');
            const apple = /iPad|iPhone|iPod/.test(navigator.userAgent)
                || /^((?!chrome|android).)*safari/i.test(navigator.userAgent);

            const play = (video) => {
                if (reduceMotion.matches) {
                    return;
                }

                video.play().catch(() => {});
            };

            videos.forEach((video) => {
                const preferred = apple
                    ? video.dataset.mascotHevc
                    : video.dataset.mascotWebm;

                // Some Chromium builds decode HEVC but cannot composite its
                // alpha channel, which leaves a box behind the mascot. Choose
                // the alpha format explicitly instead of relying on <source>.
                if (preferred) {
                    video.src = preferred;
                }

                const fallbacks = apple
                    ? [video.dataset.mascotWebm, video.dataset.mascotMp4]
                    : [video.dataset.mascotMp4];

                let fallbackIndex = 0;

                video.addEventListener('error', () => {
                    const fallback = fallbacks[fallbackIndex++];

                    if (fallback && video.src !== fallback) {
                        video.src = fallback;
                        play(video);
                    }
                });

                if (reduceMotion.matches) {
                    video.removeAttribute('autoplay');
                    video.preload = 'auto';
                    video.load();
                    video.addEventListener('loadeddata', () => video.pause(), { once: true });
                    video.pause();
                } else {
                    play(video);
                }
            });

            reduceMotion.addEventListener?.('change', () => {
                videos.forEach((video) => {
                    if (reduceMotion.matches) {
                        video.pause();
                    } else {
                        play(video);
                    }
                });
            });

            if ('IntersectionObserver' in window) {
                const observer = new IntersectionObserver((entries) => {
                    entries.forEach((entry) => {
                        if (entry.isIntersecting) {
                            play(entry.target);
                        } else {
                            entry.target.pause();
                        }
                    });
                }, { threshold: 0.1 });

                videos.forEach((video) => observer.observe(video));
            }

            document.addEventListener('visibilitychange', () => {
                videos.forEach((video) => {
                    if (document.hidden) {
                        video.pause();
                    } else {
                        play(video);
                    }
                });
            });
        })();
    </script>
@endonce
