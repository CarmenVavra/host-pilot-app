@props(['name', 'size' => 20, 'stroke' => 2])
{{-- Lucide-style icons, square line caps (Modernist). Decorative: hidden from assistive tech. --}}
<svg {{ $attributes->merge(['class' => 'icon']) }} width="{{ $size }}" height="{{ $size }}" viewBox="0 0 24 24" stroke-width="{{ $stroke }}" aria-hidden="true" focusable="false">
    @switch($name)
        @case('calendar')<rect x="3" y="4" width="18" height="18"/><path d="M16 2v4M8 2v4M3 10h18"/>@break
        @case('users')<circle cx="9" cy="8" r="4"/><path d="M2 21v-2a5 5 0 0 1 5-5h4a5 5 0 0 1 5 5v2"/>@break
        @case('user')<circle cx="12" cy="8" r="4"/><path d="M4 21v-2a5 5 0 0 1 5-5h6a5 5 0 0 1 5 5v2"/>@break
        @case('plus')<path d="M12 5v14M5 12h14"/>@break
        @case('minus')<path d="M5 12h14"/>@break
        @case('chevron-left')<path d="m15 18-6-6 6-6"/>@break
        @case('chevron-right')<path d="m9 18 6-6-6-6"/>@break
        @case('clock')<circle cx="12" cy="12" r="9"/><path d="M12 7v5l3 2"/>@break
        @case('pencil')<path d="M12 20h9M16.5 3.5l4 4L8 20l-5 1 1-5z"/>@break
        @case('trash')<path d="M3 6h18M8 6V4h8v2M6 6l1 15h10l1-15"/>@break
        @case('sparkle')<path d="M12 3l2 6 6 2-6 2-2 6-2-6-6-2 6-2z"/>@break
        @case('cart')<path d="M3 4h3l3 12h10l2-8H7"/><circle cx="10" cy="20" r="1"/><circle cx="18" cy="20" r="1"/>@break
        @case('file-text')<path d="M4 3h16v18H4zM8 8h8M8 12h8M8 16h5"/>@break
        @case('check')<path d="M20 6 9 17l-5-5"/>@break
        @case('map-pin')<path d="M12 21s-7-6.2-7-12a7 7 0 0 1 14 0c0 5.8-7 12-7 12z"/><circle cx="12" cy="9" r="2.5"/>@break
        @case('logout')<path d="M9 21H4V3h5M16 17l5-5-5-5M21 12H9"/>@break
        @case('x')<path d="M18 6 6 18M6 6l12 12"/>@break
    @endswitch
</svg>
