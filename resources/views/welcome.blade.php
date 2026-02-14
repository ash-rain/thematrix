<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="dark">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">

        <title>The Matrix: Terminal</title>

        <link rel="icon" href="/favicon.ico" sizes="any">
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=fira-code:400,500,600,700" rel="stylesheet" />

        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="min-h-screen bg-black font-mono text-green-400 antialiased selection:bg-green-500/30 overflow-x-hidden">
        {{-- Scanline overlay --}}
        <div class="pointer-events-none fixed inset-0 z-50" style="background: repeating-linear-gradient(0deg, transparent, transparent 2px, rgba(0, 255, 65, 0.03) 2px, rgba(0, 255, 65, 0.03) 4px);"></div>

        {{-- Vignette --}}
        <div class="pointer-events-none fixed inset-0 z-40" style="background: radial-gradient(ellipse at center, transparent 50%, rgba(0,0,0,0.6) 100%);"></div>

        {{-- Matrix Rain Background --}}
        <div class="fixed inset-0 overflow-hidden opacity-15" x-data="{
            columns: [],
            chars: 'アイウエオカキクケコサシスセソタチツテトナニヌネノハヒフヘホマミムメモヤユヨラリルレロワヲン0123456789ABCDEF',
            init() {
                const count = Math.floor(window.innerWidth / 22);
                for (let i = 0; i < count; i++) {
                    let str = '';
                    for (let j = 0; j < 40; j++) {
                        str += this.chars[Math.floor(Math.random() * this.chars.length)];
                    }
                    this.columns.push({
                        text: str,
                        left: (i / count) * 100,
                        duration: 6 + Math.random() * 12,
                        delay: Math.random() * -18,
                    });
                }
            }
        }">
            <template x-for="(col, i) in columns" :key="i">
                <span
                    class="matrix-rain-column absolute top-0 font-mono"
                    :style="`left: ${col.left}%; animation-duration: ${col.duration}s; animation-delay: ${col.delay}s;`"
                    x-text="col.text"
                ></span>
            </template>
        </div>

        {{-- Navigation --}}
        <nav class="relative z-30 flex items-center justify-between px-6 py-4 sm:px-10">
            <span class="matrix-glow text-sm font-bold tracking-[0.3em] text-matrix">MATRIX://</span>
            <div class="flex items-center gap-4">
                <a href="{{ route('game.select') }}" class="rounded border border-matrix px-4 py-1.5 text-xs font-bold tracking-widest text-matrix transition-all duration-300 matrix-box-glow hover:bg-matrix/10">
                    PLAY NOW
                </a>
            </div>
        </nav>

        {{-- Hero Section --}}
        <section class="relative z-10 flex min-h-[85vh] flex-col items-center justify-center px-6 text-center matrix-flicker">
            <div class="matrix-fade-in">
                {{-- System boot text --}}
                <p class="mb-8 text-xs tracking-[0.4em] text-matrix-dark">
                    SYSTEM BOOT // SEQUENCE INITIATED // v4.13.2
                </p>

                {{-- Title --}}
                <h1 class="matrix-glow mb-4 text-5xl font-bold tracking-[0.2em] text-matrix sm:text-7xl lg:text-8xl">
                    THE MATRIX
                </h1>
                <div class="mx-auto mb-6 h-px w-64 bg-gradient-to-r from-transparent via-matrix to-transparent sm:w-96"></div>
                <p class="text-2xl tracking-[0.5em] text-matrix-dim sm:text-3xl">
                    TERMINAL
                </p>

                {{-- Subtitle --}}
                <p class="mx-auto mt-8 max-w-lg text-sm leading-relaxed tracking-wide text-matrix-dim/80">
                    An AI-powered text adventure set in the world of The Matrix.
                    Every choice reshapes your reality. The machine learns. The simulation adapts.
                </p>

                {{-- CTA --}}
                <div class="mt-12 flex flex-col items-center gap-4 sm:flex-row sm:justify-center">
                    <a href="{{ route('game.select') }}" class="group relative inline-flex items-center gap-3 rounded border border-matrix px-8 py-3 text-sm font-bold tracking-[0.2em] text-matrix transition-all duration-300 matrix-box-glow hover:bg-matrix/10 hover:shadow-[0_0_30px_rgba(0,255,65,0.3)]">
                        [ ENTER THE MATRIX ]
                    </a>
                </div>

                {{-- Blinking prompt --}}
                <p class="matrix-cursor mt-16 text-xs tracking-widest text-matrix-dark">
                    > WAKE UP
                </p>
            </div>
        </section>

        {{-- Divider --}}
        <div class="relative z-10 mx-auto w-full max-w-4xl px-6">
            <div class="h-px bg-gradient-to-r from-transparent via-matrix-dark to-transparent"></div>
        </div>

        {{-- Features Section --}}
        <section class="relative z-10 mx-auto max-w-5xl px-6 py-24">
            <p class="mb-12 text-center text-xs tracking-[0.4em] text-matrix-dark">
                // SYSTEM CAPABILITIES
            </p>

            <div class="grid gap-6 sm:grid-cols-2 lg:grid-cols-3">
                {{-- Feature 1 --}}
                <div class="rounded border border-matrix-dark bg-matrix-bg/60 p-6 transition-all duration-300 matrix-box-glow-hover">
                    <div class="mb-4 flex h-10 w-10 items-center justify-center rounded border border-matrix-dark text-matrix-dim">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M9.813 15.904 9 18.75l-.813-2.846a4.5 4.5 0 0 0-3.09-3.09L2.25 12l2.846-.813a4.5 4.5 0 0 0 3.09-3.09L9 5.25l.813 2.846a4.5 4.5 0 0 0 3.09 3.09L15.75 12l-2.846.813a4.5 4.5 0 0 0-3.09 3.09ZM18.259 8.715 18 9.75l-.259-1.035a3.375 3.375 0 0 0-2.455-2.456L14.25 6l1.036-.259a3.375 3.375 0 0 0 2.455-2.456L18 2.25l.259 1.035a3.375 3.375 0 0 0 2.455 2.456L21.75 6l-1.036.259a3.375 3.375 0 0 0-2.455 2.456ZM16.894 20.567 16.5 21.75l-.394-1.183a2.25 2.25 0 0 0-1.423-1.423L13.5 18.75l1.183-.394a2.25 2.25 0 0 0 1.423-1.423l.394-1.183.394 1.183a2.25 2.25 0 0 0 1.423 1.423l1.183.394-1.183.394a2.25 2.25 0 0 0-1.423 1.423Z" />
                        </svg>
                    </div>
                    <h3 class="matrix-glow-sm mb-2 text-sm font-bold tracking-wider text-matrix">AI NARRATOR</h3>
                    <p class="text-xs leading-relaxed text-matrix-dim/70">
                        A living game master powered by artificial intelligence. It remembers your choices, adapts the story, and delivers cinematic consequences.
                    </p>
                </div>

                {{-- Feature 2 --}}
                <div class="rounded border border-matrix-dark bg-matrix-bg/60 p-6 transition-all duration-300 matrix-box-glow-hover">
                    <div class="mb-4 flex h-10 w-10 items-center justify-center rounded border border-matrix-dark text-matrix-dim">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="m2.25 15.75 5.159-5.159a2.25 2.25 0 0 1 3.182 0l5.159 5.159m-1.5-1.5 1.409-1.409a2.25 2.25 0 0 1 3.182 0l2.909 2.909M3.75 21h16.5A2.25 2.25 0 0 0 22.5 18.75V5.25A2.25 2.25 0 0 0 20.25 3H3.75A2.25 2.25 0 0 0 1.5 5.25v13.5A2.25 2.25 0 0 0 3.75 21Z" />
                        </svg>
                    </div>
                    <h3 class="matrix-glow-sm mb-2 text-sm font-bold tracking-wider text-matrix">SCENE GENERATION</h3>
                    <p class="text-xs leading-relaxed text-matrix-dim/70">
                        Every scene is brought to life with AI-generated imagery. Rain-slicked streets, neon-lit rooftops, the machine city &mdash; visualized in real time.
                    </p>
                </div>

                {{-- Feature 3 --}}
                <div class="rounded border border-matrix-dark bg-matrix-bg/60 p-6 transition-all duration-300 matrix-box-glow-hover">
                    <div class="mb-4 flex h-10 w-10 items-center justify-center rounded border border-matrix-dark text-matrix-dim">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 5.25a3 3 0 0 1 3 3m3 0a6 6 0 0 1-7.029 5.912c-.563-.097-1.159.026-1.563.43L10.5 17.25H8.25v2.25H6v2.25H2.25v-2.818c0-.597.237-1.17.659-1.591l6.499-6.499c.404-.404.527-1 .43-1.563A6 6 0 1 1 21.75 8.25Z" />
                        </svg>
                    </div>
                    <h3 class="matrix-glow-sm mb-2 text-sm font-bold tracking-wider text-matrix">MEANINGFUL CHOICES</h3>
                    <p class="text-xs leading-relaxed text-matrix-dim/70">
                        Every turn presents three paths: cautious, bold, or unconventional. Your decisions shape health, inventory, and the fate of the resistance.
                    </p>
                </div>

                {{-- Feature 4 --}}
                <div class="rounded border border-matrix-dark bg-matrix-bg/60 p-6 transition-all duration-300 matrix-box-glow-hover">
                    <div class="mb-4 flex h-10 w-10 items-center justify-center rounded border border-matrix-dark text-matrix-dim">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M21 8.25c0-2.485-2.099-4.5-4.688-4.5-1.935 0-3.597 1.126-4.312 2.733-.715-1.607-2.377-2.733-4.313-2.733C5.1 3.75 3 5.765 3 8.25c0 7.22 9 12 9 12s9-4.78 9-12Z" />
                        </svg>
                    </div>
                    <h3 class="matrix-glow-sm mb-2 text-sm font-bold tracking-wider text-matrix">HEALTH & INVENTORY</h3>
                    <p class="text-xs leading-relaxed text-matrix-dim/70">
                        Track your vitals as the simulation tests you. Find medkits, weapons, and key items. Lose it all when the Agents catch up.
                    </p>
                </div>

                {{-- Feature 5 --}}
                <div class="rounded border border-matrix-dark bg-matrix-bg/60 p-6 transition-all duration-300 matrix-box-glow-hover">
                    <div class="mb-4 flex h-10 w-10 items-center justify-center rounded border border-matrix-dark text-matrix-dim">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M20.25 8.511c.884.284 1.5 1.128 1.5 2.097v4.286c0 1.136-.847 2.1-1.98 2.193-.34.027-.68.052-1.02.072v3.091l-3-3c-1.354 0-2.694-.055-4.02-.163a2.115 2.115 0 0 1-.825-.242m9.345-8.334a2.126 2.126 0 0 0-.476-.095 48.64 48.64 0 0 0-8.048 0c-1.131.094-1.976 1.057-1.976 2.192v4.286c0 .837.46 1.58 1.155 1.951m9.345-8.334V6.637c0-1.621-1.152-3.026-2.76-3.235A48.455 48.455 0 0 0 11.25 3c-2.115 0-4.198.137-6.24.402-1.608.209-2.76 1.614-2.76 3.235v6.226c0 1.621 1.152 3.026 2.76 3.235.577.075 1.157.14 1.74.194V21l4.155-4.155" />
                        </svg>
                    </div>
                    <h3 class="matrix-glow-sm mb-2 text-sm font-bold tracking-wider text-matrix">CONVERSATION MEMORY</h3>
                    <p class="text-xs leading-relaxed text-matrix-dim/70">
                        The AI remembers everything. Past choices, alliances, betrayals. Your story is continuous, persistent, and entirely yours.
                    </p>
                </div>

                {{-- Feature 6 --}}
                <div class="rounded border border-matrix-dark bg-matrix-bg/60 p-6 transition-all duration-300 matrix-box-glow-hover">
                    <div class="mb-4 flex h-10 w-10 items-center justify-center rounded border border-matrix-dark text-matrix-dim">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 3v11.25A2.25 2.25 0 0 0 6 16.5h2.25M3.75 3h-1.5m1.5 0h16.5m0 0h1.5m-1.5 0v11.25A2.25 2.25 0 0 1 18 16.5h-2.25m-7.5 0h7.5m-7.5 0-1 3m8.5-3 1 3m0 0 .5 1.5m-.5-1.5h-9.5m0 0-.5 1.5M9 11.25v1.5M12 9v3.75m3-6v6" />
                        </svg>
                    </div>
                    <h3 class="matrix-glow-sm mb-2 text-sm font-bold tracking-wider text-matrix">COMBAT SYSTEM</h3>
                    <p class="text-xs leading-relaxed text-matrix-dim/70">
                        Dice rolls and combat calculations determine your fate. Fight Agents, sentinels, and exiles with weapons and skill checks.
                    </p>
                </div>
            </div>
        </section>

        {{-- Divider --}}
        <div class="relative z-10 mx-auto w-full max-w-4xl px-6">
            <div class="h-px bg-gradient-to-r from-transparent via-matrix-dark to-transparent"></div>
        </div>

        {{-- Characters Section --}}
        <section class="relative z-10 mx-auto max-w-5xl px-6 py-24">
            <p class="mb-4 text-center text-xs tracking-[0.4em] text-matrix-dark">
                // OPERATIVE DATABASE
            </p>
            <h2 class="matrix-glow mb-12 text-center text-2xl font-bold tracking-[0.15em] text-matrix sm:text-3xl">
                CHOOSE YOUR PATH
            </h2>

            <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-5">
                @foreach (App\Enums\Character::cases() as $character)
                    <div class="group rounded border border-matrix-dark bg-matrix-bg/60 p-5 text-center transition-all duration-300 matrix-box-glow-hover">
                        <div class="mx-auto mb-3 flex h-14 w-14 items-center justify-center rounded border border-matrix-dark text-matrix-dim transition-colors duration-300 group-hover:border-matrix-dim group-hover:text-matrix">
                            <span class="text-xl font-bold">{{ strtoupper(substr($character->displayName(), 0, 1)) }}</span>
                        </div>
                        <h3 class="matrix-glow-sm mb-1 text-sm font-bold tracking-wider text-matrix-dim transition-colors duration-300 group-hover:text-matrix">
                            {{ strtoupper($character->displayName()) }}
                        </h3>
                        <p class="text-xs tracking-wide text-matrix-dim/50">
                            {{ $character->tagline() }}
                        </p>
                    </div>
                @endforeach
            </div>

            <p class="mt-8 text-center text-xs leading-relaxed text-matrix-dim/60">
                Each operative has unique abilities, starting inventory, and narrative paths.
            </p>
        </section>

        {{-- Divider --}}
        <div class="relative z-10 mx-auto w-full max-w-4xl px-6">
            <div class="h-px bg-gradient-to-r from-transparent via-matrix-dark to-transparent"></div>
        </div>

        {{-- Final CTA --}}
        <section class="relative z-10 flex flex-col items-center px-6 py-24 text-center">
            <p class="mb-6 text-xs tracking-[0.4em] text-matrix-dark">
                // TRANSMISSION ENDS
            </p>
            <h2 class="matrix-glow mb-4 text-2xl font-bold tracking-[0.15em] text-matrix sm:text-3xl">
                FREE YOUR MIND
            </h2>
            <p class="mb-10 max-w-md text-sm leading-relaxed text-matrix-dim/70">
                The Matrix has you. The red pill is waiting.
                How deep does the rabbit hole go?
            </p>

            <a href="{{ route('game.select') }}" class="inline-flex items-center gap-3 rounded border border-matrix px-10 py-4 text-sm font-bold tracking-[0.2em] text-matrix transition-all duration-300 matrix-box-glow hover:bg-matrix/10 hover:shadow-[0_0_30px_rgba(0,255,65,0.3)]">
                [ JACK IN ]
            </a>
        </section>

        {{-- Footer --}}
        <footer class="relative z-10 border-t border-matrix-dark/30 px-6 py-6 text-center">
            <p class="text-xs tracking-widest text-matrix-dark">
                THE MATRIX: TERMINAL // BUILT WITH LARAVEL AI SDK // {{ date('Y') }}
            </p>
        </footer>
    </body>
</html>
