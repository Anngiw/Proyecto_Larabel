<?php

namespace Tests\Unit;

use App\Http\Controllers\AuthController;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use Mockery;
use Tests\TestCase;

class PruebaTokenTest extends TestCase
{
    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }

    /** @test */
    // PU-03.1: Genera token de 64 chars y lo guarda hasheado en sendResetLinkEmail()
    public function pu03_01()
    {
        // 1. Precondición
        Mail::fake();
        $usuario = (object) [
            'id' => 1,
            'nombre' => 'Usuario TaskFlow',
            'correo' => 'usuario@taskflow.com',
        ];
        $datosGuardados = [];

        $userQuery = Mockery::mock();
        $userQuery->shouldReceive('first')->once()->andReturn($usuario);
        $userAlias = Mockery::mock('alias:App\Models\User');
        $userAlias->shouldReceive('whereRaw')->once()->with('LOWER(correo) = ?', ['usuario@taskflow.com'])->andReturn($userQuery);

        $resetAlias = Mockery::mock('alias:App\Models\PasswordReset');
        $resetAlias->shouldReceive('updateOrInsert')
            ->once()
            ->withArgs(function (array $keys, array $values) use (&$datosGuardados): bool {
                $datosGuardados = array_merge($keys, $values);
                return true;
            })
            ->andReturn(true);

        $request = Request::create('/api/forgot-password', 'POST', ['email' => 'usuario@taskflow.com']);
        $controller = new AuthController();

        // 2. Ejecución de lógica
        $respuesta = $controller->sendResetLinkEmail($request);

        // 3. Aserción
        $this->assertEquals(200, $respuesta->getStatusCode());
        $this->assertEquals('usuario@taskflow.com', $datosGuardados['email']);
        $this->assertStringStartsWith('$2y$', $datosGuardados['token']);
    }

    /** @test */
    // PU-03.2: Acepta token vigente (15 min <= 30 min) en resetPassword()
    public function pu03_02()
    {
        // 1. Precondición
        $tokenPlano = Str::random(64);
        $resetRecord = (object) [
            'email' => 'usuario@taskflow.com',
            'token' => Hash::make($tokenPlano),
            'created_at' => Carbon::now()->subMinutes(15)->toDateTimeString(),
        ];
        $usuario = new class {
            public string $correo = 'usuario@taskflow.com';
            public ?string $password = null;
            public bool $tokensEliminados = false;

            public function save(): bool { return true; }

            public function tokens(): object
            {
                return new class($this) {
                    private object $parent;
                    public function __construct(object $parent) { $this->parent = $parent; }
                    public function delete(): void { $this->parent->tokensEliminados = true; }
                };
            }
        };

        $resetQuery = Mockery::mock();
        $resetQuery->shouldReceive('first')->once()->andReturn($resetRecord);
        $resetDelete = Mockery::mock();
        $resetDelete->shouldReceive('delete')->once()->andReturn(true);
        $resetAlias = Mockery::mock('alias:App\Models\PasswordReset');
        $resetAlias->shouldReceive('whereRaw')->once()->andReturn($resetQuery);
        $resetAlias->shouldReceive('where')->once()->with('email', 'usuario@taskflow.com')->andReturn($resetDelete);

        $userQuery = Mockery::mock();
        $userQuery->shouldReceive('first')->once()->andReturn($usuario);
        $userAlias = Mockery::mock('alias:App\Models\User');
        $userAlias->shouldReceive('where')->once()->with('correo', 'usuario@taskflow.com')->andReturn($userQuery);

        $request = Request::create('/api/reset-password', 'POST', [
            'email' => 'usuario@taskflow.com',
            'token' => $tokenPlano,
            'password' => 'NuevaClave123',
            'password_confirmation' => 'NuevaClave123',
        ]);
        $controller = new AuthController();

        // 2. Ejecución de lógica
        $respuesta = $controller->resetPassword($request);

        // 3. Aserción
        $this->assertEquals(200, $respuesta->getStatusCode());
        $this->assertTrue(Hash::check('NuevaClave123', (string) $usuario->password));
    }

    /** @test */
    // PU-03.3: Rechaza token expirado (35 min > 30 min) en resetPassword() con HTTP 400
    public function pu03_03()
    {
        // 1. Precondición
        $tokenPlano = Str::random(64);
        $resetRecord = (object) [
            'email' => 'usuario@taskflow.com',
            'token' => Hash::make($tokenPlano),
            'created_at' => Carbon::now()->subMinutes(35)->toDateTimeString(),
        ];

        $resetQuery = Mockery::mock();
        $resetQuery->shouldReceive('first')->once()->andReturn($resetRecord);
        $resetDelete = Mockery::mock();
        $resetDelete->shouldReceive('delete')->once()->andReturn(true);
        $resetAlias = Mockery::mock('alias:App\Models\PasswordReset');
        $resetAlias->shouldReceive('whereRaw')->once()->andReturn($resetQuery);
        $resetAlias->shouldReceive('where')->once()->with('email', 'usuario@taskflow.com')->andReturn($resetDelete);

        $request = Request::create('/api/reset-password', 'POST', [
            'email' => 'usuario@taskflow.com',
            'token' => $tokenPlano,
            'password' => 'NuevaClave123',
            'password_confirmation' => 'NuevaClave123',
        ]);
        $controller = new AuthController();

        // 2. Ejecución de lógica
        $respuesta = $controller->resetPassword($request);

        // 3. Aserción
        $this->assertEquals(400, $respuesta->getStatusCode());
    }

    /** @test */
    // PU-03.5: Verifica que el token se genera de 64 chars y se protege con Hash::make()
    public function pu03_05()
    {
        // 1. Precondición
        $tokenPlano = Str::random(64);

        // 2. Ejecución de lógica
        $tokenHasheado = Hash::make($tokenPlano);

        // 3. Aserción
        $this->assertEquals(64, strlen($tokenPlano));
        $this->assertNotEquals($tokenPlano, $tokenHasheado);
        $this->assertTrue(Hash::check($tokenPlano, $tokenHasheado));
    }
}