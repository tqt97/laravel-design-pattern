<x-guest-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Products List') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="px-6 border-b">
                    <a href="{{ route('products.create') }}" class="text-blue-500 text-xl">
                        Create a new product
                    </a>
                </div>
                {{-- show product list --}}
                <div class="p-6 text-gray-900">
                    <ul class=" border-b">
                        @foreach ($products as $product)
                            <li class="text-gray-800 py-1 flex">
                                <a href="{{ route('products.show', $product) }}" class="mr-2">
                                    ID {{ $product->id }} - {{ $product->name }}
                                </a>
                                -
                                {{-- <a href="javascript:void(0)" class="text-blue-500"
                                    onclick="event.preventDefault(); document.getElementById('delete-product').submit();"> --}}
                                {{-- create form ask --}}
                                <a href="{{ route('products.edit', $product) }}" class="text-green-400 mx-2">
                                    Edit
                                </a>
                                |
                                <div class="mx-2">
                                    <form id="delete-product" method="POST"
                                        action="{{ route('products.destroy', $product) }}"
                                        onsubmit="return confirm('Are you sure you want to delete this?');">
                                        @csrf
                                        @method('delete')
                                        <button type="submit" class="text-red-500">Delete</button>
                                    </form>
                                </div>
                                {{-- Delete --}}
                                {{-- </a> --}}
                            </li>
                        @endforeach
                    </ul>
                    {{-- pagination --}}
                    <div class="mt-5">
                        {{ $products->links() }}
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-guest-layout>
