<?php

namespace Tests\Feature\Auth;

use App\Models\User;
use App\Models\Cart;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LoginAndMergeCartTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test that guest cart is merged into user's database cart on login
     */
    public function test_guest_cart_merged_into_user_cart_on_login(): void
    {
        // Create a user
        $user = User::factory()->create([
            'email' => 'test@example.com',
            'password' => bcrypt('password123'),
        ]);

        // Set up guest cart in session
        $guestCart = [
            [
                'product_id' => 1,
                'product_name' => 'Product A',
                'category_name' => 'Electronics',
                'price' => 100,
                'quantity' => 2,
            ],
            [
                'product_id' => 2,
                'product_name' => 'Product B',
                'category_name' => 'Books',
                'price' => 50,
                'quantity' => 1,
            ],
        ];

        $response = $this->withSession(['cart' => $guestCart])
            ->post('/login', [
                'email' => 'test@example.com',
                'password' => 'password123',
            ]);

        // Assert redirect to home
        $response->assertRedirect('/');

        // Assert guest cart items were merged into user's DB cart
        $this->assertCount(2, Cart::where('user_id', $user->id)->get());
        
        $cartItem1 = Cart::where('user_id', $user->id)
            ->where('product_id', 1)
            ->first();
        $this->assertEquals(2, $cartItem1->quantity);
        $this->assertEquals('Product A', $cartItem1->product_name);

        $cartItem2 = Cart::where('user_id', $user->id)
            ->where('product_id', 2)
            ->first();
        $this->assertEquals(1, $cartItem2->quantity);
        $this->assertEquals('Product B', $cartItem2->product_name);
    }

    /**
     * Test that existing cart items are incremented on merge
     */
    public function test_existing_cart_items_quantity_incremented_on_login(): void
    {
        // Create a user with existing cart items
        $user = User::factory()->create([
            'email' => 'test@example.com',
            'password' => bcrypt('password123'),
        ]);

        Cart::create([
            'user_id' => $user->id,
            'product_id' => 1,
            'product_name' => 'Product A',
            'category_name' => 'Electronics',
            'price' => 100,
            'quantity' => 3,
        ]);

        // Guest has the same product
        $guestCart = [
            [
                'product_id' => 1,
                'product_name' => 'Product A',
                'category_name' => 'Electronics',
                'price' => 100,
                'quantity' => 2,
            ],
        ];

        $response = $this->withSession(['cart' => $guestCart])
            ->post('/login', [
                'email' => 'test@example.com',
                'password' => 'password123',
            ]);

        // Assert redirect
        $response->assertRedirect('/');

        // Assert quantity was incremented (3 + 2 = 5)
        $cartItem = Cart::where('user_id', $user->id)
            ->where('product_id', 1)
            ->first();
        $this->assertEquals(5, $cartItem->quantity);
    }

    /**
     * Test that session cart is cleared after merge
     */
    public function test_session_cart_cleared_after_login(): void
    {
        $user = User::factory()->create([
            'email' => 'test@example.com',
            'password' => bcrypt('password123'),
        ]);

        $guestCart = [
            [
                'product_id' => 1,
                'product_name' => 'Product A',
                'category_name' => 'Electronics',
                'price' => 100,
                'quantity' => 1,
            ],
        ];

        $response = $this->withSession(['cart' => $guestCart])
            ->post('/login', [
                'email' => 'test@example.com',
                'password' => 'password123',
            ]);

        // Assert session cart is cleared
        $this->assertArrayNotHasKey('cart', $response->getSession()->all());
    }

    /**
     * Test login fails with invalid credentials
     */
    public function test_login_fails_with_invalid_credentials(): void
    {
        $response = $this->post('/login', [
            'email' => 'nonexistent@example.com',
            'password' => 'wrongpassword',
        ]);

        $response->assertSessionHasErrors(['email']);
        $this->assertGuest();
    }
}
