<?php

namespace Tests\Unit;

use App\Http\Controllers\AuthController;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Mockery;
use Tests\TestCase;

class PruebaAccesoTest extends TestCase
{
    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }

    /** @test */
    // PU-05.1: Incrementa intentos_fallidos a 1 en login() ante clave errónea (HTTP 401)
    public function pu05_01()
    {
        // 1. Precondición
        $usuario = new class {
            public int $id = 1;
            public string $nombre = 'Usuario Test';
            public string $correo = 'usuario@taskflow.com';
            public ?string $password = null;
            public int $intentos_fallidos = 0;
            public mixed $bloqueado_hasta = null;

            public function save(): bool { return true; }
        };
        $usuario->password = Hash::make('PasswordSeguro123');

        $queryMock = Mockery::mock();
        $queryMock->shouldReceive('first')->once()->andReturn($usuario);
        $userAlias = Mockery::mock('alias:App\Models\User');
        $userAlias->shouldReceive('whereRaw')->once()->andReturn($queryMock);

        $request = Request::create('/api/login', 'POST', [
            'email' => 'usuario@taskflow.com',
            'password' => 'ClaveIncorrecta',
        ]);
        $controller = new AuthController();

        // 2. Ejecución de lógica
        $respuesta = $controller->login($request);

        // 3. Aserción
        $this->assertEquals(1, $usuario->intentos_fallidos);
        $this->assertEquals(401, $respuesta->getStatusCode());
    }

    /** @test */
    // PU-05.2: Bloquea por 15 min al 5to fallo en login() y retorna HTTP 429
    public function pu05_02()
    {
        // 1. Precondición
        $usuario = new class {
            public int $id = 1;
            public string $nombre = 'Usuario Test';
            public string $correo = 'usuario@taskflow.com';
            public ?string $password = null;
            public int $intentos_fallidos = 4;
            public mixed $bloqueado_hasta = null;

            public function save(): bool { return true; }
        };
        $usuario->password = Hash::make('PasswordSeguro123');

        $queryMock = Mockery::mock();
        $queryMock->shouldReceive('first')->once()->andReturn($usuario);
        $userAlias = Mockery::mock('alias:App\Models\User');
        $userAlias->shouldReceive('whereRaw')->once()->andReturn($queryMock);

        $request = Request::create('/api/login', 'POST', [
            'email' => 'usuario@taskflow.com',
            'password' => 'ClaveIncorrecta',
        ]);
        $controller = new AuthController();

        // 2. Ejecución de lógica
        $respuesta = $controller->login($request);

        // 3. Aserción
        $this->assertEquals(5, $usuario->intentos_fallidos);
        $this->assertNotNull($usuario->bloqueado_hasta);
        $this->assertEquals(429, $respuesta->getStatusCode());
    }

    /** @test */
    // PU-05.5: Reinicia intentos_fallidos a 0 y bloqueado_hasta a null al login exitoso (HTTP 200)
    public function pu05_05()
    {
        // 1. Precondición
        $usuario = new class {
            public int $id = 1;
            public string $nombre = 'Usuario Test';
            public string $correo = 'usuario@taskflow.com';
            public ?string $password = null;
            public int $intentos_fallidos = 2;
            public mixed $bloqueado_hasta = null;
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

            public function createToken(string $name): object
            {
                return new class {
                    public string $plainTextToken = 'sanctum_token_valido';
                };
            }
        };
        $usuario->password = Hash::make('PasswordSeguro123');

        $queryMock = Mockery::mock();
        $queryMock->shouldReceive('first')->once()->andReturn($usuario);
        $userAlias = Mockery::mock('alias:App\Models\User');
        $userAlias->shouldReceive('whereRaw')->once()->andReturn($queryMock);

        $request = Request::create('/api/login', 'POST', [
            'email' => 'usuario@taskflow.com',
            'password' => 'PasswordSeguro123',
        ]);
        $controller = new AuthController();

        // 2. Ejecución de lógica
        $respuesta = $controller->login($request);
        $datos = $respuesta->getData(true);

        // 3. Aserción
        $this->assertEquals(0, $usuario->intentos_fallidos);
        $this->assertNull($usuario->bloqueado_hasta);
        $this->assertEquals(200, $respuesta->getStatusCode());
        $this->assertArrayHasKey('access_token', $datos);
    }
}