<?php

use App\Models\User;
use App\Models\Vault;
use App\Models\PiggyBank;

use function Pest\Laravel\actingAs;

it('prevents connecting a vault to a cancelled piggy bank', function () {
    app()->setLocale('en');
    $user = User::factory()->create();
    $vault = Vault::factory()->for($user)->create();

    $piggyBank = PiggyBank::factory()->for($user)->create([
        'vault_id' => null,
        'status' => 'cancelled',
    ]);

    actingAs($user)
        ->put("/en/piggy-banks/$piggyBank->id", [
            'name' => $piggyBank->name,
            'details' => $piggyBank->details,
            'vault_id' => $vault->id, // Try to connect a vault (should not be allowed)
        ])
        ->assertSessionHasErrors(['vault_id']);
});

it('prevents connecting a vault to a done piggy bank', function () {
    app()->setLocale('en');
    $user = User::factory()->create();
    $vault = Vault::factory()->for($user)->create();

    $piggyBank = PiggyBank::factory()->for($user)->create([
        'vault_id' => null,
        'status' => 'done',
    ]);

    actingAs($user)
        ->put("/en/piggy-banks/$piggyBank->id", [
            'name' => $piggyBank->name,
            'details' => $piggyBank->details,
            'vault_id' => $vault->id, // Try to connect a vault (should not be allowed)
        ])
        ->assertSessionHasErrors(['vault_id']);
});
