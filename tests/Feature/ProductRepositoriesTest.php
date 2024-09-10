<?php

namespace Tests\Feature;

use App\Repositories\Products\ProductRepositoryInterface;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Mockery;
use Tests\TestCase;

class ProductRepositoriesTest extends TestCase
{
    /**
     * A basic feature test example.
     */
    public function test_it_return_product_index(): void
    {
        $response = $this->get('/repositories/products');

        $response->assertStatus(200);
    }

    public function test_it_returns_all_products()
    {
        $productRepoMock = Mockery::mock(ProductRepositoryInterface::class);
        $productRepoMock->shouldReceive('all')->andReturn([
            ['name' => 'Organce', 'description' => 'A sweet fruit'],
            ['name' => 'Banana', 'description' => 'A long yellow fruit'],
            ['name' => 'Apple', 'description' => 'A round red fruit'],
        ]);

        $this->app->instance(ProductRepositoryInterface::class, $productRepoMock);

        $response = $this->get('/repositories/products');
        // check if the response contains the data from the mock
        $response->assertSee('Orange');
    }
}
