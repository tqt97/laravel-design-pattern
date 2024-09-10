<x-guest-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Product Detail') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div>
                    <div class="px-6 mb-10 font-bold">
                        <h1 class="text-2xl font-semibold">Product Detail</h1>
                    </div>
                </div>
                {{-- show product --}}
                <div class="p-6 text-gray-900">
                    <h1 class="text-2xl font-semibold">Name: {{ $product->name }}</h1>
                    <p class="text-lg mt text-gray-500">Description: {{ $product->description }}</p>
                </div>
            </div>
            <div class="px-6 mt-10">
                <a class="text-blue-500"
                    href="{{ Request::url() === url()->previous() ? route('products.index') : url()->previous() }}">
                    Back </a>
                |
                <a class="text-blue-500" href="{{ route('products.index') }}">
                    Go to products list
                </a>
            </div>
        </div>
</x-guest-layout>
