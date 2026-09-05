@props([
    'size' => 'lg',
    'theme' => 'light',
    'as' => 'span',
    'name' => null,
    'font' => null,
    'style' => null,
    'withDot' => false,
    'subtitle' => null,
])

@php
    $dbName = \App\Models\AppSetting::get('app_name');
    $rawName = $name ?? ($dbName ?: 'SayaBantu');
    
    // Clean string if it contains separator like ' - ' or ' | '
    if (str_contains($rawName, ' - ')) {
        $rawName = trim(explode(' - ', $rawName)[0]);
    } elseif (str_contains($rawName, ' | ')) {
        $rawName = trim(explode(' | ', $rawName)[0]);
    }

    $selectedFont = $font ?? \App\Models\AppSetting::get('app_brand_font', 'Plus Jakarta Sans');
    $selectedStyle = $style ?? \App\Models\AppSetting::get('app_brand_style', 'two_tone');
    
    // Normalisasi kata jika nama adalah variasi 'SayaBantu' atau 'sayabantu'
    $cleanNormalized = strtolower(str_replace(' ', '', $rawName));
    if ($cleanNormalized === 'sayabantu') {
        $firstPart = 'Saya';
        $secondPart = 'Bantu';
    } elseif (preg_match('/^([A-Z][a-z]+|[a-z]+)([A-Z].*)$/', $rawName, $matches)) {
        $firstPart = $matches[1];
        $secondPart = $matches[2];
    } elseif (str_contains($rawName, ' ')) {
        $parts = explode(' ', $rawName, 2);
        $firstPart = $parts[0];
        $secondPart = $parts[1] ?? '';
    } else {
        $firstPart = $rawName;
        $secondPart = '';
    }

    $sizeClasses = match($size) {
        'xs' => 'text-xs',
        'sm' => 'text-sm',
        'base' => 'text-base',
        'lg' => 'text-lg',
        'xl' => 'text-xl',
        '2xl' => 'text-2xl',
        '3xl' => 'text-3xl',
        '4xl' => 'text-4xl',
        '5xl' => 'text-5xl',
        default => 'text-lg',
    };

    $dotSizes = match($size) {
        'xs', 'sm' => 'w-1 h-1',
        'base', 'lg' => 'w-1.5 h-1.5',
        'xl', '2xl' => 'w-2 h-2',
        '3xl', '4xl', '5xl' => 'w-2.5 h-2.5',
        default => 'w-1.5 h-1.5',
    };

    $fontFamily = match($selectedFont) {
        'Space Grotesk'    => "'Space Grotesk', sans-serif",
        'DM Sans'          => "'DM Sans', sans-serif",
        'Syne'             => "'Syne', sans-serif",
        'Nunito'           => "'Nunito', sans-serif",
        'Playfair Display' => "'Playfair Display', serif",
        // Legacy support (jika ada setting lama yang tersimpan)
        'Outfit'           => "'Outfit', sans-serif",
        'Poppins'          => "'Poppins', sans-serif",
        'Lexend'           => "'Lexend', sans-serif",
        'Montserrat'       => "'Montserrat', sans-serif",
        'Inter'            => "'Inter', sans-serif",
        default            => "'Plus Jakarta Sans', sans-serif",
    };

    // Color Styles for Light Theme (on dark/gradient/blue background)
    $accentLight = match($selectedStyle) {
        'gradient_indigo'  => 'text-fuchsia-300 drop-shadow-sm',
        'gradient_emerald' => 'text-emerald-300 drop-shadow-sm',
        'gradient_sunset'  => 'text-amber-300 drop-shadow-sm',
        'gradient_gold'    => 'text-yellow-300 drop-shadow-sm',
        'gradient_crimson' => 'text-rose-300 drop-shadow-sm',
        'solid_primary'    => 'text-sky-200 drop-shadow-sm',
        'solid_monochrome' => 'text-slate-200 drop-shadow-sm',
        // Legacy support
        'gradient_cyan'    => 'text-cyan-300 drop-shadow-sm',
        default            => 'text-cyan-300 drop-shadow-sm', // two_tone
    };

    $dotLight = match($selectedStyle) {
        'gradient_indigo'  => 'bg-fuchsia-300 shadow-fuchsia-400/50',
        'gradient_emerald' => 'bg-emerald-300 shadow-emerald-400/50',
        'gradient_sunset'  => 'bg-amber-300 shadow-amber-400/50',
        'gradient_gold'    => 'bg-yellow-300 shadow-yellow-400/50',
        'gradient_crimson' => 'bg-rose-300 shadow-rose-400/50',
        'solid_primary'    => 'bg-sky-200 shadow-sky-300/50',
        'solid_monochrome' => 'bg-slate-200 shadow-slate-300/50',
        // Legacy support
        'gradient_cyan'    => 'bg-cyan-300 shadow-cyan-400/50',
        default            => 'bg-cyan-300 shadow-cyan-400/50', // two_tone
    };

    // Color Styles for Dark Theme (on white/light background)
    $accentDark = match($selectedStyle) {
        'gradient_indigo'  => 'bg-gradient-to-r from-indigo-600 via-purple-600 to-pink-500 dark:from-indigo-400 dark:via-purple-400 dark:to-pink-400 bg-clip-text text-transparent',
        'gradient_emerald' => 'bg-gradient-to-r from-emerald-500 via-teal-500 to-green-600 dark:from-emerald-400 dark:via-teal-400 dark:to-green-400 bg-clip-text text-transparent',
        'gradient_sunset'  => 'bg-gradient-to-r from-amber-500 via-orange-500 to-rose-600 dark:from-amber-400 dark:via-orange-400 dark:to-rose-400 bg-clip-text text-transparent',
        'gradient_gold'    => 'bg-gradient-to-r from-amber-500 via-yellow-500 to-orange-500 dark:from-amber-400 dark:via-yellow-400 dark:to-orange-400 bg-clip-text text-transparent',
        'gradient_crimson' => 'bg-gradient-to-r from-red-600 via-rose-500 to-pink-600 dark:from-red-400 dark:via-rose-400 dark:to-pink-400 bg-clip-text text-transparent',
        'solid_primary'    => 'text-primary-600 dark:text-primary-400',
        'solid_monochrome' => 'text-slate-700 dark:text-slate-300',
        // Legacy support
        'gradient_cyan'    => 'bg-gradient-to-r from-sky-500 via-cyan-500 to-blue-600 dark:from-sky-400 dark:via-cyan-400 dark:to-blue-400 bg-clip-text text-transparent',
        default            => 'bg-gradient-to-r from-sky-500 via-cyan-500 to-blue-600 dark:from-sky-400 dark:via-cyan-400 dark:to-blue-400 bg-clip-text text-transparent', // two_tone
    };

    $dotDark = match($selectedStyle) {
        'gradient_indigo'  => 'bg-purple-600 dark:bg-purple-400',
        'gradient_emerald' => 'bg-emerald-500 dark:bg-teal-400',
        'gradient_sunset'  => 'bg-orange-500 dark:bg-amber-400',
        'gradient_gold'    => 'bg-amber-500 dark:bg-yellow-400',
        'gradient_crimson' => 'bg-rose-600 dark:bg-rose-400',
        'solid_primary'    => 'bg-primary-600 dark:bg-primary-400',
        'solid_monochrome' => 'bg-slate-700 dark:bg-slate-300',
        // Legacy support
        'gradient_cyan'    => 'bg-cyan-500 dark:bg-cyan-400',
        default            => 'bg-cyan-500 dark:bg-cyan-400', // two_tone
    };

    // Color Styles for Admin Theme
    $accentAdmin = match($selectedStyle) {
        'gradient_indigo'  => 'bg-gradient-to-r from-indigo-600 via-purple-600 to-pink-500 dark:from-indigo-400 dark:via-purple-400 dark:to-pink-400 bg-clip-text text-transparent',
        'gradient_emerald' => 'bg-gradient-to-r from-emerald-500 via-teal-500 to-green-600 dark:from-emerald-400 dark:via-teal-400 dark:to-green-400 bg-clip-text text-transparent',
        'gradient_sunset'  => 'bg-gradient-to-r from-amber-500 via-orange-500 to-rose-600 dark:from-amber-400 dark:via-orange-400 dark:to-rose-400 bg-clip-text text-transparent',
        'gradient_gold'    => 'bg-gradient-to-r from-amber-500 via-yellow-500 to-orange-500 dark:from-amber-400 dark:via-yellow-400 dark:to-orange-400 bg-clip-text text-transparent',
        'gradient_crimson' => 'bg-gradient-to-r from-red-600 via-rose-500 to-pink-600 dark:from-red-400 dark:via-rose-400 dark:to-pink-400 bg-clip-text text-transparent',
        'solid_primary'    => 'text-primary-600 dark:text-primary-400',
        'solid_monochrome' => 'text-slate-700 dark:text-slate-300',
        // Legacy support
        'gradient_cyan'    => 'bg-gradient-to-r from-sky-500 via-cyan-500 to-blue-600 dark:from-sky-400 dark:via-cyan-400 dark:to-blue-400 bg-clip-text text-transparent',
        default            => 'bg-gradient-to-r from-sky-500 via-cyan-500 to-blue-600 dark:from-sky-400 dark:via-cyan-400 dark:to-blue-400 bg-clip-text text-transparent', // two_tone
    };

    $dotAdmin = match($selectedStyle) {
        'gradient_indigo'  => 'bg-purple-600 dark:bg-purple-400',
        'gradient_emerald' => 'bg-emerald-500 dark:bg-teal-400',
        'gradient_sunset'  => 'bg-orange-500 dark:bg-amber-400',
        'gradient_gold'    => 'bg-amber-500 dark:bg-yellow-400',
        'gradient_crimson' => 'bg-rose-600 dark:bg-rose-400',
        'solid_primary'    => 'bg-primary-600 dark:bg-primary-400',
        'solid_monochrome' => 'bg-slate-700 dark:bg-slate-300',
        // Legacy support
        'gradient_cyan'    => 'bg-cyan-500 dark:bg-cyan-400',
        default            => 'bg-cyan-500 dark:bg-cyan-400', // two_tone
    };
