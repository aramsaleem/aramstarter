@props(['href'])

<a href="{{ $href }}" {{ $attributes->class('font-medium text-primary-600 underline-offset-4 transition-colors hover:text-primary-500 hover:underline dark:text-primary-400 dark:hover:text-primary-300') }}>{{ $slot }}</a>
