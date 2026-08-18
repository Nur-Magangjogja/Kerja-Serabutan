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
        'Outfit' => "'Outfit', sans-serif",
        'Poppins' => "'Poppins', sans-serif",
        'Lexend' => "'Lexend', sans-serif",
        'Montserrat' => "'Montserrat', sans-serif",
        'Inter' => "'Inter', sans-serif",
        default => "'Plus Jakarta Sans', sans-serif",
    };

    // Color Styles for Light Theme (on dark/blue background)
    $accentLight = match($selectedStyle) {
        'gradient_emerald' => 'text-emerald-300 drop-shadow-sm',
        'gradient_sunset' => 'text-amber-300 drop-shadow-sm',
        'gradient_indigo' => 'text-indigo-200 drop-shadow-sm',
        'solid_primary' => 'text-white/90',
        default => 'text-sky-300 drop-shadow-sm', // two_tone & gradient_cyan
    };

    $dotLight = match($selectedStyle) {
        'gradient_emerald' => 'bg-emerald-300',
        'gradient_sunset' => 'bg-amber-300',
        'gradient_indigo' => 'bg-indigo-300',
        default => 'bg-sky-300',
    };

    // Color Styles for Dark Theme (on white/light background)
    $accentDark = match($selectedStyle) {
        'gradient_emerald' => 'bg-gradient-to-r from-emerald-500 to-teal-600 bg-clip-text text-transparent',
        'gradient_sunset' => 'bg-gradient-to-r from-amber-500 via-orange-500 to-rose-600 bg-clip-text text-transparent',
        'gradient_indigo' => 'bg-gradient-to-r from-indigo-500 via-purple-500 to-primary-600 bg-clip-text text-transparent',
        'solid_primary' => 'text-primary-600 dark:text-primary-400',
        default => 'bg-gradient-to-r from-sky-500 via-primary-500 to-primary-600 bg-clip-text text-transparent',
    };

    $dotDark = match($selectedStyle) {
        'gradient_emerald' => 'bg-emerald-500',
        'gradient_sunset' => 'bg-amber-500',
        'gradient_indigo' => 'bg-indigo-500',
        default => 'bg-primary-500',
    };

    // Color Styles for Admin Theme
    $accentAdmin = match($selectedStyle) {
        'gradient_emerald' => 'text-emerald-600 dark:text-emerald-400',
        'gradient_sunset' => 'text-amber-600 dark:text-amber-400',
        'gradient_indigo' => 'text-indigo-600 dark:text-indigo-400',
        default => 'text-primary-600 dark:text-sky-400',
    };

    $dotAdmin = match($selectedStyle) {
        'gradient_emerald' => 'bg-emerald-500 dark:bg-emerald-400',
        'gradient_sunset' => 'bg-amber-500 dark:bg-amber-400',
        'gradient_indigo' => 'bg-indigo-500 dark:bg-indigo-400',
        default => 'bg-primary-500 dark:bg-sky-400',
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