@endphp

<{{ $as }} {{ $attributes->merge(['class' => "font-extrabold tracking-tight inline-flex items-baseline gap-0.5 {$sizeClasses}"]) }} style="font-family: {{ $fontFamily }};">
    @if($theme === 'light')
        {{-- For Dark/Gradient/Blue Backgrounds (Header, Hero) --}}
        <span class="text-white font-black drop-shadow-xs">{{ $firstPart }}</span>
        @if($secondPart)
            <span class="{{ $accentLight }} font-black tracking-normal">{{ $secondPart }}</span>
        @endif
        @if($withDot)
            <span class="{{ $dotSizes }} rounded-full {{ $dotLight }} shadow-xs inline-block ml-0.5 mb-0.5"></span>
        @endif

    @elseif($theme === 'dark')
        {{-- For Light/White Backgrounds (Cards, Login Form, Welcome Body) --}}
        <span class="text-slate-900 dark:text-white font-black">{{ $firstPart }}</span>
        @if($secondPart)
            <span class="{{ $accentDark }} font-black tracking-normal">{{ $secondPart }}</span>
        @endif
        @if($withDot)
            <span class="{{ $dotSizes }} rounded-full {{ $dotDark }} shadow-xs inline-block ml-0.5 mb-0.5"></span>
        @endif

    @elseif($theme === 'admin')
        {{-- Adaptive for Admin / SuperAdmin Sidebar --}}
        <span class="text-slate-900 dark:text-white font-black">{{ $firstPart }}</span>
        @if($secondPart)
            <span class="{{ $accentAdmin }} font-black tracking-normal">{{ $secondPart }}</span>
        @endif
        @if($withDot)
            <span class="{{ $dotSizes }} rounded-full {{ $dotAdmin }} inline-block ml-0.5 mb-0.5"></span>
        @endif

    @elseif($theme === 'primary')
        {{-- Full Primary / Gradient --}}
        <span class="text-primary-700 dark:text-primary-300 font-black">{{ $firstPart }}</span>
        @if($secondPart)
            <span class="{{ $accentDark }} font-black tracking-normal">{{ $secondPart }}</span>
        @endif
        @if($withDot)
            <span class="{{ $dotSizes }} rounded-full {{ $dotDark }} inline-block ml-0.5 mb-0.5"></span>
        @endif

    @else
        {{-- Default / Inherit --}}
        <span class="font-black">{{ $firstPart }}</span>
        @if($secondPart)
            <span class="font-black opacity-90">{{ $secondPart }}</span>
        @endif
    @endif
</{{ $as }}>

@if($subtitle)
    <p class="text-xs text-white/90 font-medium tracking-normal mt-0.5">{{ $subtitle }}</p>
@endif
