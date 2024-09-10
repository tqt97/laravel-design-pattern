<x-guest-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Product Edit') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                {{-- create product --}}
                <div class="mb-10 text-gray-900">
                    <h1 class="text-2xl font-semibold">Edit Product</h1>
                    <br>
                </div>
                <form method="POST" action="{{ route('products.update', $product) }}">
                    @csrf
                    @method('put')
                    {{-- @method('post') --}}
                    <label for="name" class="font-semibold">Name :</label>
                    <input type="text" name="name" class="w-full my-1" value="{{ $product->name }}">
                    @error('name')
                        <div class="text-red-500">{{ $message }}</div>
                    @enderror
                    <br>
                    <label for="description" class="font-semibold">Description :</label>
                    <input type="text" name="description" class="w-full my-1" value="{{ $product->description }}">
                    @error('description')
                        <div class="text-red-500">{{ $message }}</div>
                    @enderror
                    <br>
                    <button type="submit" class="p-2 bg-blue-600 text-white my-1">Update</button>
                </form>
                <div class="mt-10">
                    <a class="text-blue-500"
                        href="{{ Request::url() === url()->previous() ? route('products.index') : url()->previous() }}">
                        Back </a>
                    |
                    <a class="text-blue-500" href="{{ route('products.index') }}">
                        Go to products list
                    </a>
                </div>
            </div>

        </div>
</x-guest-layout>
