<div class="relative flex min-h-screen items-center justify-center overflow-hidden p-4">
    {{-- Matrix Rain Background --}}
    <div class="absolute inset-0 overflow-hidden opacity-20" x-data="{
        columns: [],
        chars: 'アイウエオカキクケコサシスセソタチツテトナニヌネノハヒフヘホマミムメモヤユヨラリルレロワヲン0123456789ABCDEF',
        init() {
            const count = Math.floor(window.innerWidth / 20);
            for (let i = 0; i < count; i++) {
                let str = '';
                for (let j = 0; j < 30; j++) {
                    str += this.chars[Math.floor(Math.random() * this.chars.length)];
                }
                this.columns.push({
                    text: str,
                    left: (i / count) * 100,
                    duration: 5 + Math.random() * 10,
                    delay: Math.random() * -15,
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

    {{-- Main Content --}}
    <div class="relative z-10 w-full max-w-4xl matrix-flicker">
        {{-- Title --}}
        <div class="mb-12 text-center">
            <h1 class="matrix-glow mb-2 text-4xl font-bold tracking-widest text-matrix sm:text-5xl">
                THE MATRIX
            </h1>
            <p class="text-lg tracking-[0.3em] text-matrix-dim">
                TERMINAL
            </p>
            <div class="mx-auto mt-6 h-px w-48 bg-gradient-to-r from-transparent via-matrix to-transparent"></div>
            <p class="matrix-glow-sm mt-6 text-sm tracking-widest text-matrix-dim">
                > CHOOSE YOUR PATH_
            </p>
        </div>

        {{-- Character Grid --}}
        <div class="mx-auto grid max-w-3xl grid-cols-2 gap-4 sm:grid-cols-3 lg:grid-cols-5">
            @foreach ($characters as $character)
                <button
                    wire:click="selectCharacter('{{ $character->value }}')"
                    wire:key="char-{{ $character->value }}"
                    class="group relative flex flex-col items-center rounded border p-4 transition-all duration-300
                        {{ $selectedCharacter === $character->value
                            ? 'border-matrix bg-matrix/5 matrix-pulse-glow'
                            : 'border-matrix-dark hover:border-matrix-dim bg-matrix-bg matrix-box-glow-hover' }}"
                >
                    {{-- Character Icon --}}
                    <div class="mb-3 flex h-16 w-16 items-center justify-center rounded border
                        {{ $selectedCharacter === $character->value ? 'border-matrix text-matrix' : 'border-matrix-dark text-matrix-dim group-hover:border-matrix-dim group-hover:text-matrix' }}
                        transition-colors duration-300">
                        <span class="text-2xl font-bold">{{ strtoupper(substr($character->displayName(), 0, 1)) }}</span>
                    </div>

                    {{-- Name --}}
                    <span class="matrix-glow-sm text-sm font-bold tracking-wider
                        {{ $selectedCharacter === $character->value ? 'text-matrix' : 'text-matrix-dim group-hover:text-matrix' }}
                        transition-colors duration-300">
                        {{ strtoupper($character->displayName()) }}
                    </span>

                    {{-- Tagline --}}
                    <span class="mt-1 text-xs tracking-wide text-matrix-dim/70">
                        {{ $character->tagline() }}
                    </span>
                </button>
            @endforeach
        </div>

        {{-- Character Description --}}
        @if ($selectedCharacter)
            <div class="matrix-fade-in mx-auto mt-8 max-w-2xl rounded border border-matrix-dark bg-matrix-bg/80 p-6">
                @php $char = App\Enums\Character::from($selectedCharacter); @endphp
                <div class="mb-3 flex items-center gap-3">
                    <span class="matrix-glow text-lg font-bold text-matrix">{{ $char->displayName() }}</span>
                    <span class="text-xs tracking-wider text-matrix-dim">// {{ $char->tagline() }}</span>
                </div>
                <p class="mb-4 text-sm leading-relaxed text-matrix-dim">
                    {{ $char->description() }}
                </p>
                <div class="flex flex-wrap gap-2">
                    <span class="text-xs text-matrix-dim/70">Starting inventory:</span>
                    @foreach ($char->startingInventory() as $item)
                        <span class="rounded border border-matrix-dark px-2 py-0.5 text-xs text-matrix-dim">
                            {{ $item }}
                        </span>
                    @endforeach
                </div>
            </div>
        @endif

        {{-- Enter Button --}}
        <div class="mt-10 text-center">
            <button
                wire:click="startGame"
                wire:loading.attr="disabled"
                @if (! $selectedCharacter) disabled @endif
                class="relative inline-flex items-center gap-3 rounded border px-8 py-3 text-sm font-bold tracking-[0.2em] transition-all duration-300
                    {{ $selectedCharacter
                        ? 'border-matrix text-matrix matrix-box-glow hover:bg-matrix/10 hover:shadow-[0_0_30px_rgba(0,255,65,0.3)] cursor-pointer'
                        : 'border-matrix-dark text-matrix-dark cursor-not-allowed' }}"
            >
                <span wire:loading.remove wire:target="startGame">
                    [ ENTER THE MATRIX ]
                </span>
                <span wire:loading wire:target="startGame" class="matrix-cursor">
                    > INITIALIZING
                </span>
            </button>
        </div>

        {{-- Back Link --}}
        <div class="mt-8 text-center">
            <a href="{{ route('dashboard') }}" wire:navigate class="text-xs tracking-wider text-matrix-dark hover:text-matrix-dim transition-colors">
                > RETURN TO DASHBOARD
            </a>
        </div>
    </div>
</div>
