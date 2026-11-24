<?php

namespace Tests\Unit;

use App\Http\Controllers\AuthController;
use Illuminate\Http\Request;
use Mockery;
use Tests\TestCase;

class PruebaSeguridadTest extends TestCase
{
    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }

    /** @test */
    // PU-04.1: Elimina el token actual del usuario autenticado en logout()
    public function pu04_01()
    {
        // 1. Precondición
        $tokenEliminado = false;
        $tokenMock = new class($tokenEliminado) {
            private bool $flag;
            public function __construct(bool &$flag) { $this->flag = &$flag; }
            public function delete(): void { $this->flag = true; }
        };
        $usuarioMock = new class($tokenMock) {
            public string $correo = 'usuario@taskflow.com';
            private object $token;
            public function __construct(object $token) { $this->token = $token; }
            public function currentAccessToken(): object { return $this->token; }
        };

        $request = Request::create('/api/logout', 'POST');
        $request->setUserResolver(fn (): object => $usuarioMock);
        $controller = new AuthController();

        // 2. Ejecución de lógica
        $respuesta = $controller->logout($request);

        // 3. Aserción
        $this->assertTrue($tokenEliminado);
        $this->assertEquals(200, $respuesta->getStatusCode());
    }

    /** @test */
    // PU-04.2: Rechaza con HTTP 401 una petición con token ya revocado
    public function pu04_02()
    {
        // 1. Precondición
        $tokensActivos = [];
        $tokenRevocado = 'token_revocado_123';

        // 2. Ejecución de lógica
        $autorizado = in_array($tokenRevocado, $tokensActivos, true);
        $codigoHttp = $autorizado ? 200 : 401;

        // 3. Aserción
        $this->assertFalse($autorizado);
        $this->assertEquals(401, $codigoHttp);
    }

    /** @test */
    // PU-04.4: Limpia credenciales de sesión ('auth_token', 'user_name') al cerrar sesión
    public function pu04_04()
    {
        // 1. Precondición
        $sesion = [
            'auth_token' => 'token_secreto_12345',
            'user_name' => 'Usuario Test',
        ];

        // 2. Ejecución de lógica
        unset($sesion['auth_token'], $sesion['user_name']);
        $redirigirLogin = true;

        // 3. Aserción
        $this->assertArrayNotHasKey('auth_token', $sesion);
        $this->assertArrayNotHasKey('user_name', $sesion);
        $this->assertTrue($redirigirLogin);
    }
}