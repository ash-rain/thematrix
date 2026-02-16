<div class="flex min-h-screen flex-col matrix-flicker">
    {{-- Top Bar --}}
    <header class="flex items-center justify-between border-b border-matrix-dark px-4 py-3 sm:px-6">
        <div class="flex items-center gap-4">
            <span class="matrix-glow text-sm font-bold tracking-widest text-matrix">THE MATRIX: TERMINAL</span>
            <span class="hidden text-xs tracking-wider text-matrix-dim sm:inline">
                // {{ strtoupper($characterName) }} — TURN {{ $turnCount }}
            </span>
        </div>

        {{-- Health Bar --}}
        <div class="flex items-center gap-3">
            <span class="text-xs tracking-wider {{ $health > 50 ? 'text-matrix-dim' : ($health > 25 ? 'text-matrix-warning' : 'text-matrix-danger') }}">
                HP
            </span>
            <div class="h-3 w-32 overflow-hidden rounded-sm border border-matrix-dark bg-black sm:w-48">
                <div
                    class="matrix-health-bar h-full transition-all duration-700 ease-out"
                    style="width: {{ $health }}%"
                ></div>
            </div>
            <span class="w-8 text-right text-xs font-bold tabular-nums {{ $health > 50 ? 'text-matrix' : ($health > 25 ? 'text-matrix-warning' : 'text-matrix-danger') }}">
                {{ $health }}
            </span>
        </div>
    </header>

    {{-- Main Content --}}
    <main class="flex flex-1 flex-col lg:flex-row">
        {{-- Left Column: Scene Image + Inventory --}}
        <div class="flex flex-col border-b border-matrix-dark lg:w-2/5 lg:border-b-0 lg:border-r">
            {{-- Scene Image --}}
            <div @if (! $sceneImage) wire:poll.5s="refreshImage" @endif class="relative border-b border-matrix-dark">
                @if ($sceneImage)
                    <div class="relative overflow-hidden">
                        <img
                            src="{{ Storage::url($sceneImage) }}"
                            alt="Scene"
                            class="h-48 w-full object-cover opacity-80 sm:h-56 lg:h-64"
                        />
                        <div class="absolute inset-0 bg-gradient-to-t from-black via-transparent to-transparent"></div>
                        <div class="absolute inset-0 mix-blend-multiply" style="background: rgba(0, 255, 65, 0.08)"></div>
                    </div>
                @else
                    <div class="flex h-48 items-center justify-center bg-black/50 sm:h-56 lg:h-64">
                        <div class="text-center">
                            <div class="mb-2 text-xs tracking-[0.3em] text-matrix-dark">
                                &#9608;&#9608;&#9608;&#9608;&#9608;&#9608;&#9608;&#9608;
                            </div>
                            <p class="matrix-cursor text-xs tracking-wider text-matrix-dim">
                                RENDERING SCENE...
                            </p>
                        </div>
                    </div>
                @endif
            </div>

            {{-- Inventory --}}
            <div class="flex-1 p-4">
                <h3 class="mb-3 text-xs font-bold tracking-[0.2em] text-matrix-dim">
                    > INVENTORY
                </h3>
                @if (count($inventory) > 0)
                    <div class="flex flex-wrap gap-2">
                        @foreach ($inventory as $item)
                            <span
                                wire:key="inv-{{ $loop->index }}"
                                class="rounded border border-matrix-dark bg-matrix-bg px-3 py-1.5 text-xs text-matrix-dim transition-colors hover:border-matrix-dim hover:text-matrix"
                            >
                                {{ $item }}
                            </span>
                        @endforeach
                    </div>
                @else
                    <p class="text-xs text-matrix-dark">[ empty ]</p>
                @endif
            </div>
        </div>

        {{-- Right Column: Narrative + Choices --}}
        <div class="flex flex-1 flex-col lg:w-3/5">
            {{-- Error Message --}}
            @if ($errorMessage)
                <div class="mx-4 mt-4 rounded border border-matrix-danger/50 bg-matrix-danger/5 p-4 sm:mx-6 sm:mt-6">
                    <p class="text-sm tracking-wider text-matrix-danger">
                        > {{ $errorMessage }}
                    </p>
                </div>
            @endif

            {{-- Narrative --}}
            <div class="flex-1 overflow-y-auto p-4 sm:p-6">
                @if ($gameOver)
                    {{-- Game Over --}}
                    <div class="matrix-fade-in text-center">
                        <div class="matrix-glitch mb-6">
                            <h2 class="text-3xl font-bold tracking-widest text-matrix-danger sm:text-4xl"
                                style="text-shadow: 0 0 20px rgba(255,0,64,0.5);">
                                SYSTEM FAILURE
                            </h2>
                        </div>
                        <div class="mx-auto mb-8 h-px w-32 bg-matrix-danger/50"></div>
                        <div class="mx-auto max-w-lg text-sm leading-relaxed text-matrix-dim">
                            {!! nl2br(e($narrative)) !!}
                        </div>
                        <div class="mt-8 space-y-3">
                            <p class="text-xs text-matrix-dark">
                                Survived {{ $turnCount }} turns | Items collected: {{ count($inventory) }}
                            </p>
                            <button
                                wire:click="newGame"
                                class="matrix-box-glow inline-flex items-center gap-2 rounded border border-matrix px-6 py-2 text-sm font-bold tracking-[0.15em] text-matrix transition-all hover:bg-matrix/10"
                            >
                                [ REENTER THE MATRIX ]
                            </button>
                        </div>
                    </div>
                @elseif ($narrative)
                    {{-- Active Narrative --}}
                    <div class="matrix-fade-in">
                        <div class="mb-2 text-xs tracking-wider text-matrix-dark">
                            > TURN {{ $turnCount }}
                        </div>
                        <div class="text-sm leading-relaxed text-green-300/90 sm:text-base">
                            {!! nl2br(e($narrative)) !!}
                        </div>
                    </div>
                @else
                    {{-- Loading initial state --}}
                    <div class="flex h-full items-center justify-center">
                        <p class="matrix-cursor text-sm text-matrix-dim">
                            > Connecting to the Matrix
                        </p>
                    </div>
                @endif
            </div>

            {{-- Choices --}}
            @if (! $gameOver && count($choices) > 0)
                <div class="border-t border-matrix-dark p-4 sm:p-6">
                    <div class="space-y-3">
                        @foreach ($choices as $index => $choice)
                            <button
                                wire:click="makeChoice({{ $index }})"
                                wire:key="choice-{{ $index }}"
                                wire:loading.attr="disabled"
                                wire:target="makeChoice"
                                class="group flex w-full items-start gap-3 rounded border border-matrix-dark bg-matrix-bg px-4 py-3 text-left text-sm transition-all duration-200 hover:border-matrix hover:bg-matrix/5 disabled:cursor-wait disabled:opacity-50 matrix-box-glow-hover"
                            >
                                <span class="mt-px shrink-0 font-bold text-matrix-dim group-hover:text-matrix">
                                    [{{ $index + 1 }}]
                                </span>
                                <span class="text-matrix-dim group-hover:text-matrix">
                                    {{ $choice }}
                                </span>
                            </button>
                        @endforeach
                    </div>

                    {{-- Loading indicator --}}
                    <div wire:loading wire:target="makeChoice" class="mt-4">
                        <p class="matrix-cursor text-xs text-matrix-dim">
                            > Processing
                        </p>
                    </div>
                </div>
            @endif
        </div>
    </main>

    {{-- Bottom Bar --}}
    <footer class="flex items-center justify-between border-t border-matrix-dark px-4 py-2 text-xs text-matrix-dark sm:px-6">
        <span>MATRIX TERMINAL v1.0</span>
        <a href="{{ route('game.select') }}" wire:navigate class="transition-colors hover:text-matrix-dim">
            > NEW GAME
        </a>
    </footer>
</div>
